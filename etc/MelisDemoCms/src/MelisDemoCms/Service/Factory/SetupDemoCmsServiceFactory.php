<?php

/**
 * Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisDemoCms\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisDemoCms\Service\SetupDemoCmsService;

/**
 * Setup DemoCms Services Factory
 */
class SetupDemoCmsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $setupDemoCmsService = new SetupDemoCmsService();
        $setupDemoCmsService->setServiceLocator($sl);
        return $setupDemoCmsService;
    }
}