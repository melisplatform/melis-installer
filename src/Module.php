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
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\ArrayUtils;
use Zend\Session\Container;
use MelisInstaller\Listener\MelisInstallCheckPlatformListener;
use MelisInstaller\Listener\MelisInstallerDatabaseInstallStatusListener;
use MelisInstaller\Listener\MelisInstallerDatabaseInstallListener;
use MelisInstaller\Listener\MelisInstallerLastProcessListener;
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
        $eventManager->attach(new MelisInstallCheckPlatformListener());
        $eventManager->attach(new MelisInstallerDatabaseInstallListener());
        $eventManager->attach(new MelisInstallerDatabaseInstallStatusListener());
        $eventManager->attach(new MelisInstallerLastProcessListener());

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
        if(!empty($locale)) { 
            $translator->addTranslationFile('phparray', __DIR__ . '/../language/'.$locale.'.interface.php');
        }

    }
    
    public function getServiceConfig()
    {
        return array(
			'factories' => array(
			    'MelisInstaller\Service\InstallHelperService' =>  function($sm) {
    			    $melisInstallService = new \MelisInstaller\Service\InstallHelperService();
    			    $melisInstallService->setServiceLocator($sm);
    			    return $melisInstallService;
			    },
			    'MelisInstaller\Service\MelisInstallerConfigService' =>  function($sm) {
    			    $melisInstallerConfigService = new \MelisInstaller\Service\MelisInstallerConfigService();
    			    $melisInstallerConfigService->setServiceLocator($sm);
    			    return $melisInstallerConfigService;
			    },
			    'MelisInstaller\Service\MelisInstallerTranslation' => function($sm) {
    			    $melisInstallerTranslation = new \MelisInstaller\Service\MelisInstallerTranslationService();
    			    $melisInstallerTranslation->setServiceLocator($sm);
    			    return $melisInstallerTranslation;
			    },
			),
        );
    }
 
}
