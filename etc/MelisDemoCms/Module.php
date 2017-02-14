<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms; 

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;

use MelisDemoCms\Listener\SiteMenuCustomizationListener;
use MelisDemoCms\Listener\SetupDemoCmsListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($e) {
        	$viewModel = $e->getViewModel();
        	$viewModel->setTemplate('layout/errorLayout');
        });
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function($e) {
        	$viewModel = $e->getViewModel();
        	$viewModel->setTemplate('layout/errorLayout');
        }); 
        
        // Adding Event listener to customize the Site menu from Plugin
        $eventManager->attach(new SiteMenuCustomizationListener());
        // Event listener to Setup MelisDemoCms pre-defined datas
        $eventManager->attach(new SetupDemoCmsListener());
        
        $this->createTranslations($e);
    }
    
    public function getConfig()
    {
    	$config = array();
    	$configFiles = array(
    			include __DIR__ . '/config/module.config.php',
    			include __DIR__ . '/config/MelisDemoCms.config.php',
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
        $locale = 'en_EN';
        $translator->addTranslationFile('phparray', __DIR__ . '/language/' . $locale . '.php');
    }
}
