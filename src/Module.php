<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller;

use MelisInstaller\Listener\MelisInstallerNewPlatformListener;
use MelisInstaller\Listener\MelisInstallModuleConfigListener;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use Laminas\Stdlib\ArrayUtils;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sm = $e->getApplication()->getServiceManager();
        $this->createTranslations($e);
        $this->initSession();

        $eventManager->attach(new MelisInstallModuleConfigListener());
        $eventManager->attach(new MelisInstallerNewPlatformListener());


        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function ($e) {

            $sm = $e->getTarget()->getServiceManager();
            $uri = $_SERVER['REQUEST_URI'];

            // check if the platform configuration file is available
            $env = getenv('MELIS_PLATFORM');
            $docRoot = $_SERVER['DOCUMENT_ROOT'] . '/../';
            $setupRoute = '/melis/setup';
            $platformFile = $docRoot . 'config/autoload/platforms/' . $env . '.php';

            /** @var \MelisAssetManager\Service\MelisModulesService $moduleSvc */
            $moduleSvc = $sm->get('MelisAssetManagerModulesService');
            $installerPath = $moduleSvc->getModulePath('MelisInstaller');
            $appLoader = $installerPath . '/etc/installer.application.config.php';

            if (file_exists($appLoader)) {
                $installed = false;
                if (file_exists($melisInstallCheckPath = $docRoot . 'config/melis.install')) {
                    $installed = (bool) trim(file_get_contents($melisInstallCheckPath));
                }

                if (! $installed) {
                    if (file_exists($appLoader)) {
                        // force to use the installer's application.config
                        copy($appLoader, $docRoot . '/config/application.config.php');
                    }

                    $routeMatch = $e->getRouteMatch();
                    $matchedRouteName = $routeMatch->getMatchedRouteName();

                    $excludedRoutes = [
                        'melis-backoffice/application-MelisInstaller',
                        'melis-backoffice/application-MelisInstaller/default',
                        'melis-backoffice/setup',
                        'melis-backoffice/translations',
                        'melis-backoffice/application-MelisEngine/default'
                    ];

                    if ($matchedRouteName && !in_array($matchedRouteName, $excludedRoutes)) {
                        header("location: $setupRoute");
                        die;
                    } else {
                        // reset module load
                        $testMode = true;

                        if (!$testMode) {

                            if (file_exists($platformFile)) {
                                unlink($platformFile);
                            }

                            $moduleSvc = $e->getTarget()->getServiceManager()->get('MelisInstallerModulesService');
                            $moduleSvc->createModuleLoader('config/', [
                                'MelisAssetManager',
                                'MelisDbDeploy',
                                'MelisComposerDeploy',
                                'MelisInstaller',
                                'MelisModuleConfig',
                            ], [], []);
                        }
                    }
                } else {
                    $melisInstallPath = $moduleSvc->getModulePath('MelisInstaller');

                    unlink($melisInstallCheckPath);
                    copy($appLoader, $docRoot . '/config/application.config.php');
                    $moduleSvc->unloadModule('MelisInstaller');
                    header("location: /melis/login");
                    die;
                }
            }
        }, 10000);
    }

    public function createTranslations($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');

        $container = new Container('meliscore');


        $locale = isset($container['melis-lang-locale']) ? $container['melis-lang-locale'] : 'en_EN';

        if (!empty($locale)) {
            $translationType = [
                'interface',
            ];

            $translationList = [];
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../module/MelisModuleConfig/config/translation.list.php')) {
                $translationList = include 'module/MelisModuleConfig/config/translation.list.php';
            }

            foreach ($translationType as $type) {

                $transPath = '';
                $moduleTrans = __NAMESPACE__ . "/$locale.$type.php";

                if (in_array($moduleTrans, $translationList)) {
                    $transPath = "module/MelisModuleConfig/languages/" . $moduleTrans;
                }

                if (empty($transPath)) {

                    // if translation is not found, use melis default translations
                    $defaultLocale = (file_exists(__DIR__ . "/../language/$locale.$type.php")) ? $locale : "en_EN";
                    $transPath = __DIR__ . "/../language/$defaultLocale.$type.php";
                }

                $translator->addTranslationFile('phparray', $transPath);
            }
        }
    }

    /**
     * Create module's session container
     */
    public function initSession()
    {
        $sessionManager = new SessionManager();
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
        $container = new Container('melisinstaller');

    }

    public function init(ModuleManager $mm)
    {
        $mm->getEventManager()->getSharedManager()->attach('MelisCore', MvcEvent::EVENT_DISPATCH, function ($e) {
            $routeMatch = $e->getRouteMatch();
            $routeParams = $routeMatch->getParams();

            $controller = '';
            $action = '';

            // force route user when MelisInstaller module is loaded and active
            $controller = $e->getTarget();

            $matchedRouteName = $routeMatch->getMatchedRouteName();
            $excludedRoutes = [
                'melis-backoffice/get-translations',
            ];

            if (!in_array($matchedRouteName, $excludedRoutes)) {
                $controller->plugin('redirect')->toUrl('/melis/setup');
            }

        }, 100);
    }

    public function getConfig()
    {
        $config = [];
        $configFiles = [
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/app.interface.php',
            include __DIR__ . '/../config/app.forms.php',
        ];

        foreach ($configFiles as $file) {
            $config = ArrayUtils::merge($config, $file);
        }

        return $config;
    }

    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

}
