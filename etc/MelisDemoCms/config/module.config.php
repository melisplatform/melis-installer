<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return [
    'router' => [
        'routes' => [
            'MelisDemoCms-pageids' => [
                'type' => 'regex',
                'options' => [
                    'regex' => '.*/MelisDemoCms/.*/id/(?<idpage>[0-9]+)',
                    'defaults' => [
                        'controller' => 'MelisDemoCms\Controller\Index',
                        'action' => 'indexsite',
                    ],
                    'spec' => '%idpage',
                ],
            ],
            'MelisDemoCms-homepage' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'MelisFront\Controller\Index',
                        'action' => 'index',
                        'renderType' => 'melis_zf2_mvc',
                        'renderMode' => 'front',
                        'preview' => false,
                        'idpage' => '[:homePageId]',
                    ],
                ],
            ],
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'applicationMelisDemoCms' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/MelisDemoCms',
                    'defaults' => [
                        '__NAMESPACE__' => 'MelisDemoCms\Controller',
                        'controller' => 'MelisSetup',
                        'action' => 'setupForm',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
//
                            ],
                        ],
                    ],
                    'setup' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/setup',
                            'defaults' => [
                                'controller' => 'MelisDemoCms\Controller\MelisSetup',
                                'action' => 'setupForm',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'aliases' => [
            'translator' => 'MvcTranslator',
            'MelisPlatformTable' => 'MelisDemoCms\Model\Tables\MelisPlatformTable',
        ],
        'factories' => [
            // MelisDemoCms Services
            'DemoCmsService' => 'MelisDemoCms\Service\Factory\DemoCmsServiceFactory',
            'SetupDemoCmsService' => 'MelisDemoCms\Service\Factory\SetupDemoCmsServiceFactory',

            'MelisDemoCms\Model\Tables\MelisPlatformTable' => 'MelisDemoCms\Model\Tables\Factory\MelisPlatformTableFactory',
        ],
    ],
    'translator' => [
        // 'locale' => 'en_EN',
    ],
    'controllers' => [
        'invokables' => [
            'MelisDemoCms\Controller\Base' => 'MelisDemoCms\Controller\BaseController',
            'MelisDemoCms\Controller\Home' => 'MelisDemoCms\Controller\HomeController',
            'MelisDemoCms\Controller\News' => 'MelisDemoCms\Controller\NewsController',
            'MelisDemoCms\Controller\Content' => 'MelisDemoCms\Controller\ContentController',
            'MelisDemoCms\Controller\About' => 'MelisDemoCms\Controller\AboutController',
            'MelisDemoCms\Controller\Contact' => 'MelisDemoCms\Controller\ContactController',
            'MelisDemoCms\Controller\Testimonial' => 'MelisDemoCms\Controller\TestimonialController',
            'MelisDemoCms\Controller\Search' => 'MelisDemoCms\Controller\SearchController',
            'MelisDemoCms\Controller\Setup' => 'MelisDemoCms\Controller\SetupController',
            'MelisDemoCms\Controller\MelisSetupPostDownload' => 'MelisDemoCms\Controller\MelisSetupPostDownloadController',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'DemoSiteFieldCollection' => 'MelisDemoCms\Form\View\Helper\DemoSiteFieldCollection',
            'DemoSiteFieldRow' => 'MelisDemoCms\Form\View\Helper\DemoSiteFieldRow',
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'controller_map' => [
            'MelisDemoCms' => true,
        ],
        'template_map' => [
            // Zend default layout
            'layout/layout' => __DIR__ . '/../view/layout/defaultLayout.phtml',
            // Main layout
            'MelisDemoCms/defaultLayout' => __DIR__ . '/../view/layout/defaultLayout.phtml',
            'MelisDemoCms/setupLayout' => __DIR__ . '/../view/layout/setupLayout.phtml',
            'layout/errorLayout' => __DIR__ . '/../view/layout/errorLayout.phtml',
            // Errors layout
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            // Plugins layout
            'MelisDemoCms/plugin/menu' => __DIR__ . '/../view/plugins/menu.phtml',
            'MelisDemoCms/plugin/breadcrumb' => __DIR__ . '/../view/plugins/breadcrumb.phtml',
            'MelisDemoCms/plugin/contactus' => __DIR__ . '/../view/plugins/contactus.phtml',
            'MelisDemoCms/plugin/homepage-slider' => __DIR__ . '/../view/plugins/homepage-slider.phtml',
            'MelisDemoCms/plugin/latest-news' => __DIR__ . '/../view/plugins/latest-news.phtml',
            'MelisDemoCms/plugin/testimonial-slider' => __DIR__ . '/../view/plugins/testimonial-slider.phtml',
            'MelisDemoCms/plugin/news-list' => __DIR__ . '/../view/plugins/news-list.phtml',
            'MelisDemoCms/plugin/list-paginator' => __DIR__ . '/../view/plugins/list-paginator.phtml',
            'MelisDemoCms/plugin/news-details' => __DIR__ . '/../view/plugins/news-details.phtml',
            'MelisDemoCms/plugin/aboutus-slider' => __DIR__ . '/../view/plugins/aboutus-slider.phtml',
            'MelisDemoCms/plugin/search-results' => __DIR__ . '/../view/plugins/search-results.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
