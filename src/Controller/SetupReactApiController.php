<?php

namespace MelisInstaller\Controller;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;
use MelisInstaller\Service\SetupWizardService;

/**
 * API JSON pour le nouveau wizard d'installation React — logique métier partagée avec
 * InstallerController (legacy) via SetupWizardService, format de réponse {success, data, error}
 * identique à MelisReactApi{Tool}Controller.
 *
 * Routes : /melis/MelisInstaller/SetupReactApi/<action> (route existante
 * `melis-backoffice/application-MelisInstaller/default`, déjà whitelistée dans
 * Module.php — aucune nouvelle route/whitelist nécessaire).
 */
class SetupReactApiController extends MelisAbstractActionController
{
    public function pingAction(): HttpResponse
    {
        return $this->jsonResponse(['success' => true, 'data' => ['pong' => true]]);
    }

    /** Step 1.0 — extensions PHP requises + variables d'environnement. */
    public function systemCheckAction(): HttpResponse
    {
        $result = $this->getSetupWizardService()->checkSystemConfig();

        return $this->jsonResponse(['success' => true, 'data' => [
            'passed' => (bool) $result['success'],
            'errors' => array_values($result['errors']),
            'extensions' => $result['data']['extensions'],
            'variables' => $result['data']['variables'],
        ]]);
    }

    /** Step 1 — modules Apache requis (mod_headers, mod_alias, mod_deflate). */
    public function apacheCheckAction(): HttpResponse
    {
        $result = $this->getSetupWizardService()->checkApache();

        return $this->jsonResponse(['success' => true, 'data' => [
            'passed' => (bool) $result['success'],
            'errors' => array_values($result['errors']),
            'modules' => $result['result'],
        ]]);
    }

    /** Step 1.1 — variables d'environnement MELIS_PLATFORM / MELIS_MODULE (vhost). */
    public function vhostCheckAction(): HttpResponse
    {
        $result = $this->getSetupWizardService()->checkVhost();

        return $this->jsonResponse(['success' => true, 'data' => [
            'passed' => (bool) $result['success'],
            'errors' => $result['errors'],
            'platform' => $result['data']['platform'],
            'module' => $result['data']['module'],
        ]]);
    }

    /** Step 1.2 — droits d'écriture sur les répertoires config/modules. */
    public function fsRightsCheckAction(): HttpResponse
    {
        $result = $this->getSetupWizardService()->checkDirectoryRights();

        return $this->jsonResponse(['success' => true, 'data' => [
            'passed' => (bool) $result['success'],
            'errors' => array_values($result['errors']),
            'directories' => $result['data'],
        ]]);
    }

    /**
     * Step 1.3 — environnement par défaut (non modifiable), mêmes valeurs que le bloc
     * "Default environment" du carousel legacy (`step-1.3.phtml`) : nom = MELIS_PLATFORM,
     * domaine = SERVER_NAME. `sendEmail`/`errorReporting` reprennent la configuration déjà
     * enregistrée en session le cas échéant (retour arrière dans le wizard), sinon les mêmes
     * valeurs par défaut que le legacy (email activé, E_ALL & ~E_USER_DEPRECATED).
     */
    public function defaultEnvironmentAction(): HttpResponse
    {
        $installHelper = $this->getServiceManager()->get('InstallerHelper');

        $container = new \Laminas\Session\Container('melisinstaller');
        $current = $container['environments']['default_environment'] ?? [];
        $conf = $current['app_interface_conf'] ?? [];

        return $this->jsonResponse(['success' => true, 'data' => [
            'name' => $installHelper->getMelisPlatform() ?: null,
            'domain' => $this->getRequest()->getServer()->get('SERVER_NAME'),
            'sendEmail' => isset($conf['send_email']) ? (bool) $conf['send_email'] : true,
            'errorReporting' => array_key_exists('error_reporting', $conf)
                ? ($conf['error_reporting'] !== '0' && $conf['error_reporting'] !== 0)
                : true,
        ]]);
    }

    /**
     * Step 1.3 — enregistre l'environnement courant + les sites/domaines déclarés. Réutilise
     * l'event `melis_install_new_platform_start` (MelisInstallerNewPlatformListener) tel quel —
     * seule la forme de la requête change (JSON au lieu de champs de formulaire indexés
     * environment_name_N/domain_N côté carousel legacy).
     *
     * Body attendu : { currentPlatform: { domain, sendEmail, errorReporting },
     *                   environments: [{ name, domain, sendEmail, errorReporting }] }
     */
    public function createEnvironmentAction(): HttpResponse
    {
        $body = json_decode($this->getRequest()->getContent(), true) ?? [];
        $currentPlatform = $body['currentPlatform'] ?? [];
        $environments = $body['environments'] ?? [];

        if (empty($currentPlatform['domain'])) {
            return $this->jsonResponse(['success' => false, 'error' => 'Domain is required'], 400);
        }

        $siteDomain = [];
        foreach ($environments as $env) {
            if (empty($env['name']) || empty($env['domain'])) {
                continue;
            }
            $siteDomain[] = [
                'environment' => $env['name'],
                'domain' => $env['domain'],
                'send_email' => !empty($env['sendEmail']) ? 'on' : 'off',
                'error_reporting' => $env['errorReporting'] ?? 0,
            ];
        }

        $request = [
            'currentPlatform' => [
                'platform_domain' => $currentPlatform['domain'],
                'send_email' => !empty($currentPlatform['sendEmail']) ? 1 : 0,
                'error_reporting' => $currentPlatform['errorReporting'] ?? 0,
            ],
            'siteDomain' => $siteDomain,
        ];

        $this->getEventManager()->trigger('melis_install_new_platform_start', $this, $request);

        return $this->jsonResponse(['success' => true, 'data' => ['saved' => true]]);
    }

    /**
     * Step 2.0 — teste la connexion MySQL. Réutilise InstallHelperService::checkMysqlConnection()
     * tel quel ; contrairement à l'action legacy, renvoie une map champ→message PLATE (pas la
     * forme imbriquée façon erreurs de formulaire Laminas) — plus simple à consommer côté React.
     */
    public function testDatabaseConnectionAction(): HttpResponse
    {
        $body = json_decode($this->getRequest()->getContent(), true) ?? [];
        $hostname = trim((string) ($body['hostname'] ?? ''));
        $database = trim((string) ($body['database'] ?? ''));
        $username = trim((string) ($body['username'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        if ($hostname === '') {
            return $this->jsonResponse(['success' => true, 'data' => ['passed' => false, 'errors' => ['hostname' => 'Host unreachable or not set']]]);
        }
        if ($database === '') {
            return $this->jsonResponse(['success' => true, 'data' => ['passed' => false, 'errors' => ['database' => 'Database name is required']]]);
        }
        if ($username === '') {
            return $this->jsonResponse(['success' => true, 'data' => ['passed' => false, 'errors' => ['username' => 'Username is required']]]);
        }

        $installHelper = $this->getServiceManager()->get('InstallerHelper');
        $result = $installHelper->checkMysqlConnection($hostname, $database, $username, $password);

        $errors = [];
        if (!$result['isConnected']) {
            $errors['hostname'] = 'Host unreachable';
        } elseif (!$result['isMysqlPasswordCorrect']) {
            $errors['password'] = 'Incorrect password';
        } elseif (!$result['isDatabaseExists']) {
            $errors['database'] = 'Database does not exist';
        } elseif (!$result['isDatabaseCollationNameValid']) {
            $errors['database'] = 'Invalid database collation';
        }

        $passed = empty($errors);
        if ($passed) {
            // Même stockage que le carousel legacy — permet à finalizeSetup (étape ultérieure,
            // commune aux deux UIs) de retrouver ces identifiants quel que soit le chemin emprunté.
            $container = new \Laminas\Session\Container('melisinstaller');
            $container['database'] = $body;
            $_SESSION['database'] = $body;
        }

        return $this->jsonResponse(['success' => true, 'data' => ['passed' => $passed, 'errors' => $errors]]);
    }

    /**
     * Step 3.1 — catalogue des modules disponibles à l'installation (appel réseau vers le
     * marketplace, cf. SetupWizardService::listAvailableModules()).
     */
    public function listModulesAction(): HttpResponse
    {
        $service = $this->getSetupWizardService();
        $container = new \Laminas\Session\Container('melisinstaller');
        $siteModule = $container['site_module'] ?? [];
        $vhostModule = getenv('MELIS_MODULE') ?: '';

        return $this->jsonResponse(['success' => true, 'data' => [
            'modules' => $service->listAvailableModules(),
            'sites' => $service->listAvailableSites(),
            'languages' => $service->listSiteLanguages(),
            // Valeur par défaut du module du site : MELIS_MODULE (le vhost), comme le champ
            // pré-rempli du legacy. Côté React il reste éditable.
            'websiteModule' => $vhostModule,
            // Sélection déjà enregistrée en session (retour arrière dans le wizard).
            'selection' => [
                'site' => $siteModule['site'] ?? null,
                'websiteName' => $siteModule['website_name'] ?? $vhostModule,
                'websiteModule' => $siteModule['website_module'] ?? $vhostModule,
                'language' => $siteModule['language'] ?? null,
                'modules' => $container['install_modules'] ?? [],
            ],
        ]]);
    }

    /**
     * Step 3.1 — enregistre l'option de plateforme choisie, le site à installer et la sélection
     * de modules. Écrit exactement les mêmes clés de session que `setDownloadableModulesAction`
     * legacy (`install_modules`, `download_modules`, `site_module`), pour que les étapes
     * suivantes (téléchargement composer, activation, installation du site) — partagées avec le
     * carousel — retrouvent la sélection quel que soit le chemin emprunté. Le flux
     * multi-framework/demo-tool du legacy (`otherFWData`) n'est volontairement pas porté :
     * c'est le seul champ retiré de cette étape côté React.
     *
     * Body attendu : { webOption, site: {module, package}|null, modules: [{name, package}],
     *                  language, websiteName, websiteModule }
     */
    public function saveModuleSelectionAction(): HttpResponse
    {
        $body = json_decode($this->getRequest()->getContent(), true) ?? [];
        $webOption = $body['webOption'] ?? 'MelisCoreOnly';
        $site = $body['site'] ?? null;
        $selected = $body['modules'] ?? [];

        $installModules = [];
        $downloadModules = [];
        foreach ($selected as $module) {
            if (empty($module['name']) || empty($module['package'])) {
                continue;
            }
            $installModules[] = $module['name'];
            $downloadModules[$module['name']] = $module['package'];
        }

        // Le site démo choisi s'ajoute à la liste des modules à télécharger, comme le legacy
        // qui pousse le package du radio `site` dans packages[]/modules[].
        if ($webOption === 'MelisDemoCms' && !empty($site['module']) && !empty($site['package'])) {
            $installModules[] = $site['module'];
            $downloadModules[$site['module']] = $site['package'];
        }

        // `site` en session = le module du site démo choisi, sinon l'option elle-même
        // (MelisCoreOnly / None / NewSite) — cf. `selectedSite` du legacy.
        $selectedSite = $webOption === 'MelisDemoCms' ? ($site['module'] ?? $webOption) : $webOption;

        $container = new \Laminas\Session\Container('melisinstaller');
        $container['install_modules'] = $installModules;
        $container['download_modules'] = $downloadModules;
        $container['site_module'] = [
            'site' => $selectedSite,
            'language' => $body['language'] ?? null,
            'website_name' => $body['websiteName'] ?? '',
            'website_module' => $body['websiteModule'] ?? (getenv('MELIS_MODULE') ?: ''),
        ];

        // Core seul : aucun module à télécharger ni à activer (même court-circuit que
        // `isUsingCoreOnly()` côté legacy).
        if ($webOption === 'MelisCoreOnly') {
            $container['install_modules'] = [];
            $container['download_modules'] = [];
        }

        return $this->jsonResponse(['success' => true, 'data' => [
            'count' => count($container['install_modules']),
            'site' => $selectedSite,
        ]]);
    }

    /**
     * Step 3.2a — télécharge les modules sélectionnés absents de vendor/ (ne touche jamais un
     * module déjà présent — cf. SetupWizardService::downloadMissingModules()). Peut être long
     * (appel composer réseau) : pas de timeout côté client, le spinner reste affiché.
     */
    public function downloadModulesAction(): HttpResponse
    {
        $container = new \Laminas\Session\Container('melisinstaller');
        $downloadModules = $container['download_modules'] ?? [];

        if (empty($downloadModules)) {
            return $this->jsonResponse(['success' => false, 'error' => 'No module selection found — go back to the Modules step first.'], 400);
        }

        try {
            $result = $this->getSetupWizardService()->downloadMissingModules($downloadModules);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }

        return $this->jsonResponse(['success' => true, 'data' => $result]);
    }

    /**
     * Step 3.2b — active les modules sélectionnés (fusion avec la liste déjà active — cf.
     * SetupWizardService::activateModules()).
     */
    public function activateModulesAction(): HttpResponse
    {
        $container = new \Laminas\Session\Container('melisinstaller');
        $installModules = $container['install_modules'] ?? [];

        try {
            $merged = $this->getSetupWizardService()->activateModules($installModules);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }

        return $this->jsonResponse(['success' => true, 'data' => ['modules' => $merged]]);
    }

    /**
     * Étape finale — demande au conteneur d'adopter le module de site choisi dans le wizard
     * comme `MELIS_MODULE` (variable de vhost + `.env` de la stack), pour que le site front
     * réponde sans repasser par une édition manuelle et un redémarrage.
     *
     * Appelée AVANT `finalizeSetup` : cette dernière débranche MelisInstaller, donc cette route
     * n'existe plus après. Le nom est celui du module de site RÉELLEMENT installé (cf.
     * `resolveSiteModule()`) ; le body peut le surcharger.
     *
     * Body accepté : { module?: string }
     */
    public function applyModuleAction(): HttpResponse
    {
        $body = json_decode($this->getRequest()->getContent(), true) ?? [];
        $container = new \Laminas\Session\Container('melisinstaller');

        $module = (string) ($body['module'] ?? $this->resolveSiteModule($container));
        if ($module === '') {
            // Installation sans site (core seul / plateforme nue) : il n'y a pas de module de
            // site à servir, MELIS_MODULE garde sa valeur. Ce n'est pas une erreur.
            return $this->jsonResponse(['success' => true, 'data' => [
                'state' => 'skipped',
                'module' => '',
                'current' => getenv('MELIS_MODULE') ?: '',
            ]]);
        }

        $result = $this->getSetupWizardService()->requestModuleChange($module);
        if (!$result['success']) {
            return $this->jsonResponse(['success' => false, 'error' => $result['error'] ?? 'Invalid module name'], 400);
        }

        return $this->jsonResponse(['success' => true, 'data' => [
            'state' => $result['state'],
            'module' => $result['module'],
            'current' => $result['current'],
        ]]);
    }

    /**
     * Module de site que le vhost doit servir, d'après la sélection de l'étape des modules.
     * `site_module.site` vaut soit l'option de plateforme elle-même, soit — pour un site démo —
     * le module de ce site (cf. `saveModuleSelectionAction`) :
     *
     *  - site démo   → le module installé par Composer (`MelisDemoCms`…) ;
     *  - `NewSite`   → le module créé sous `module/MelisSites/`, saisi dans le formulaire ;
     *  - core seul / plateforme nue → aucun site installé, donc rien à adopter.
     *
     * Se rabattre sur `website_module` dans tous les cas (ce que faisait cette méthode) revenait
     * à réappliquer le champ « Module name » — pré-rempli avec le MELIS_MODULE courant et
     * masqué hors option « nouveau site ». Une installation de site démo redemandait donc la
     * valeur déjà en place : l'applier n'avait rien à faire et le `.env` gardait l'ancien nom.
     */
    private function resolveSiteModule(\Laminas\Session\Container $container): string
    {
        $selection = $container['site_module'] ?? [];
        $site = (string) ($selection['site'] ?? '');

        if ($site === '' || in_array($site, ['MelisCoreOnly', 'None'], true)) {
            return '';
        }

        if ($site === 'NewSite') {
            return trim((string) ($selection['website_module'] ?? ''));
        }

        return trim($site);
    }

    /** Étape finale — avancement de la demande ci-dessus (applied / failed / pending / idle). */
    public function moduleStateAction(): HttpResponse
    {
        return $this->jsonResponse(['success' => true, 'data' => $this->getSetupWizardService()->getModuleApplyState()]);
    }

    private function getSetupWizardService(): SetupWizardService
    {
        return new SetupWizardService($this->getServiceManager());
    }

    private function jsonResponse(array $data, int $status = 200): HttpResponse
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setStatusCode($status);
        $response->getHeaders()->addHeaders([
            'Content-Type'           => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response;
    }
}
