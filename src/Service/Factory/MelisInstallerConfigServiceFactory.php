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
use MelisInstaller\Service\MelisInstallerConfigService;

class MelisInstallerConfigServiceFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $sl)
	{ 
	    $melisInstallerConfigService = new MelisInstallerConfigService();
	    $melisInstallerConfigService->setServiceLocator($sl);
	    
	    return $melisInstallerConfigService;
	}

}