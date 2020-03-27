<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

/**
 * Melis General Listener implements detach
 * so that other listener can extends this class and not
 * redefine those
 */
abstract class MelisInstallerGeneralListener extends AbstractListenerAggregate
{
    /**
     * Attach a listener to an event emitted by components with specific identifiers.
     *
     * @param  string $identifier Identifier for event emitting component
     * @param  string $eventName
     * @param  callable $listener Listener that will handle the event.
     * @param  int $priority Priority at which listener should execute
     */
    abstract function attach(EventManagerInterface $events, $priority = 1);

	protected function getControllerAction($e)
	{
		$routeMatch = $e->getRouteMatch();
		$routeParams = $routeMatch->getParams();
		$controller = '';
		$action = '';
		
		if (!empty($routeParams['controller']))
			$controller = $routeParams['controller'];
		
		if (!empty($routeParams['action']))
			$action = $routeParams['action'];
		
		return array($controller, $action);
	}
}