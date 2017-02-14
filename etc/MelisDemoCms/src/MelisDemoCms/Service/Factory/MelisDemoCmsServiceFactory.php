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
use MelisDemoCms\Service\MelisDemoCmsService;

/**
 * MelisDemoCms Services Factory
 */
class MelisDemoCmsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $melisDemoCmsService = new MelisDemoCmsService();
        $melisDemoCmsService->setServiceLocator($sl);
        return $melisDemoCmsService;
    }
}