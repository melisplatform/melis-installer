<?php

namespace MelisInstaller\Service;

use Laminas\ServiceManager\ServiceLocatorInterface;
use MelisCore\Controller\ModulesController;

/**
 * Logique des checks de diagnostic du wizard d'installation (steps 1.0/1/1.1/1.2), partagée
 * entre le wizard legacy (InstallerController — comportement inchangé, simple délégation) et
 * le wizard React (SetupReactApiController) — évite de dupliquer cette logique entre les deux.
 */
class SetupWizardService
{
    /** @var ServiceLocatorInterface */
    private $sm;

    public function __construct($serviceManager)
    {
        $this->sm = $serviceManager;
    }

    /** @return array{success:int,errors:array,data:array} */
    public function checkSystemConfig(): array
    {
        $errors = [];
        $checkDataExt = 0;
        $success = 0;

        $translator = $this->sm->get('translator');
        $installHelper = $this->sm->get('InstallerHelper');

        $installHelper->setRequiredExtensions([
            'openssl',
            'json',
            'pdo_mysql',
            'intl',
            'zip',
        ]);

        $dataExt = [];
        foreach ($installHelper->getRequiredExtensions() as $ext) {
            if (in_array($ext, $installHelper->getPhpExtensions())) {
                $dataExt[$ext] = $installHelper->isExtensionsExists($ext);
            } else {
                $dataExt[$ext] = sprintf($translator->translate('tr_melis_installer_step_1_0_extension_not_loaded'), $ext);
            }
        }

        $dataVar = $installHelper->checkEnvironmentVariables();

        if (!empty($dataExt)) {
            foreach ($dataExt as $ext => $status) {
                $checkDataExt = ((int) $status === 1) ? 1 : 0;
            }
        }

        if (!empty($dataVar)) {
            foreach ($dataVar as $var => $value) {
                $currentVal = trim($value);
                if (is_null($currentVal)) {
                    $dataVar[$var] = sprintf($translator->translate('tr_melis_installer_step_1_0_php_variable_not_set'), $var);
                    array_push($errors, sprintf($translator->translate('tr_melis_installer_step_1_0_php_variable_not_set'), $var));
                } elseif ($currentVal || $currentVal == '0' || $currentVal == '-1') {
                    $dataVar[$var] = 1;
                }
            }
        } else {
            array_push($errors, $translator->translate('tr_melis_installer_step_1_0_php_requied_variables_empty'));
        }

        if (empty($errors) && $checkDataExt === 1) {
            $success = 1;
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'data' => [
                'extensions' => $dataExt,
                'variables' => $dataVar,
            ],
        ];
    }

    /** @return array{success:int,errors:array,data:array} */
    public function checkVhost(): array
    {
        $translator = $this->sm->get('translator');

        $success = 0;
        $error = [];
        $platform = null;
        $module = null;

        if (!empty(getenv('MELIS_PLATFORM'))) {
            $platform = getenv('MELIS_PLATFORM');
        } else {
            $error['platform'] = $translator->translate('tr_melis_installer_step_1_1_no_paltform_declared');
        }

        if (!empty(getenv('MELIS_MODULE'))) {
            $module = getenv('MELIS_MODULE');
        } else {
            $error['module'] = $translator->translate('tr_melis_installer_step_1_1_no_module_declared');
        }

        if (empty($error)) {
            $success = 1;
        }

        return [
            'success' => $success,
            'errors' => $error,
            'data' => [
                'platform' => $platform,
                'module' => $module,
            ],
        ];
    }

    /** @return array{success:int,errors:array,result:array} */
    public function checkApache(): array
    {
        $errors = [];
        $results = [];
        $translator = $this->sm->get('translator');
        $requiredModules = ['mod_headers', 'mod_alias', 'mod_deflate'];

        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            foreach ($requiredModules as $requiredModule) {
                $results[$requiredModule] = in_array($requiredModule, $modules);
            }
        } else {
            foreach ($requiredModules as $requiredModule) {
                $results[$requiredModule] = getenv($requiredModule) == 'On';
            }
        }

        foreach ($results as $key => $result) {
            if ($result === false) {
                $errors[$key] = sprintf($translator->translate('tr_melis_installer_apache_module_disabled'), $key);
            }
        }

        $success = count($errors) > 0 ? 0 : 1;

        return [
            'success' => $success,
            'errors' => $errors,
            'result' => $results,
        ];
    }

    /**
     * Step 3.1 — catalogue des modules Melis installables (récupéré en direct depuis le
     * marketplace Packagist Melis — même appel réseau que le carousel legacy
     * `InstallHelperService::getPackagistMelisModules()` — peut renvoyer une liste vide si le
     * marketplace est injoignable en dev, comme pour le carousel). Filtrage identique à
     * `InstallerController::parseModulesList()` (modules inactifs/privés retirés, sauf
     * MelisEngine/MelisFront toujours conservés).
     *
     * @return array<int,array{name:string,package:string,active:bool}>
     */
    public function listAvailableModules(): array
    {
        $installHelper = $this->sm->get('InstallerHelper');
        $alwaysIncluded = ['MelisEngine', 'MelisFront'];

        $result = $installHelper->getPackagistMelisModules();
        $packages = $result['packages'] ?? [];

        $modules = [];
        foreach ($packages as $package) {
            $name = $package['packageModuleName'] ?? null;
            if (!$name) {
                continue;
            }
            if (!empty($package['packageIsPrivate'])) {
                continue;
            }
            $isActive = empty($package['packageIsActive']) ? false : (bool) $package['packageIsActive'];
            if (!$isActive && !in_array($name, $alwaysIncluded, true)) {
                continue;
            }
            $modules[] = [
                'name' => $name,
                'package' => $package['packageName'] ?? '',
                'active' => $isActive,
            ];
        }

        return $modules;
    }

    /**
     * Step 3.2a — télécharge (composer) UNIQUEMENT les modules sélectionnés qui ne sont pas déjà
     * présents dans vendor/. Contrairement au carousel legacy (qui télécharge toute la sélection
     * sans vérifier), on ne touche JAMAIS un module déjà installé — cet environnement de dev a
     * plusieurs modules (dont melis-installer, melis-core lui-même) activement modifiés à la main
     * pendant cette session ; les re-télécharger via composer écraserait ce travail.
     *
     * @param array<string,string> $downloadModules Map nom de module => package composer
     * @return array{downloaded:string[],alreadyPresent:string[]}
     */
    public function downloadMissingModules(array $downloadModules): array
    {
        $moduleSvc = $this->sm->get('MelisAssetManagerModulesService');

        $missing = [];
        $alreadyPresent = [];
        foreach ($downloadModules as $name => $package) {
            if ($moduleSvc->getModulePath($name)) {
                $alreadyPresent[] = $name;
            } else {
                $missing[$name] = $package;
            }
        }

        if (!empty($missing)) {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $composerSvc = $this->sm->get('MelisComposerService');
            $composerSvc->download(implode(' ', $missing), null, true);
            $composerSvc->dumpAutoload();
        }

        return [
            'downloaded' => array_keys($missing),
            'alreadyPresent' => $alreadyPresent,
        ];
    }

    /**
     * Step 3.2b — active les modules sélectionnés dans config/melis.module.load.php.
     *
     * ⚠️ Déviation DÉLIBÉRÉE du carousel legacy : `activateModulesAction` legacy REMPLACE la
     * liste entière des modules chargés par la seule sélection courante — correct pour une
     * INSTALLATION FRAÎCHE (rien n'est encore chargé), mais destructeur ici puisque
     * `config/melis.module.load.php` contient déjà ~28 modules actifs (MelisAI, MelisCommerce,
     * MelisSmallBusiness…) qui n'apparaissent même pas dans le catalogue marketplace. On calcule
     * donc une FUSION (union) — les modules sélectionnés viennent s'AJOUTER à la liste existante,
     * jamais la remplacer — même principe défensif que pour le téléchargement ci-dessus.
     *
     * @param string[] $selectedModules
     * @return string[] Liste finale des modules actifs après fusion
     */
    public function activateModules(array $selectedModules): array
    {
        $moduleSvc = $this->sm->get('MelisInstallerModulesService');

        $current = [];
        if (file_exists('config/melis.module.load.php')) {
            $current = (array) include 'config/melis.module.load.php';
        }

        // MelisEngine/MelisFront toujours nécessaires pour un site (même logique que legacy
        // hors option "MelisCoreOnly", que le wizard React ne propose pas encore).
        $merged = array_values(array_unique(array_merge($current, $selectedModules, ['MelisEngine', 'MelisFront'])));

        $moduleSvc->createModuleLoader('config/', $merged, [], []);

        return $merged;
    }

    /** @return array{success:int,errors:array,data:array} */
    public function checkDirectoryRights(): array
    {
        $translator = $this->sm->get('translator');
        $installHelper = $this->sm->get('InstallerHelper');
        $moduleSvc = $this->sm->get('MelisAssetManagerModulesService');

        $configDir = $installHelper->getDir('config');
        $modules = $moduleSvc->getAllModules();

        $errors = [];

        for ($x = 0; $x < count($configDir); $x++) {
            $configDir[$x] = 'config/' . $configDir[$x];
        }
        array_push($configDir, 'config');

        array_push($configDir, 'config/autoload/platforms/');
        array_push($configDir, 'module/MelisModuleConfig/');
        array_push($configDir, 'module/MelisModuleConfig/languages');
        array_push($configDir, 'module/MelisModuleConfig/config');
        array_push($configDir, 'module/MelisSites/');
        array_push($configDir, 'data/');
        array_push($configDir, 'dbdeploy/');
        array_push($configDir, 'dbdeploy/data');
        array_push($configDir, 'public/');
        array_push($configDir, 'etc/' . ModulesController::BUNDLE_FOLDER_NAME . '/');
        array_push($configDir, 'cache/');
        array_push($configDir, 'test/');
        array_push($configDir, 'thirdparty/');

        for ($x = 0; $x < count($modules); $x++) {
            $modules[$x] = $moduleSvc->getModulePath($modules[$x], false) . '/config';
        }

        $dirs = array_merge($configDir, $modules);

        $results = [];
        foreach ($dirs as $dir) {
            if (file_exists($dir)) {
                if ($installHelper->isDirWritable($dir)) {
                    $results[$dir] = 1;
                } else {
                    $results[$dir] = sprintf($translator->translate('tr_melis_installer_step_1_2_dir_not_writable'), $dir);
                    array_push($errors, sprintf($translator->translate('tr_melis_installer_step_1_2_dir_not_writable'), $dir));
                }
            }
        }

        $success = empty($errors) ? 1 : 0;

        return [
            'success' => $success,
            'errors' => $errors,
            'data' => $results,
        ];
    }
}
