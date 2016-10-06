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

class MelisInstallerDatabaseInstallListener extends MelisInstallerGeneralListener implements ListenerAggregateInterface
{
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisInstaller',
        	array(
                'melis_install_database_process_start'
        	),
        	function($e){

        		$sm = $e->getTarget()->getServiceLocator();
        		$params = $e->getParams();
                $installModules = $params['install_modules'];
                $installHelper = $sm->get('InstallerHelper');

                if(isset($params['dbAdapter']) && !empty($params['dbAdapter'])) {
                    $installHelper->setDbAdapter($params['dbAdapter']);
                    
                    foreach($installModules as $module) {
                        $modulePathInstall = MELIS_MODULES_FOLDER.$module.'/install/sql/';
                        $installHelper->importSql($modulePathInstall);
                    }
                    
                    // install the rest of the required database
                    foreach($installHelper->getRequiredModules() as $module) {
                        $modulePathInstall = MELIS_MODULES_FOLDER.$module.'/install/sql/';
                        $installHelper->importSql($modulePathInstall);
                    }
                    
                }


        	},
        1000);
        
        $this->listeners[] = $callBackHandler;
    }
}