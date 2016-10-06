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

		$translator   = $serviceManager->get('translator');
		$translations = $serviceManager->get('MelisCoreTranslation');
		$locales      = $translations->getTranslationsLocale();
		
		$valueoptions = array();
		for ($i = 0; $i < count($locales); $i++)
		{
			$valueoptions[$i+1] = $translator->translate($locales[$i]);
		}
		
		return $valueoptions;
	}

}