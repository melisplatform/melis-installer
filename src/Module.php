<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Session\Container;
use MelisInstaller\Listener\MelisInstallModuleConfigListener;
use Zend\Session\SessionManager;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sm = $e->getApplication()->getServiceManager();
        $this->createTranslations($e);
        $this->initSession();

        $eventManager->attach(new MelisInstallModuleConfigListener());

        // force route to setup if this module is activated
        $eventManager->attach(MvcEvent::EVENT_FINISH, function($e) {
            $uri        = $_SERVER['REQUEST_URI'];
            $setupRoute = '/melis/setup';

            if($uri == '/') {
                header("location: " . $setupRoute);
                die;
            }

        });

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function($e) {

            $uri          = $_SERVER['REQUEST_URI'];

            // check if the platform configuration file is available
            $env          = getenv('MELIS_PLATFORM');
            $docRoot      = $_SERVER['DOCUMENT_ROOT'] . '/../';
            $setupRoute   = '/melis/setup';
            $platformFile = $docRoot . 'config/autoload/platforms/'.$env.'.php';

            $moduleSvc = $e->getTarget()->getServiceManager()->get('MelisInstallerModulesService');

            // force  to use the installer's application.config
            $installerPath = $moduleSvc->getModulePath('MelisInstaller');
            $appLoader     = $installerPath . '/etc/installer.application.config.php';

            if(file_exists($appLoader)) {
                copy($appLoader, $docRoot.'/config/application.config.php');
            }

            $routeMatch       = $e->getRouteMatch();
            $matchedRouteName = $routeMatch->getMatchedRouteName();

            $excludedRoutes = array(
                'melis-backoffice/application-MelisInstaller',
                'melis-backoffice/application-MelisInstaller/default',
                'melis-backoffice/setup',
                'melis-backoffice/translations',
            );

            if ($matchedRouteName && !in_array($matchedRouteName, $excludedRoutes)) {
                header("location: $setupRoute");
                die;
            }
            else {
                if(file_exists($platformFile))
                    unlink($platformFile);

                // reset module load
                $testMode = false;

                if(!$testMode) {
                    $moduleSvc = $e->getTarget()->getServiceManager()->get('MelisInstallerModulesService');
                    $moduleSvc->createModuleLoader('config/', array('MelisAssetManager',
                        'MelisDbDeploy',
                        'MelisComposerDeploy',
                        'MelisInstaller',
                        'MelisModuleConfig'), array(), array());
                }
            }

        }, 10000);
    }

    public function init(ModuleManager $mm)
    {
        $mm->getEventManager()->getSharedManager()->attach('MelisCore', MvcEvent::EVENT_DISPATCH, function($e) {
            $routeMatch  = $e->getRouteMatch();
            $routeParams = $routeMatch->getParams();
            
            $controller = '';
            $action = '';
            
            // force route user when MelisInstaller module is loaded and active
            $controller = $e->getTarget();
            
            $matchedRouteName = $routeMatch->getMatchedRouteName();
            
            $excludedRoutes = array(
                'melis-backoffice/get-translations',
            );
            
            if (!in_array($matchedRouteName, $excludedRoutes)){
                $controller->plugin('redirect')->toUrl('/melis/setup');
            }
            
        }, 100);
    }
    
    /**
     * Create module's session container
     */
    public function initSession()
    {
        $sessionManager = new SessionManager();
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
        $container = new Container('melisinstaller');
    
    }


    public function getConfig()
    {
    	$config = array();
    	$configFiles = array(
			include __DIR__ . '/../config/module.config.php',
	        include __DIR__ . '/../config/app.interface.php',
    	    include __DIR__ . '/../config/app.forms.php',
    	);
    	
    	foreach ($configFiles as $file) {
    		$config = ArrayUtils::merge($config, $file);
    	} 
    	
    	return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function createTranslations($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');
    
        $container = new Container('melisinstaller');
        $locale = !empty($container['setup-language']) ? $container['setup-language'] : 'en_EN';
        
        if (!empty($locale)){
            $translationType = array(
                'interface',
            );
            
            $translationList = array();
            if(file_exists($_SERVER['DOCUMENT_ROOT'].'/../module/MelisModuleConfig/config/translation.list.php')){
                $translationList = include 'module/MelisModuleConfig/config/translation.list.php';
            }

            foreach($translationType as $type){
                
                $transPath = '';
                $moduleTrans = __NAMESPACE__."/$locale.$type.php";
                
                if(in_array($moduleTrans, $translationList)){
                    $transPath = "module/MelisModuleConfig/languages/".$moduleTrans;
                }

                if(empty($transPath)){
                    
                    // if translation is not found, use melis default translations
                    $defaultLocale = (file_exists(__DIR__ . "/../language/$locale.$type.php"))? $locale : "en_EN";
                    $transPath = __DIR__ . "/../language/$defaultLocale.$type.php";
                }
                
                $translator->addTranslationFile('phparray', $transPath);
            }
        }
    }
 
}
