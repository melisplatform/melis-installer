<?php
/**
 * ModuleManager.php
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0
 * @file      ModuleManager.php
 * @link      http://github.com/melisplatform/melis-core the canonical source repo
 */

namespace MelisInstaller;

/**
 * ModuleManager
 *
 * @package    MelisInstaller
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0
 */
class MelisModuleManager
{
    /**
     * @return array
     */
    public static function getModules()
    {
        // This needs to be set when using MelisPlatform
        error_reporting(E_ALL & ~E_USER_DEPRECATED);
        if (empty(date_default_timezone_get()))
            date_default_timezone_set('Europe/Paris');

        $modules = array();
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : '../..';
        $modulesMelisBackOffice = include $docRoot . '/../config/melis.module.load.php';

        $modules = $modulesMelisBackOffice;

        return $modules;
    }
}