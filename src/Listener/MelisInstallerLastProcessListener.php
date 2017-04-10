<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use MelisInstaller\Listener\MelisInstallerGeneralListener;
use Zend\Session\Container;
class MelisInstallerLastProcessListener extends MelisInstallerGeneralListener implements ListenerAggregateInterface
{
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisInstaller',
        	array(
                'melis_install_last_process_start'
        	),
        	function($e){

        		$sm = $e->getTarget()->getServiceLocator();
        		$moduleSvc = $sm->get('ModulesService');
        		$installHelperSvc = $sm->get('InstallerHelper');
        		$params = $e->getParams();
        		
        		$modulesInstalled = $params['install_modules'];
        		
        		$moduleSvc->createModuleLoader($_SERVER['DOCUMENT_ROOT'].'/../config/',$modulesInstalled,
        		    array('MelisAssetManager', 'meliscore', 'melisfront', 'melisengine','MelisCms'), array('MelisModuleConfig'));
        		
        		// create public/media folder
        		$mediaPath = HTTP_ROOT.'/media';
        		if(!file_exists($mediaPath)) {
        		    mkdir($mediaPath);
        		    $installHelperSvc->filePermission($mediaPath);
        		}

        	},
        1000);
        
        $this->listeners[] = $callBackHandler;
    }
}