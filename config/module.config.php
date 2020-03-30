<?php

/**
 * Melis Technology (http://www.melistechnology.com]
 *
 * @copyright Copyright (c] 2015 Melis Technology (http://www.melistechnology.com]
 *
 */

use MelisInstaller\Service\Factory\AbstractFactory;
use MelisInstaller\Service\{InstallHelperService, MelisInstallerConfigService, MelisInstallerModulesService, MelisInstallerTranslationService};
use MelisInstaller\Model\Tables\TempTable;
use MelisInstaller\Model\Tables\Factory\TempTableFactory;

return [
    'router' => [
        'routes' => [
            'MelisInstaller-home' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'MelisInstaller\Controller\Installer',
                        'action' => 'index',
                    ],
                ],
            ],
            'melis-backoffice' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/melis[/]',
                    'defaults' => [
                        'controller' => 'MelisInstaller\Controller\Index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'application-MelisInstaller' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'MelisInstaller',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisInstaller\Controller',
                                'controller'    => 'Installer',
                                'action'        => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'default' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                    'defaults' => [
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'setup' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'setup',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisInstaller\Controller',
                                'controller'    => 'Installer',
                                'action'        => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'translations' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'get-translations',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisInstaller\Controller',
                                'controller'    => 'Translation',
                                'action'        => 'getTranslation',
                            ],
                        ],
                    ]
                ],
            ],

        ],
    ],
    'translator' => [
        'locale' => 'en_EN',
    ],
    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            InstallHelperService::class                     => AbstractFactory::class,
            MelisInstallerConfigService::class              => AbstractFactory::class,
            MelisInstallerModulesService::class             => AbstractFactory::class,
            MelisInstallerTranslationService::class         => AbstractFactory::class,
            TempTable::class                                => TempTableFactory::class
        ],
        'aliases' => [
            'translator'                   => 'MvcTranslator',
            'InstallerHelper'              => InstallHelperService::class,
            'MelisInstallerConfig'         => MelisInstallerConfigService::class,
            'MelisInstallerModulesService' => MelisInstallerModulesService::class,
            'MelisInstallerTranslation'    => MelisInstallerTranslationService::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MelisInstaller\Controller\Installer'   => 'MelisInstaller\Controller\InstallerController',
            'MelisInstaller\Controller\Translation' => 'MelisInstaller\Controller\TranslationController',
        ],
    ],
    'form_elements' => [
        'factories' => [
            'MelisSelect'                   => \MelisInstaller\Form\Factory\MelisSelectFactory::class,
            'MelisText'                     => \MelisInstaller\Form\Factory\MelisTextFactory::class,
            'MelisInstallerLanguageSelect'  => \MelisInstaller\Form\Factory\MelisInstallerLanguageSelectFactory::class,
            'MelisInstallerWebOptionSelect' => \MelisInstaller\Form\Factory\MelisInstallerWebOptionSelectFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'MelisFieldCollection'  => \MelisInstaller\Form\View\Helper\MelisFieldCollection::class,
            'MelisFieldRow'         => \MelisInstaller\Form\View\Helper\MelisFieldRow::class,
        ],
        'aliases' => [
            'melisFieldCollection'  => 'MelisFieldCollection'
        ],
    ],
    'validators' => [
        'invokables' => [
            'MelisPasswordValidator' => 'MelisInstaller\Validator\MelisPasswordValidator',
        ],
    ],
    'view_manager' => [
        'doctype'                   => 'HTML5',
        'not_found_template'        => 'error/404',
        'exception_template'        => 'error/index',
        'template_map' => [
            'layout/layout'         => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'             => __DIR__ . '/../view/error/404.phtml',
            'error/index'           => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ]
    ],
];
