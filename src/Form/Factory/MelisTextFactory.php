<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Form\Factory; 

use Laminas\Form\Element\Text;
use Psr\Container\ContainerInterface;

/**
 * Melis Text Input Element
 * 
 */

class MelisTextFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $element = new Text;

        $element->setAttribute('class', 'form-control');
        $element->setLabelOption('class','col-sm-2 control-label');

        return $element;
    }
}