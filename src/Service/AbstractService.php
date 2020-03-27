<?php

namespace MelisInstaller\Service;

use Laminas\ServiceManager\ServiceManager;

class AbstractService
{
    protected $serviceManager;

    public function setServiceManager(ServiceManager $service)
    {
        $this->serviceManager = $service;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}