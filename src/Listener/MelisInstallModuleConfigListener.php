<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisGeneralListener;
use Laminas\Session\Container;

class MelisInstallModuleConfigListener extends MelisGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            'MelisInstaller',
            'melis_installer_last_process_start',
            function($e){
                set_time_limit(0);
                $sm = $e->getTarget()->getEvent()->getApplication()->getServiceManager();
                $moduleSvc = $sm->get('MelisInstallerModulesService');
                $installHelperSvc = $sm->get('InstallerHelper');
                $params = $e->getParams();

                $melisInstallerPath = $moduleSvc->getModulePath('MelisInstaller');
                $envConfTemplate    = $melisInstallerPath.'/etc/MelisModuleConfig/env_config_tpl';
                $appInterface       = $melisInstallerPath.'/etc/MelisModuleConfig/app.interface.php';

                $container = new Container('melisinstaller');
                $currentEnv = null;
                $newEnv     = null;

                if(isset($container['environments']) && isset($container['environments']['default_environment'])) {
                    $currentEnv = $container['environments']['default_environment'];
                }

                if(isset($container['environments']) && isset($container['environments']['new'])) {
                    $newEnv = $container['environments']['new'];
                }

                $currentEnvConf = null;
                $newEnvironmentsConf = null;

                if(file_exists($envConfTemplate)) {
                    $envConfTemplate = file_get_contents($envConfTemplate);
                    $displayError    = $currentEnv['app_interface_conf']['error_reporting'] != '0' ? 1 : 0;
                    if(!empty($currentEnv)) {
                        $currentEnvConf = $envConfTemplate;
                        $currentEnvConf = str_replace([
                            '[:sdom_env]',
                            '[:sdom_domain]',
                            '[:sdom_scheme]',
                            '[:send_email]',
                            '[:error_reporting]',
                            '[:display_error]',
                        ], [
                            $currentEnv['wildcard']['sdom_env'],
                            $currentEnv['data']['sdom_domain'],
                            $_SERVER['REQUEST_SCHEME'],
                            $currentEnv['app_interface_conf']['send_email'],
                            $currentEnv['app_interface_conf']['error_reporting'],
                            $displayError
                        ], $currentEnvConf) . PHP_EOL;



                    }

                    if(!empty($newEnv)) {
                        $newEnvironments     = $envConfTemplate;
                        $newEnvironmentsConf = null;
                        foreach($newEnv as $env => $data) {
                            $displayError         = $data[0]['app_interface_conf']['error_reporting'] != '0' ? 1 : 0;
                            $newEnvironmentsConf .= str_replace([
                                '[:sdom_env]',
                                '[:sdom_domain]',
                                '[:sdom_scheme]',
                                '[:send_email]',
                                '[:error_reporting]',
                                '[:display_error]',
                            ], [
                                $env,
                                $data[0]['sdom_domain'],
                                $_SERVER['REQUEST_SCHEME'],
                                $data[0]['app_interface_conf']['send_email'],
                                $data[0]['app_interface_conf']['error_reporting'],
                                $displayError,
                            ], $newEnvironments) . PHP_EOL;
                        }
                    }
                }

                $conf = $currentEnvConf . $newEnvironmentsConf;

                if(file_exists($appInterface)) {
                    $appInterface = file_get_contents($appInterface);
                    $appInterface = str_replace('[:environment_configurations]', $conf, $appInterface);

                    $melisModuleConfig = $moduleSvc->getModulePath('MelisModuleConfig').'/config/';
                    if(is_writable($melisModuleConfig)) {
                        unlink($melisModuleConfig.'app.interface.php');
                        file_put_contents($melisModuleConfig.'app.interface.php', $appInterface);
                    }
                }
            },
            900
        );
    }
}