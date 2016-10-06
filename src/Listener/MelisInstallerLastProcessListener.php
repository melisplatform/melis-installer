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
        		$params = $e->getParams();
        		$moduleName = $params['cms_data']['website_module'];
        		$modulesInstalled = $params['install_modules'];
        		
        		$moduleSvc->createModuleLoader(MELIS_MODULE_CONFIG_FOLDER.'MelisModuleConfig/config/',$modulesInstalled,
        		    array('AssetManager', 'meliscore', 'melisfront', 'melisengine','MelisCms'));
        		
//         		$tmpModule = '';
//         		foreach($modulesInstalled as $module) {
//         		    $tmpModule .= "\t'".$module . "',\n";
//         		}
        		// remove MelisInstaller from module.load
//                 $moduleLoad = file_get_contents(MELIS_MODULE_CONFIG_FOLDER.'MelisModuleConfig/config/module.load.php');
//                 $moduleLoad = str_replace("'MelisInstaller',", $tmpModule."'MelisFront',\n\t'".$moduleName."',", $moduleLoad);
//                 unlink(MELIS_MODULE_CONFIG_FOLDER.'/MelisModuleConfig/config/module.load.php');
//                 file_put_contents(MELIS_MODULE_CONFIG_FOLDER.'/MelisModuleConfig/config/module.load.php', $moduleLoad);

        	},
        1000);
        
        $this->listeners[] = $callBackHandler;
    }
}