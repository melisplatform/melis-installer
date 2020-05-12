<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisInstaller\Service\MelisInstallerTranslationService;

class MelisInstallerTranslationServiceFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $sl)
	{ 
	    $melisInstallerTranslation = new MelisInstallerTranslationService();
	    $melisInstallerTranslation->setServiceLocator($sl);
	    
	    return $melisInstallerTranslation;
	}

}