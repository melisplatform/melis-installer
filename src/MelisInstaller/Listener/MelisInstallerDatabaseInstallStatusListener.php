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
class MelisInstallerDatabaseInstallStatusListener extends MelisInstallerGeneralListener implements ListenerAggregateInterface
{
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisInstaller',
        	array(
                'melis_install_background_process_start'
        	),
        	function($e){

        		$sm = $e->getTarget()->getServiceLocator();
        		$params = $e->getParams();
                $tables = $params['db_tables'];
                $installHelper = $sm->get('InstallerHelper');
                $status = array();
                
                $container = new Container('melisinstaller');
                if(isset($params['dbAdapter']) && !empty($params['dbAdapter'])) {
                    $installHelper->setDbAdapter($params['dbAdapter']);
                    $container['db_install_tables'] = $tables;
                    foreach($tables as $table) {
                        if($installHelper->isDbTableExists($table)) {
                            $status['installed'][] = $table;
                        }else {
                            $status['failed'][] = $table;
                        }
                    }
                }


                return array('status' => $status);

        	},
        -1000);
        
        $this->listeners[] = $callBackHandler;
    }
}