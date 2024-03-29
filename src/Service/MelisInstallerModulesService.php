<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */
namespace MelisInstaller\Service;
use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\CompletePackage;
use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;

class MelisInstallerModulesService extends AbstractService
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @param Composer $composer
     * @return $this
     */
    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;
        return $this;
    }
    /**
     * @return Composer
     */
    public function getComposer()
    {
        if (is_null($this->composer)) {
            $composer = new \MelisComposerDeploy\MelisComposer();
            $this->composer = $composer->getComposer();
        }
        return $this->composer;
    }
    /**
     * Returns all the modules
     */
    public function getAllModules()
    {
        return array_merge($this->getUserModules(), $this->getVendorModules());
    }
    /**
     * Returns all melisplatform-module packages loaded by composer
     * @return array
     */
    public function getVendorModules()
    {
        $melisComposer = new \MelisComposerDeploy\MelisComposer();
        $melisInstalledPackages = $melisComposer->getInstalledPackages();

        $packages = array_filter($melisInstalledPackages, function ($package) {

            $type = $package->type;
            $extra = $package->extra ?? (object) [];
            $isMelisModule = true;
            if (property_exists($extra, 'melis-module')) {
                $key = 'melis-module';
                if (!$extra->$key)
                    $isMelisModule = false;
            }

            /** @var CompletePackage $package */
            return $type === 'melisplatform-module' &&
                property_exists($extra, 'module-name') && $isMelisModule;
        });

        $modules = array_map(function ($package) {
            $extra = (array) $package->extra;
            /** @var CompletePackage $package */
            return $extra['module-name'];
        }, $packages);

        sort($modules);

        return $modules;
    }
    /**
     * Returns the module name, module package, and its' version
     * @param null $moduleName - provide the module name if you want to get the package specific information
     * @return array
     */
    public function getModulesAndVersions($moduleName = null)
    {
        $tmpModules = array();

        $melisComposer = new \MelisComposerDeploy\MelisComposer();
        $melisInstalledPackages = $melisComposer->getInstalledPackages();

        foreach ($melisInstalledPackages as $package) {
            $packageModuleName = isset($package->extra) ? (array) $package->extra : null;
            $module            = null;
            if(isset($packageModuleName['module-name'])) {
                $module = $packageModuleName['module-name'];
            }
            if($module) {
                $tmpModules[$module] = array(
                    'package' => $package->name,
                    'module'  => $module,
                    'version' => $package->version
                );
                if($module == $moduleName)
                    break;
            }
        }
        $userModules = $this->getUserModules();
        $exclusions  = array('MelisModuleConfig', 'MelisSites');
        foreach($userModules as $module) {
            if(!in_array($module, $exclusions)) {
                $class = $_SERVER['DOCUMENT_ROOT'].'/../module/'.$module.'/Module.php';
                $class = file_get_contents($class);
                $package    = $module;
                $version    = '1.0';
                if (preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $class, $matches)){
                    $result  = array_combine($matches[1], $matches[2]);
                    $version = isset($result['version']) ? $result['version'] : '1.0';
                    $package = isset($result['module'])  ? $result['module'] : $module;
                }
                $tmpModules[$package] = array(
                    'package' => $package,
                    'module'  => $package,
                    'version' => $version
                );
            }
        }
        $modules = $tmpModules;
        if(!is_null($moduleName)) {
            return isset($modules[$moduleName]) ? $modules[$moduleName] : null;
        }
        return $modules;
    }
    public function getUserModules()
    {
        $userModules = $_SERVER['DOCUMENT_ROOT'] . '/../module';
        $modules = array();
        if($this->checkDir($userModules)) {
            $modules = $this->getDir($userModules);
        }
        return $modules;
    }
    public function getSitesModules()
    {
        $userModules = $_SERVER['DOCUMENT_ROOT'] . '/../module/MelisSites';
        $modules = array();
        if($this->checkDir($userModules)) {
            $modules = $this->getDir($userModules);
        }
        return $modules;
    }
    /**
     * Returns all the important modules
     * @param array $excludeModulesOnReturn | exclude some modules that you don't want to be included in return
     * @return array
     */
    public function getCoreModules($excludeModulesOnReturn = array())
    {
        $modules = array(
            'meliscore' => 'MelisCore',
            'melisinstaller' => 'MelisInstaller',
            'melisengine' => 'MelisEngine',
            'melisfront'  => 'MelisFront',
            'melissites' => 'MelisSites',
            'melisassetmanager' => 'MelisAssetManager'
        );
        if($excludeModulesOnReturn) {
            foreach($excludeModulesOnReturn as $exMod) {
                if(isset($modules[$exMod])  && $modules[$exMod]) {
                    unset($modules[$exMod]);
                }
            }
        }
        return $modules;
    }
    /**
     * Returns the full path of the module
     * @param $moduleName
     * @param bool $returnFullPath
     * @return string
     */
    public function getModulePath($moduleName, $returnFullPath = true)
    {
        $path = $this->getUserModulePath($moduleName, $returnFullPath);
        if ($path == '')
            $path = $this->getComposerModulePath($moduleName, $returnFullPath);
        return $path;
    }
    public function getComposerModulePath($moduleName, $returnFullPath = true)
    {
        $melisComposer = new \MelisComposerDeploy\MelisComposer();
        return $melisComposer->getComposerModulePath($moduleName, $returnFullPath);
    }
    public function getUserModulePath($moduleName, $returnFullPath = true)
    {
        $path = '';
        $userModules = $_SERVER['DOCUMENT_ROOT'] . '/../';
        if (in_array($moduleName, $this->getUserModules()))
        {
            if ($this->checkDir($userModules.'module/'.$moduleName))
            {
                if (!$returnFullPath)
                {
                    $path = '/module/'.$moduleName;
                }
                else {
                    $path = $userModules.'module/'.$moduleName;
                }
            }
        }
        return $path;
    }
    /**
     * Returns all modules plugins that does not belong or treated as core modules
     * @param array $excludeModulesOnReturn
     * @return array
     */
    public function getModulePlugins($excludeModulesOnReturn = array())
    {
        $modules = array();
        $excludeModules = array_values($this->getCoreModules());
        foreach($this->getAllModules() as $module) {
            if(!in_array($module, array_merge($excludeModules,$excludeModulesOnReturn))) {
                $modules[] = $module;
            }
        }
        return $modules;
    }
    /**
     * Returns all the modules that has been created by Melis
     * @return array
     */
    public function getMelisModules()
    {
        $modules = array();
        foreach($this->getAllModules() as $module) {
            if(strpos($module, 'Melis') !== false || strpos($module, 'melis') !== false) {
                $modules[] = $module;
            }
        }
        return $modules;
    }
    /**
     * Creates module loader file
     * @param $pathToStore
     * @param array $modules
     * @param array $topModules
     * @param array $bottomModules
     * @return bool
     */
    public function createModuleLoader($pathToStore, $modules = array(), $topModules = array('meliscore', 'melisfront', 'melisengine'), $bottomModules = array('MelisModuleConfig'))
    {

        $tmpFileName = 'melis.module.load.php.tmp';
        $fileName = 'melis.module.load.php';
        if($this->checkDir($pathToStore)) {
            $coreModules = $this->getCoreModules();
            $topModules = array_reverse($topModules);
            foreach($topModules as $module) {
                if(isset($coreModules[$module]) && $coreModules[$module]) {
                    array_unshift($modules, $coreModules[$module]);
                }
                else {
                    array_unshift($modules, $module);
                }
            }
            foreach($bottomModules as $module) {
                if(isset($coreModules[$module]) && $coreModules[$module]) {
                    array_push($modules, $coreModules[$module]);
                }
                else {
                    array_push($modules, $module);
                }
            }

            $config = new Config($modules, true);
            $writer = new PhpArray();
            $writer->setUseBracketArraySyntax(true);
            $conf = $writer->toString($config);
            $conf = preg_replace('/    \d+/u', '', $conf); // remove the number index
            $conf = str_replace('=>', '', $conf); // remove the => characters.
            file_put_contents($pathToStore.'/'.$tmpFileName, $conf);
            if(file_exists($pathToStore.'/'.$tmpFileName)) {
                // check if the array is not empty
                $checkConfig = include($pathToStore.'/'.$tmpFileName);
                if(count($checkConfig) > 1) {
                    // delete the current module loader file
                    if(file_exists($pathToStore.'/'.$fileName))
                        unlink($pathToStore.'/'.$fileName);
                    // rename the module loader tmp file into module.load.php
                    rename($pathToStore.'/'.$tmpFileName, $pathToStore.'/'.$fileName);
                    // if everything went well
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Returns all the modules that has been loaded in laminas
     * @param array $exclude
     * @return unknown[]
     */
    public function getActiveModules($exclude = array())
    {
        $mm = $this->getServiceManager()->get('ModuleManager');
        $loadedModules = array_keys($mm->getLoadedModules());
        $pluginModules = $this->getModulePlugins();
        $modules = array();
        foreach($loadedModules as $module) {
            if(in_array($module, $pluginModules)) {
                if(!in_array($module, $exclude)) {
                    $modules[] = $module;
                }
            }
        }
        return $modules;
    }
    /**
     * Returns the dependencies of the module
     * @param $moduleName
     * @param bool $convertPackageNameToNamespace - set to "true" to convert all package name into their actual Module name
     * @return array
     */
    public function getDependencies($moduleName, $convertPackageNameToNamespace = true)
    {
        $modulePath          = $this->getModulePath($moduleName);
        $dependencies        = array();
        if($modulePath) {
            $defaultDependencies  = array('melis-core');
            $dependencies         = $defaultDependencies;
            $composerPossiblePath = array($modulePath.'/composer.json');
            $composerFile         = null;
            // search for the composer.json file
            foreach($composerPossiblePath as $file) {
                if(file_exists($file)) {
                    $composerFile = file_get_contents($file);
                }
            }
            // if composer.json is found
            if($composerFile) {
                $composer = json_decode($composerFile, true);
                $requires = isset($composer['require']) ? $composer['require']: null;
                if($requires) {
                    $requires = array_map(function($a) {
                        // remove melisplatform prefix
                        return str_replace(array('melisplatform/', ' '), '', trim($a));
                    }, array_keys($requires));
                    $dependencies = $requires;
                }
            }
            if($convertPackageNameToNamespace) {
                $tmpDependencies = array();
                $toolSvc         = $this->getServiceManager()->get('MelisCoreTool');
                foreach($dependencies as $dependency) {
                    $tmpDependencies[] = ucfirst($toolSvc->convertToNormalFunction($dependency));
                }
                $dependencies = $tmpDependencies;
            }
        }
        return $dependencies;
    }
    /**
     * Returns an array of modules or packages that is dependent to the module name provided
     * @param $moduleName
     * @param bool $convertPackageNameToNamespace
     * @param bool $getOnlyActiveModules - returns only the active modules
     * @return array
     */
    public function getChildDependencies($moduleName, $convertPackageNameToNamespace = true, $getOnlyActiveModules = true)
    {
        $modules     = $this->getAllModules();
        $matchModule = $convertPackageNameToNamespace ? $moduleName : $this->convertToPackageName($moduleName);
        $dependents  = array();
        foreach($modules as $module) {
            $dependencies = $this->getDependencies($module, $convertPackageNameToNamespace);
            if($dependencies) {
                if(in_array($matchModule, $dependencies)) {
                    $dependents[] = $convertPackageNameToNamespace ? $module : $this->convertToPackageName($module);
                }
            }
        }
        if(true === $getOnlyActiveModules) {
            $activeModules = $this->getActiveModules();
            $modules       = array();
            foreach($dependents as $module) {
                if(in_array($module, $activeModules)) {
                    $modules[] = $module;
                }
            }
            $dependents = $modules;
        }
        return $dependents;
    }
    /**
     * This will check if directory exists and it's a valid directory
     * @param $dir
     * @return bool
     */
    protected function checkDir($dir)
    {
        if(file_exists($dir) && is_dir($dir))
        {
            return true;
        }
        return false;
    }
    /**
     * Returns all the sub-folders in the provided path
     * @param String $dir
     * @param array $excludeSubFolders
     * @return array
     */
    protected function getDir($dir, $excludeSubFolders = array())
    {
        $directories = array();
        if(file_exists($dir)) {
            $excludeDir = array_merge(array('.', '..', '.gitignore'), $excludeSubFolders);
            $directory  = array_diff(scandir($dir), $excludeDir);
            foreach($directory as $d) {
                if(is_dir($dir.'/'.$d)) {
                    $directories[] = $d;
                }
            }
        }
        return $directories;
    }
    /**
     * convert module name into package name, example: MelisCore will become melis-core
     * @param $module
     * @return string
     */
    private function convertToPackageName($module)
    {
        $moduleName = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $module));
        return $moduleName;
    }
}