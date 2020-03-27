<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Form\Factory;

use Laminas\Form\Element\Select;
use Laminas\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

class MelisSelectFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $element = new Select;
        $element->setValueOptions($this->loadValueOptions($container));
        return $element;
    }
}