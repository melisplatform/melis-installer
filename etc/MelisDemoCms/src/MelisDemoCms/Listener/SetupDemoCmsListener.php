<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SetupDemoCmsListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisInstaller',
        	array(
                'melis_install_last_process_start'
        	),
        	function($e){
        		$sm = $e->getTarget()->getServiceLocator();
        		$params = $e->getParams();
        		$environment = $params['environments'];
        		$environmentName   = $environment['default_environment']['wildcard']['sdom_env'];
        		
        		// DemoCms Service that process the DemoCms pre-defined datas
        		$setupSrv = $sm->get('SetupDemoCmsService');
        		$setupSrv->setup($environmentName);
        	},
        -2000);
        
        $this->listeners[] = $callBackHandler;
    }
    
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}