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
        $modules = $this->getSetupWizardService()->listAvailableModules();

        return $this->jsonResponse(['success' => true, 'data' => ['modules' => $modules]]);
    }

    /**
     * Step 3.1 — enregistre la sélection de modules à installer/télécharger. Même stockage
     * session (`install_modules` / `download_modules`) que `setDownloadableModulesAction`
     * legacy, pour que les étapes suivantes (téléchargement composer, activation) — qui
     * restent partagées avec le carousel — retrouvent la sélection quel que soit le chemin
     * emprunté. Le flux multi-framework/demo-tool du legacy (`otherFWData`) n'est pas encore
     * porté ici — cas marginal, hors scope de cette itération.
     *
     * Body attendu : { modules: [{ name, package }] }
     */
    public function saveModuleSelectionAction(): HttpResponse
    {
        $body = json_decode($this->getRequest()->getContent(), true) ?? [];
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

        $container = new \Laminas\Session\Container('melisinstaller');
        $container['install_modules'] = $installModules;
        $container['download_modules'] = $downloadModules;

        return $this->jsonResponse(['success' => true, 'data' => ['count' => count($installModules)]]);
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
