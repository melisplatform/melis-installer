<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Form\Factory;

use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\ServiceManager;
use MelisInstaller\Form\Factory\MelisSelectFactory;

class MelisInstallerLanguageSelectFactory extends MelisSelectFactory
{
	protected function loadValueOptions(ServiceManager $serviceManager)
	{
		$translator     = new Translator();
		$locales        = $this->getTranslationsLocale($serviceManager);
		$valueoptions   = array();

		for ($i = 0; $i < count($locales); $i++){
			$valueoptions[$i+1] = $translator->translate($locales[$i]);
		}

		return $valueoptions;
	}

    public function getTranslationsLocale($serviceManager)
    {
        $modulesSvc = $serviceManager->get('MelisAssetManagerModulesService');
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