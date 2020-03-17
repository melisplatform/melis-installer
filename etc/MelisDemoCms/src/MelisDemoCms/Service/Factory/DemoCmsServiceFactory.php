<?php

/**
 * Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisDemoCms\Service\Factory;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\FactoryInterface;
use MelisDemoCms\Service\DemoCmsService;

/**
 * MelisDemoCms Services Factory
 */
class DemoCmsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $demoCmsService = new DemoCmsService();
        $demoCmsService->setServiceLocator($sl);
        return $demoCmsService;
    }
}