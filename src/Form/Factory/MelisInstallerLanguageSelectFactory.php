<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Form\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use MelisInstaller\Form\Factory\MelisSelectFactory;

class MelisInstallerLanguageSelectFactory extends MelisSelectFactory
{
	protected function loadValueOptions(ServiceLocatorInterface $formElementManager)
	{
		$serviceManager = $formElementManager->getServiceLocator();
		$translator     = $serviceManager->get('translator');
		$locales        = $this->getTranslationsLocale($serviceManager);
		$valueoptions   = array();

		for ($i = 0; $i < count($locales); $i++)
		{
			$valueoptions[$i+1] = $translator->translate($locales[$i]);
		}
		
		return $valueoptions;
	}

    public function getTranslationsLocale($sm)
    {
        $modulesSvc = $sm->get('MelisAssetManagerModulesService');
        $modules = $modulesSvc->getAllModules();
        $modulePath = $modulesSvc->getModulePath('MelisInstaller');
        $path = $modulePath.'/language/';
        $dir  = scandir($path);
        $files = array();
        foreach($dir as $file) {
            if(is_file($path.$file)) {
                $files[] = $file;
            }
        }
        $locales = array();
        foreach($files as $file) {
            $locale = explode('.',$file);
            $locales[] = $locale[0];
        }
        // re-add locales to get the unique locales and fix proper array indexing
        $uniqueLocales = array_unique($locales);
        $newUniqueLocales = array();
        foreach($uniqueLocales as $locale) {
            $newUniqueLocales[] = $locale;
        }
        return $newUniqueLocales;
    }

}