<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Model\Tables\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway;
//use Laminas\Stdlib\Hydrator\ObjectProperty;

use MelisInstaller\Model\Tables\TempTable;
use MelisInstaller\Model\Temp;

class TempTableFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        // Requested class instance
        $instance = new $requestedName();
        // TableGateway
        $tableGateway = new TableGateway($instance::TABLE, $container->get(\Laminas\Db\Adapter\Adapter::class));
        // TableGateway requested class setter
        $instance->setTableGateway($tableGateway);
        // Service manager instance
        $instance->setServiceManager($container);

        return $instance;
    }
    // BEFORE
	/*public function createService(ServiceLocatorInterface $sl)
	{
    	$hydratingResultSet = new HydratingResultSet(new ObjectProperty(), new Temp());
    	$tableGateway = new TableGateway('changelog', $sl->get(\Laminas\Db\Adapter\Adapter::class), null, $hydratingResultSet);
		
    	return new TempTable($tableGateway);
	}*/
}