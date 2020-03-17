<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Form\Factory;

use Laminas\ServiceManager\ServiceLocatorInterface;
use MelisInstaller\Form\Factory\MelisSelectFactory;

class MelisInstallerWebOptionSelectFactory extends MelisSelectFactory
{
	protected function loadValueOptions(ServiceLocatorInterface $formElementManager)
	{
		$serviceManager = $formElementManager->getServiceLocator();
		$translator   = $serviceManager->get('translator');
		
		// Getting the default values for Website confguration options from app.interface.php datas
	    $config = $serviceManager->get('config');
	    $defaultWebConfigOptions = $config['plugins']['melis_installer']['datas']['default_website_config_options'];
	    
	    // Translating the values of each option
	    foreach ($defaultWebConfigOptions As $key => $val)
	    {
	        $defaultWebConfigOptions[$key] = $translator->translate($val);
	    }
	    
	    // Retrieving the "etc" dir 
        $ectDir = __DIR__.'/../../../etc';
        $ectDirContent = scandir($ectDir);
        
        $dir = array();
        /**
         * Avoiding dir from "etc" dir
         * SiteSample is Site template increating new Website during installation of Platform
         */
        $excludeDir = array('.', '..', 'SiteSample', 'MelisModuleConfig');
        // Getting only Demo Sites added to the Dir "etc"
        foreach ($ectDirContent As $val)
        {
            if (is_dir($ectDir.'/'.$val) && !in_array($val, $excludeDir))
            {
                array_push($dir, $val);
            }
        }
        
        $demo = array();
        foreach ($dir As $val)
        {
            $demo[$val] = $translator->translate('tr_melis_installer_web_config_option_use').' '.$val;
        }
        
        $valueoptions = array_merge($defaultWebConfigOptions, $demo);
    		
    	return $valueoptions;
	}

}