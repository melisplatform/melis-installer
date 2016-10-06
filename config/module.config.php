<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

return array(
    'router' => array(
        'routes' => array(
        	'melis-backoffice' => array(
                'child_routes' => array(
                    'application-MelisInstaller' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'MelisInstaller',
                            'defaults' => array(
                                '__NAMESPACE__' => 'MelisInstaller\Controller',
                                'controller'    => 'Installer',
                                'action'        => 'index',
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
                    'setup' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'setup',
                            'defaults' => array(
                                '__NAMESPACE__' => 'MelisInstaller\Controller',
                                'controller'    => 'Installer',
                                'action'        => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                    ), 
                ),
            ),
        ),
    ),
    'translator' => array(
    	'locale' => 'en_EN',
	),
    'service_manager' => array(
		'invokables' => array(
		),
        'aliases' => array(
            'translator' => 'MvcTranslator',
            'InstallerHelper' => 'MelisInstaller\Service\InstallHelperService',
            'MelisInstallerConfig' => 'MelisInstaller\Service\MelisInstallerConfigService',
            'MelisInstallerTranslation' => 'MelisInstaller\Service\MelisInstallerTranslationService',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'MelisInstaller\Controller\Installer' => 'MelisInstaller\Controller\InstallerController',
        ),
    ),
    
    'form_elements' => array(
        'factories' => array(
            'MelisSelect' => 'MelisInstaller\Form\Factory\MelisSelectFactory',
            'MelisText' => 'MelisInstaller\Form\Factory\MelisTextFactory',
            'MelisInstallerLanguageSelect' => 'MelisInstaller\Form\Factory\MelisInstallerLanguageSelectFactory',
        ),
    ),
    
    'view_helpers' => array(
        'invokables' => array(
            'MelisFieldCollection' => 'MelisInstaller\Form\View\Helper\MelisFieldCollection',
            'MelisFieldRow' => 'MelisInstaller\Form\View\Helper\MelisFieldRow',
        ),
    ),
    
    'validators' => array(
        'invokables' => array(
            'MelisPasswordValidator' => 'MelisInstaller\Validator\MelisPasswordValidator',
        ),
    ),
    
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'          => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
	    'strategies' => array(
	        'ViewJsonStrategy'
	    )
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'aliases' => array(
                'MelisInstaller/' => __DIR__ . '/../public/',
            ),
        ),
    ),
);
