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

class MelisInstallCheckPlatformListener extends MelisInstallerGeneralListener implements ListenerAggregateInterface
{
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisInstaller',
        	array(
                'melis_install_new_platform_end'
        	),
        	function($e){

        		$sm = $e->getTarget()->getServiceLocator();
        		$params = $e->getParams();
                $errors = array();
                $success= 0; 

                $tablePlatforms  = $sm->get('MelisCoreTablePlatform');
                $tableSiteDomain = $sm->get('MelisEngineTableSiteDomain');
                
                $platformDomain = $params['platformDomain'];
                $siteDomains    = $params['siteDomain'];
                $platform       = getenv('MELIS_PLATFORM');
                
                $envData = $tableSiteDomain->getEntryByField('sdom_env', $platform);
                $envData = $envData->current();
                
                if($platformDomain != $envData->sdom_domain) {
                    array_push($errors, array('domain' => array('error' => 'Invalid domain')));
                }
                $siteData = $tableSiteDomain->fetchAll()->toArray();
                // remove unneccesary data while checking
                for($x = 0; $x < count($siteData); $x++) {
                    unset($siteData[$x]['sdom_id']);
                    unset($siteData[$x]['sdom_site_id']);
                    unset($siteData[$x]['sdom_scheme']);
                }

                $ctr = 1;
                foreach($siteDomains as $sites) {
                    if(!in_array(array('sdom_env' => $sites['environment'], 'sdom_domain' => $sites['domain']), $siteData)) {
                        array_push($errors, array('environment_name_'.$x => array('error' => 'Please check this data')));
                        array_push($errors, array('domain_'.$x => array('error' => 'Please check this data')));
                    }
                    $ctr++;
                }
    
                // if all went well
                if(empty($errors)) {
                    $success = 1;
                }

                
        		return array(
        		    'success' => $success,
        		    'errors' => $errors);

        	},
        -1000);
        
        $this->listeners[] = $callBackHandler;
    }
}