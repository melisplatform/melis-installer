<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace MelisInstaller\Controller;

use Laminas\Mvc\Controller\AbstractActionController as AbstractController;

/**
 * Basic action controller
 */
abstract class AbstractActionController extends AbstractController
{
    public function getServiceManager()
    {
        return $this->getEvent()->getApplication()->getServiceManager();
    }
}