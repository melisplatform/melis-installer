<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
        	'[:ModuleName]-pageids' => array(
				'type'    => 'regex',
				'options' => array(
						'regex'    => '.*/[:ModuleName]/.*/id/(?<idpage>[0-9]+)',
						'defaults' => array(
							'controller' => '[:ModuleName]\Controller\Index',
							'action'     => 'indexsite',
							),
						'spec' => '%idpage'
						)
			),
        	'[:ModuleName]-homepage' => array(
				'type'    => 'Literal',
				'options' => array(
					'route'    => '/',
					'defaults' => array(
						'controller'     => 'MelisFront\Controller\Index',
						'action'         => 'index',
					    'renderType'     => 'melis_zf2_mvc',
					    'renderMode'     => 'front',
					    'preview'        => false,
					    'idpage'         => 1
						)
					),
			),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application[:ModuleName]' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/[:ModuleName]',
                    'defaults' => array(
                        '__NAMESPACE__' => '[:ModuleName]\Controller',
                        'controller'    => 'Index',
                        'action'        => 'indexsite',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ), 
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
    /*    'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '[:ModuleName].mo',
            ),
        ), */
    ),
    'controllers' => array(
        'invokables' => array(
            '[:ModuleName]\Controller\Index' => '[:ModuleName]\Controller\IndexController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'controller_map' => array(
            '[:ModuleName]' => true,
        ),
        'template_map' => array(
            'layout/layoutError'        => __DIR__ . '/../view/layout/layoutError.phtml',
            '[:ModuleName]/layout[:ModuleName]'  => __DIR__ . '/../view/layout/layout[:ModuleName].phtml',
            '[:ModuleName]/layout[:ModuleName]Home'  => __DIR__ . '/../view/layout/layout[:ModuleName]Home.phtml',
            '[:ModuleName]/index/indexsite' => __DIR__ . '/../view/[:ModuleName]/index/indexsite.phtml',
            'error/404'               		=> __DIR__ . '/../view/error/404.phtml',
            'error/index'             		=> __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'aliases' => array(
                '[:ModuleName]/' => __DIR__ . '/../public/',
            ),
        ),
    ),
);
