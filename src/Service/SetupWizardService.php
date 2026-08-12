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
     * Chaque entrée porte les mêmes informations que la case à cocher legacy : titre + version
     * affichés, sous-titre en infobulle, et dépendances converties en noms de modules
     * (`toModuleName()` de `selection.phtml`) pour rejouer côté React la logique de
     * `dependencyChecker()`.
     *
     * @return array<int,array{name:string,package:string,active:bool,title:string,version:string,subtitle:string,dependencies:string[]}>
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
                'title' => $package['packageTitle'] ?? $name,
                'version' => $package['packageVersion'] ?? '',
                'subtitle' => $package['packageSubtitle'] ?? '',
                'dependencies' => array_values(array_filter(array_map(
                    [self::class, 'toModuleName'],
                    (array) ($package['packageDependency'] ?? [])
                ))),
            ];
        }

        // Le marketplace Melis sert une version figée (souvent en retard d'une branche majeure) :
        // on affiche la dernière version stable réellement publiée sur Packagist, qui est celle
        // que composer installera. Retombe sur la valeur du marketplace si Packagist ne répond pas.
        $latest = $this->fetchLatestPackagistVersions(array_column($modules, 'package'));
        foreach ($modules as &$module) {
            if (!empty($latest[$module['package']])) {
                $module['version'] = $latest[$module['package']];
            }
        }
        unset($module);

        return $modules;
    }

    /**
     * Dernière version stable de chaque package sur Packagist (métadonnées p2, servies par CDN),
     * récupérées en parallèle. Toute erreur réseau est silencieuse : l'appelant garde alors la
     * version renvoyée par le marketplace.
     *
     * @param string[] $packages
     * @return array<string,string> package => version
     */
    private function fetchLatestPackagistVersions(array $packages): array
    {
        $packages = array_values(array_filter(array_unique($packages)));
        if (!$packages || !function_exists('curl_multi_init')) {
            return [];
        }

        $multi = curl_multi_init();
        $handles = [];
        foreach ($packages as $package) {
            $ch = curl_init('https://repo.packagist.org/p2/' . $package . '.json');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 8,
            ]);
            curl_multi_add_handle($multi, $ch);
            $handles[$package] = $ch;
        }

        do {
            $status = curl_multi_exec($multi, $running);
            if ($running) {
                curl_multi_select($multi, 1.0);
            }
        } while ($running && $status === CURLM_OK);

        $versions = [];
        foreach ($handles as $package => $ch) {
            $body = curl_multi_getcontent($ch);
            $version = $this->latestStableVersion($body, $package);
            if ($version !== null) {
                $versions[$package] = $version;
            }
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }
        curl_multi_close($multi);

        return $versions;
    }

    /** Première version stable des métadonnées p2 (triées de la plus récente à la plus ancienne). */
    private function latestStableVersion(?string $body, string $package): ?string
    {
        if (!$body) {
            return null;
        }

        $data = json_decode($body, true);
        $releases = $data['packages'][$package] ?? null;
        if (!is_array($releases)) {
            return null;
        }

        foreach ($releases as $release) {
            $version = $release['version'] ?? '';
            // Ni branche de dev, ni pré-version : composer n'installerait pas celles-là par défaut.
            if ($version === '' || stripos($version, 'dev') !== false) {
                continue;
            }
            if (preg_match('/-(alpha|beta|rc|pl)/i', $version)) {
                continue;
            }

            return $version;
        }

        return null;
    }

    /**
     * Step 3.1 — sites démo installables ("Site to Install" du carousel legacy,
     * `getPackagistMelisSites()` : déjà filtré sur les sites actifs côté InstallHelperService).
     *
     * @return array<int,array{module:string,package:string,title:string,description:string}>
     */
    public function listAvailableSites(): array
    {
        $installHelper = $this->sm->get('InstallerHelper');
        $result = $installHelper->getPackagistMelisSites();

        $sites = [];
        foreach ($result['packages'] ?? [] as $package) {
            if (empty($package['packageModuleName'])) {
                continue;
            }
            $sites[] = [
                'module' => $package['packageModuleName'],
                'package' => $package['packageName'] ?? '',
                'title' => $package['packageTitle'] ?? $package['packageModuleName'],
                // Le marketplace renvoie la description en HTML (<p>…</p>) ; le carousel legacy
                // l'injecte telle quelle, ici on la renvoie en texte (paragraphes = sauts de
                // ligne) pour éviter d'injecter du HTML distant dans le SPA.
                'description' => $this->htmlToText($package['packageDescription'] ?? ''),
            ];
        }

        return $sites;
    }

    /**
     * Langues proposées pour un nouveau site — mêmes valeurs que l'élément de formulaire
     * `MelisInstallerLanguageSelect` du legacy : la valeur stockée est l'index 1..n du
     * locale dans la liste des traductions disponibles, pas le locale lui-même.
     *
     * @return array<int,array{value:string,label:string}>
     */
    public function listSiteLanguages(): array
    {
        $factory = new \MelisInstaller\Form\Factory\MelisInstallerLanguageSelectFactory();
        $locales = $factory->getTranslationsLocale($this->sm);

        $languages = [];
        foreach (array_values($locales) as $i => $locale) {
            $languages[] = ['value' => (string) ($i + 1), 'label' => $locale];
        }

        return $languages;
    }

    /** Description HTML du marketplace → texte, un paragraphe/`<br>` par ligne. */
    private function htmlToText(string $html): string
    {
        $text = preg_replace('#</p>|<br\s*/?>#i', "\n", $html);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_filter(array_map('trim', explode("\n", $text)), 'strlen');

        return implode("\n", $lines);
    }

    /**
     * `toModuleName()` de `selection.phtml` : nom de package composer → nom de module
     * (`melis-cms` → `MelisCms`), pour comparer les dépendances aux cases à cocher.
     */
    private static function toModuleName(string $package): string
    {
        $package = trim($package);
        if ($package === '') {
            return '';
        }

        return implode('', array_map('ucwords', explode('-', $package)));
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
    /**
     * Nom de module valide : ce que le gate legacy accepte (`/[^a-z_\-0-9]/i`, cf.
     * `InstallerController::setWebConfigAction()`), resserré en liste blanche parce que la
     * valeur finit dans un fichier de configuration Apache côté applier.
     */
    public const MODULE_NAME_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';

    /** Fichier de requête déposé par PHP, lu par l'applier root du conteneur. */
    private const MODULE_REQUEST_FILE = 'data/.melis-module-request';
    /** Marqueur écrit par l'applier une fois la valeur appliquée (ou refusée). */
    private const MODULE_APPLIED_FILE = 'data/.melis-module-applied';

    /**
     * Étape finale — demande que `MELIS_MODULE` prenne la valeur du module de site choisi dans
     * le wizard. PHP (www-data) ne peut ni écrire la configuration Apache (root) ni le `.env`
     * de la stack (propriété de l'hôte) : il dépose une requête, qu'un applier root démarré par
     * l'entrypoint du conteneur applique puis acquitte. Sans applier (installation hors Docker,
     * image plus ancienne), la requête reste simplement en attente — rien n'est cassé, la
     * valeur du vhost continue de faire foi.
     *
     * @return array{success:bool,error?:string,state:string,module:string,current:string}
     */
    public function requestModuleChange(string $name): array
    {
        $name = trim($name);
        $current = (string) getenv('MELIS_MODULE');

        if (!preg_match(self::MODULE_NAME_PATTERN, $name)) {
            return ['success' => false, 'error' => 'Invalid module name', 'state' => 'failed', 'module' => $name, 'current' => $current];
        }

        // Déjà la valeur courante : rien à appliquer, surtout pas un rechargement d'Apache.
        if ($name === $current) {
            return ['success' => true, 'state' => 'applied', 'module' => $name, 'current' => $current];
        }

        $request = $this->appPath(self::MODULE_REQUEST_FILE);
        $applied = $this->appPath(self::MODULE_APPLIED_FILE);
        if (!is_dir(dirname($request)) || !is_writable(dirname($request))) {
            return ['success' => false, 'error' => 'Cannot write ' . dirname($request), 'state' => 'failed', 'module' => $name, 'current' => $current];
        }

        // Acquittement précédent effacé d'abord : l'appelant ne doit pas prendre l'ancien
        // marqueur pour la réponse à cette demande-ci.
        @unlink($applied);

        // tmp + rename : l'applier ne doit jamais pouvoir lire un fichier à moitié écrit.
        $tmp = $request . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, $name . "\n") === false || !@rename($tmp, $request)) {
            @unlink($tmp);
            return ['success' => false, 'error' => 'Cannot write ' . $request, 'state' => 'failed', 'module' => $name, 'current' => $current];
        }
        @chmod($request, 0664);

        return ['success' => true, 'state' => 'pending', 'module' => $name, 'current' => $current];
    }

    /**
     * État de la dernière demande : `applied` (l'applier a acquitté), `failed` (refusée),
     * `pending` (requête déposée, pas encore traitée) ou `idle`.
     *
     * @return array{state:string,module:string,current:string,error:string}
     */
    public function getModuleApplyState(): array
    {
        $current = (string) getenv('MELIS_MODULE');
        $applied = $this->appPath(self::MODULE_APPLIED_FILE);
        $request = $this->appPath(self::MODULE_REQUEST_FILE);

        if (is_file($applied)) {
            // Format écrit par l'applier : "<état> <module>" (ex. "applied MySiteTest").
            $parts = preg_split('/\s+/', trim((string) @file_get_contents($applied)), 2);
            $state = $parts[0] ?? '';
            $module = $parts[1] ?? '';

            return [
                'state' => in_array($state, ['applied', 'failed'], true) ? $state : 'failed',
                'module' => $module,
                'current' => $current,
                'error' => $state === 'failed' ? 'The container could not apply the module name' : '',
            ];
        }

        return [
            'state' => is_file($request) ? 'pending' : 'idle',
            'module' => is_file($request) ? trim((string) @file_get_contents($request)) : $current,
            'current' => $current,
            'error' => '',
        ];
    }

    /** Chemin absolu dans la racine applicative (celle qui contient config/ et data/). */
    private function appPath(string $relative): string
    {
        $root = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/..';

        return $root . '/' . ltrim($relative, '/');
    }
}
