<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)b
 *
 */

namespace MelisInstaller\Controller;

use PDO;
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class InstallerController extends AbstractActionController
{

    protected $steps = ['sysconfig', 'vhost', 'fsrights', 'environments', 'dbconn', 'selmod', 'pf_init'];
    protected $includedModules = ['MelisEngine', 'MelisFront'];

    public function indexAction()
    {
        $resources = $this->getServiceLocator()->get('MelisAssetManagerWebPack')->getAssets();
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');

        $locales = $this->getTranslationsLocale();


        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent();

        $route = $routeMatch->getRouteMatch()->getMatchedRouteName();

        if ($route == 'melis-backoffice/setup' || $route == 'melis-backoffice/application-MelisInstaller') {
            $this->layout()->isMelisInstallerRoute = true;
            $this->layout()->installerJsFiles = $resources['js'];
            $this->layout()->installerCssFiles = $resources['css'];
        }

        $container = new Container('melisinstaller');

        // Website configuration
        $webConfigOption = $this->getForm('melis_installer/forms/melis_installer_webconfig_option');
        $webLangForm = $this->getForm('melis_installer/forms/melis_installer_web_lang');
        $webForm = $this->getForm('melis_installer/forms/melis_installer_webform');
        $otherFrameworkForm = $this->getForm('melis_installer/forms/melis_installer_other_frameworks');

        $showWebForm = false;
        // WebConfigOption preload values from Session/Container
        if (isset($container['cms_data']['weboption'])) {

            // Getting the default values for Website confguration options from app.interface.php datas
            $config = $this->getServiceLocator()->get('config');
            $defaultWebConfigOptions = $config['plugins']['melis_installer']['datas']['default_website_config_options'];
            if (array_key_exists($container['cms_data']['weboption'], $defaultWebConfigOptions) && $container['cms_data']['weboption'] != 'None') {
                $showWebForm = true;
            }

            $webConfigOption->get('weboption')->setValue($container['cms_data']['weboption']);
        }

        // WebLangForm preload values from Session/Container
        if (isset($container['cms_data']['web_lang'])) {
            $webLangForm->get('language')->setValue($container['cms_data']['web_lang']);
        }

        // WebForm preload from Session/Container
        if (isset($container['cms_data']['web_form'])) {
            foreach ($container['cms_data']['web_form'] as $key => $val) {
                $webForm->get($key)->setValue($val);
            }
        }

        // Melis Configuration
        $createUserForm = $this->getForm('melis_installer/forms/melis_installer_user_data');

        /*
         * create session for steps, 
         * this makes sure that we reset the steps status if the page/browser has been refreshed or closed
         */
        $container->steps = [];

        // pre-load user data if set from the session
        if (isset($container['user_data'])) {
            foreach ($container['user_data'] as $key => $userData) {
                $createUserForm->get($key)->setValue($userData);
            }
        }

        $selectedModules = [];
        if (!empty($container['install_modules'])) {
            $selectedModules = $container['install_modules'];
        }
        $coreContainer = new Container('meliscore');

        $currentLocale = isset($coreContainer['melis-lang-locale']) ? $coreContainer['melis-lang-locale'] : 'en_EN';

        $requiredModules = [];
        if (!empty($container['cms_data']['required_modules'])) {
            $requiredModules = $container['cms_data']['required_modules'];
        }

        $currentSite = isset($container['site_module']['site']) ? $container['site_module']['site'] : 'MelisCoreOnly';
        $webConfigOption->get('weboption')->setValue($currentSite);
        $modules = $installHelper->getPackagistMelisModules();
        $this->parseModulesList($modules);
        $sites = $installHelper->getPackagistMelisSites();

        $view = new ViewModel();
        // pre-loaded stuffs 
        $view->currentLocale = $currentLocale;
        $view->setupLocales = $locales;
        $view->setup1_0 = $this->systemConfigurationChecker();
        $view->setup1_0_phpversion = phpversion();
        $view->setup1_apache = $this->apacheSetupChecker();
        $view->setup1_1 = $this->vHostSetupChecker();
        $view->setup1_2 = $this->checkDirectoryRights();
        $view->setup1_3 = $this->getEnvironments();
        $view->setup1_3_current = $this->getCurrentPlatform();
        $view->setup1_3_env_name = $installHelper->getMelisPlatform();
        $view->setup1_3_env_domain = $this->getRequest()->getServer()->SERVER_NAME;
        $view->setup2 = $this->loadDatabaseCredentialFromSession();

        $view->setup3_webConfigOption = $webConfigOption;
        $view->setup3_showWebForm = $showWebForm;
        $view->setup3_webLangForm = $webLangForm;
        $view->setup3_webForm = $webForm;
        $view->setup3_createUserForm = $createUserForm;

        $view->setup3_3_selected = $selectedModules;
        $view->setup3_3_requiredModules = $requiredModules;

        $view->setup3_3_otherFrameworkForm = $otherFrameworkForm;

        $view->packagistMelisModules = $modules['packages'];
        $view->packagistSiteModules = $sites['packages'];

        return $view;
    }

    public function getTranslationsLocale()
    {
        $modulesSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
        $modules = $modulesSvc->getAllModules();
        $modulePath = $modulesSvc->getModulePath('MelisInstaller');
        $path = $modulePath . '/language/';
        $dir = scandir($path);
        $files = [];
        foreach ($dir as $file) {
            if (is_file($path . $file)) {
                $files[] = $file;
            }
        }
        $locales = [];
        foreach ($files as $file) {
            $locale = explode('.', $file);
            $locales[] = $locale[0];
        }
        // re-add locales to get the unique locales and fix proper array indexing
        $uniqueLocales = array_unique($locales);
        $newUniqueLocales = [];
        foreach ($uniqueLocales as $locale) {
            $newUniqueLocales[] = $locale;
        }

        return $newUniqueLocales;
    }

    /**
     * Retrieves the array configuration from app.forms
     *
     * @param string $configPath
     *
     * @return Form
     */
    protected function getForm($configPath)
    {
        $form = null;
        $melisConfig = $this->serviceLocator->get('MelisInstallerConfig');
        $formConfig = $melisConfig->getItem($configPath);

        if ($formConfig) {
            $factory = new \Zend\Form\Factory();
            $formElements = $this->getServiceLocator()->get('FormElementManager');
            $factory->setFormElementManager($formElements);
            $form = $factory->createForm($formConfig);
        }

        return $form;
    }

    /**
     * Checks the PHP Environment and Variables
     * @return Array
     */
    protected function systemConfigurationChecker()
    {
        $response = [];
        $data = [];
        $errors = [];
        $dataExt = [];
        $dataVar = [];
        $checkDataExt = 0;
        $success = 0;

        $translator = $this->getServiceLocator()->get('translator');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');

        $installHelper->setRequiredExtensions([
            'openssl',
            'json',
            'pdo_mysql',
            'intl',
            'zip'
        ]);


        foreach ($installHelper->getRequiredExtensions() as $ext) {
            if (in_array($ext, $installHelper->getPhpExtensions())) {
                $dataExt[$ext] = $installHelper->isExtensionsExists($ext);
            } else {
                $dataExt[$ext] = sprintf($translator->translate('tr_melis_installer_step_1_0_extension_not_loaded'), $ext);
            }

        }

        $dataVar = $installHelper->checkEnvironmentVariables();

        // checks if all PHP configuration is fine
        if (!empty($dataExt)) {
            foreach ($dataExt as $ext => $status) {
                if ((int) $status === 1) {
                    $checkDataExt = 1;
                } else {
                    $checkDataExt = 0;
                }
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

        // last checking
        if (empty($errors) && $checkDataExt === 1) {
            $success = 1;
        }

        $response = [
            'success' => $success,
            'errors' => $errors,
            'data' => [
                'extensions' => $dataExt,
                'variables' => $dataVar,
            ],

        ];

        return $response;
    }

    /**
     * Checks if the Vhost platform and module variable are set
     * @return Array
     */
    protected function vHostSetupChecker()
    {
        $translator = $this->getServiceLocator()->get('translator');

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

        $response = [
            'success' => $success,
            'errors' => $error,
            'data' => [
                'platform' => $platform,
                'module' => $module,
            ],
        ];

        return $response;
    }

    /**
     * Checks if the Vhost platform and module variable are set
     * @return Array
     */
    protected function apacheSetupChecker()
    {
        $success = 0;
        $errors = [];
        $results = [];
        $translator = $this->getServiceLocator()->get('translator');
        $requiredModules = array('mod_headers','mod_alias','mod_deflate');

        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            foreach($requiredModules as $requiredModule){
                $results[$requiredModule] = in_array($requiredModule, $modules) ? true : false ;
            }

        } else {
            foreach($requiredModules as $requiredModule){
                $results[$requiredModule] = getenv($requiredModule)=='On' ? true : false ;
            }
        }

        foreach($results as $key => $result){
            if($result === false){
                $errors[$key] =  sprintf($translator->translate('tr_melis_installer_apache_module_disabled'),$key);
            }
        }

        $success = count($errors) > 0 ? 0 : 1;

        $response = [
            'success' => $success,
            'errors' => $errors,
            'result' => $results,
        ];

        return $response;
    }

    /**
     * Check the directory rights if it is writable or not
     * @return Array
     */
    protected function checkDirectoryRights()
    {
        $translator = $this->getServiceLocator()->get('translator');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');

        $configDir = $installHelper->getDir('config');
        $module = $this->getModuleSvc()->getAllModules();

        $success = 0;
        $errors = [];
        $data = [];

        for ($x = 0; $x < count($configDir); $x++) {
            $configDir[$x] = 'config/' . $configDir[$x];
        }
        array_push($configDir, 'config');

        /**
         * Add config platform, MelisSites and public dir to check permission
         */
        array_push($configDir, 'config/autoload/platforms/');
        array_push($configDir, 'module/MelisModuleConfig/');
        array_push($configDir, 'module/MelisModuleConfig/languages');
        array_push($configDir, 'module/MelisModuleConfig/config');
        array_push($configDir, 'module/MelisSites/');
        array_push($configDir, 'dbdeploy/');
        array_push($configDir, 'dbdeploy/data');
        array_push($configDir, 'public/');
        array_push($configDir, 'cache/');
        array_push($configDir, 'test/');
        array_push($configDir, 'thirdparty/');

        for ($x = 0; $x < count($module); $x++) {
            $module[$x] = $this->getModuleSvc()->getModulePath($module[$x], false) . '/config';
        }

        $dirs = array_merge($configDir, $module);

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

        if (empty($errors)) {
            $success = 1;
        }

        $response = [
            'success' => $success,
            'errors' => $errors,
            'data' => $results,
        ];


        return $response;
    }

    public function getModuleSvc()
    {
        $moduleSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');

        return $moduleSvc;
    }

    /**
     * Returns the set environments in the session
     * @return Array
     */
    protected function getEnvironments()
    {
        $env = [];

        $container = new Container('melisinstaller');
        if (isset($container['environments']) && isset($container['environments']['new'])) {
            $env = $container['environments']['new'];
        }

        return $env;
    }

    /**
     * Returns the current values environment in the session
     * @return Array
     */
    protected function getCurrentPlatform()
    {
        $env = [];

        $container = new Container('melisinstaller');
        if (isset($container['environments']) && isset($container['environments']['default_environment'])) {
            $env = $container['environments']['default_environment'];
        }

        return $env;
    }

    /**
     * Retrieve's the current set values for database credentials to array
     * @return Array
     */
    protected function loadDatabaseCredentialFromSession()
    {
        $data = [];

        $container = new Container('melisinstaller');
        if (isset($container['database'])) {
            $data = $container['database'];
        }

        return $data;
    }

    public function newEnvironmentFormAction()
    {
        $count = (int) $this->params()->fromQuery('count', 1);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->count = $count;

        return $view;
    }

    public function changeLangAction()
    {
        $success = 0;

        if ($this->getRequest()->isPost()) {
            $locale = $this->getRequest()->getPost('langLocale');

            $container = new Container('meliscore');
            $container['melis-lang-locale'] = $locale;
            $success = 1;
        }

        return new JsonModel(['success' => $success]);
    }

    /**
     * This function is used for rechecking the status the desired step
     * @return \Zend\View\Model\JsonModel
     */
    public function checkSysConfigAction()
    {
        $success = 0;
        $errors = [];

        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->systemConfigurationChecker();
            $success = $response['success'];
            $errors = $response['errors'];

            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[0]] = ['page' => 1, 'success' => $success];
        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    /**
     * This step rechecks the Step 1.1 which is the apache Setup just to check
     * that everything fine.
     * @return \Zend\View\Model\JsonModel
     */
    public function checkApacheSetupAction()
    {
        $success = 0;
        $errors = [];
        $results = [];
        $translator = $this->getServiceLocator()->get('translator');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $requiredModules = array('mod_headers','mod_alias','mod_deflate');

            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                foreach($requiredModules as $requiredModule){
                    $results[$requiredModule] = in_array($requiredModule, $modules) ? true : false ;
                }

            } else {
                foreach($requiredModules as $requiredModule){
                    $results[$requiredModule] = getenv($requiredModule)=='On' ? true : false ;
                }
            }

            foreach($results as $key => $result){
                if($result === false){
                    $errors[$key] =  sprintf($translator->translate('tr_melis_installer_apache_module_disabled'),$key);
                }
            }

            $success = count($errors) > 0 ? 1 : 1;
        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
            'result' => $results,
        ]);
    }



    /**
     * This step rechecks the Step 1.2 which is the Vhost Setup just to check
     * that everything fine.
     * @return \Zend\View\Model\JsonModel
     */
    public function checkVhostSetupAction()
    {
        $success = 0;
        $errors = [];

        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->vHostSetupChecker();
            $success = $response['success'];
            $errors = $response['errors'];

            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[1]] = ['page' => 2, 'success' => $success];
        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    /**
     * Rechecks Step 1.4File System Rights
     * @return \Zend\View\Model\JsonModel
     */
    public function checkFileSystemRightsAction()
    {
        $success = 0;
        $errors = [];

        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->checkDirectoryRights();
            $success = $response['success'];
            $errors = $response['errors'];

            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[2]] = ['page' => 3, 'success' => $success];
        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    public function newEnvironmentAction()
    {
        $success = 0;
        $errors = [];
        $request = [];
        // add listeners here for MelisCms and MelisCore to listen
        if ($this->getRequest()->isPost()) {
            $data = get_object_vars($this->getRequest()->getPost());
            $currentPlatformDomain = $data['domain'];
            $domainEnv = [];

            // remove domain key
            unset($data['domain']);
            for ($x = 0; $x <= count($data) / 2; $x++) {
                $environmentName = 'environment_name_' . ($x + 1);
                $domainName = 'domain_' . ($x + 1);
                $sendEmail = 'send_email_' . ($x + 1);
                $errorReporting = 'error_reporting_' . ($x + 1);
                if (isset($data[$environmentName]) && isset($data[$domainName]) &&
                    $data[$environmentName] != '' && $data[$domainName] != '') {
                    $domainEnv[] = [
                        'environment' => $data[$environmentName],
                        'domain' => $data[$domainName],
                        'send_email' => isset($data[$sendEmail]) ? $data[$sendEmail] : 'off',
                        'error_reporting' => isset($data[$errorReporting]) ? $data[$errorReporting] : 0,
                    ];
                }

            }

            $request = [
                'currentPlatform' => [
                    'platform_domain' => $currentPlatformDomain,
                    'send_email' => isset($data['send_email']) && $data['send_email'] == 'on' ? 1 : 0,
                    'error_reporting' => $data['error_reporting'],
                ],
                'siteDomain' => $domainEnv,

            ];

            // add the new values
            $this->getEventManager()->trigger('melis_install_new_platform_start', $this, $request);

            $success = 1;

            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[3]] = ['page' => 4, 'success' => $success];;

        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
        // also here, add listeners
    }

    public function deleteEnvironmentAction()
    {
        $success = 0;
        if ($this->getRequest()->isPost()) {
            $data = get_object_vars($this->getRequest()->getPost());
            $response = $this->getEventManager()->trigger('melis_install_delete_environment_start', $this, $data);

            if (!empty($response)) {
                $success = $response[0]['success'];
            }


        }

        return new JsonModel([
            'success' => $success,
        ]);
    }

    public function testDatabaseConnectionAction()
    {
        $success = 0;
        $errors = [];
        $translator = $this->getServiceLocator()->get('translator');
        if ($this->getRequest()->isPost()) {
            $data = get_object_vars($this->getRequest()->getPost());
            $installHelper = $this->getServiceLocator()->get('InstallerHelper');


            if (!empty($data['hostname'])) {
                if (!empty($data['database'])) {
                    if (!empty($data['username'])) {
                        $response = $installHelper->checkMysqlConnection($data['hostname'], $data['database'], $data['username'], $data['password']);

                        if ($response['isConnected']) {
                            if ($response['isMysqlPasswordCorrect']) {
                                if ($response['isDatabaseExists']) {
                                    if ($response['isDatabaseCollationNameValid']) {
                                        $success = 1;
                                        // add status to session
                                        $container = new Container('melisinstaller');
                                        $container['steps'][$this->steps[4]] = ['page' => 5, 'success' => $success];

                                        $container = new Container('melisinstaller');
                                        $container['database'] = $data;

                                        $_SESSION['database'] = $data;

                                    } else {
                                        $errors = [
                                            'Collation' => ['invalidCollation' => $translator->translate('tr_melis_installer_layout_dbcon_collation_name_invalid'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_collation_name')],
                                        ];
                                    }
                                } else {
                                    $errors = [
                                        'database' => ['invalidDatabase' => $translator->translate('tr_melis_installer_dbcon_form_db_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_db')],
                                        'username' => ['invalidUsername' => $translator->translate('tr_melis_installer_dbcon_form_user_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_user')],
                                        'password' => ['invalidPassword' => $translator->translate('tr_melis_installer_dbcon_form_pass_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_pass')],
                                    ];
                                }
                            } else {
                                $errors = [
                                    'password' => ['invalidPassword' => $translator->translate('tr_melis_installer_dbcon_form_pass_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_pass')],
                                ];
                            }
                        } else {
                            $errors = ['Host' => ['unreachableHost' => $translator->translate('tr_melis_installer_dbcon_form_host_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_host')]];
                        }
                    } else {
                        $errors = ['username' => ['emptyUsername' => $translator->translate('tr_melis_installer_dbcon_form_user_empty'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_user')]];
                    }

                } else {
                    $errors = ['database' => ['emptyDatabase' => $translator->translate('tr_melis_installer_dbcon_form_db_empty'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_db')]];
                }

            } else {
                $errors = ['Host' => ['unreachableHost' => $translator->translate('tr_melis_installer_dbcon_form_host_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_host')]];
            }

        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    public function addInstallableModulesAction()
    {
        $container = new Container('melisinstaller');
        $container['steps'][$this->steps[6]] = ['page' => 6, 'success' => 1];
        $container['install_modules'] = [];
        if ($this->getRequest()->isXmlHttpRequest()) {
            $data = get_object_vars($this->getRequest()->getPost());

            // remove pre-set data
            unset($data['_default']);

            if (!empty($data)) {
                $container['install_modules'] = array_values($data);
            }
        }

        return new JsonModel(['success' => 1]);
    }

    public function setWebConfigAction()
    {
        $success = 0;
        $errors = [];
        $appConfigForm = [];
        $requiredModules = [];

        $translator = $this->getServiceLocator()->get('translator');
        $melisMelisInstallerConfig = $this->getServiceLocator()->get('MelisInstallerConfig');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postValues = $request->getPost();
            // Website configuration option
            $webConfigOption = $this->getForm('melis_installer/forms/melis_installer_webconfig_option');
            $webConfigOption->setData($postValues);

            if ($webConfigOption->isValid()) {
                $data = $webConfigOption->getData();
                $container = new Container('melisinstaller');
                $container['cms_data'] = $data;

                // Getting the default values for Website confguration options from app.interface.php datas
                $config = $this->getServiceLocator()->get('config');
                $defaultWebConfigOptions = $config['plugins']['melis_installer']['datas']['default_website_config_options'];
                // Checking if the Website option
                if (array_key_exists($container['cms_data']['weboption'], $defaultWebConfigOptions) && $container['cms_data']['weboption'] != 'None') {

                    $webForm = $this->getForm('melis_installer/forms/melis_installer_webform');
                    $webForm->setData($postValues);
                    if ($webForm->isValid()) {

                        $melisSite = $_SERVER['DOCUMENT_ROOT'] . '/../module/MelisSites';
                        if (!file_exists($melisSite)) {
                            mkdir($melisSite, 0777);
                            $installHelper->filePermission($melisSite);
                        }

                        // checking if the target module name is existing on the target dir
                        if (!file_exists($melisSite . '/' . $postValues['website_module'])) {
                            $data = $webForm->getData();
                            $container['cms_data']['web_form'] = $data;
                            $container['cms_data']['web_lang'] = $postValues['language'];
                            $success = 1;
                        } else {
                            array_push($errors, [
                                "hasError" => sprintf($translator->translate("tr_melis_installer_web_form_module_exists"), $postValues['website_module']),
                                "label" => $translator->translate("tr_melis_installer_web_form_module_label"),
                            ]);
                        }
                    } else {
                        $errors = $webForm->getMessages();
                        $appConfigForm = $melisMelisInstallerConfig->getItem('melis_installer/forms/melis_installer_webform');
                        $appConfigForm = $appConfigForm['elements'];
                    }
                } elseif ($container['cms_data']['weboption'] != 'None') {
                    if (!empty(getenv('MELIS_MODULE'))) {
                        if (!preg_match('/[^a-z_\-0-9]/i', getenv('MELIS_MODULE'))) {

                            // Website Configuration chooses a Demo Site
                            $melisSite = $_SERVER['DOCUMENT_ROOT'] . '/../module/MelisSites';

                            if (!file_exists($melisSite)) {
                                mkdir($melisSite, 0777);
                                $installHelper->filePermission($melisSite);
                            }

                            // checking if the target module name is existing on the target dir
                            //if(file_exists($melisSite.'/'.$container['cms_data']['weboption']))
                            if (file_exists($melisSite . '/' . getenv('MELIS_MODULE'))) {
                                array_push($errors, [
                                    "hasError" => sprintf($translator->translate("tr_melis_installer_web_form_module_exists"), $container['cms_data']['weboption']),
                                    "label" => $translator->translate("tr_melis_installer_web_form_module_label"),
                                ]);
                            }

                            /**
                             * Required modules needed to install Demo Site from config
                             */
                            $demoDir = $this->getModuleSvc()->getModulePath('MelisInstaller') . '/etc/' . $container['cms_data']['weboption'];
                            $siteConfig = require $demoDir . '/config/' . $container['cms_data']['weboption'] . '.config.php';

                            $requiredModules = $siteConfig['site'][$container['cms_data']['weboption']]['datas']['required_modules'];
                            $container['cms_data']['required_modules'] = $requiredModules;

                            if (empty($errors)) {
                                $success = 1;
                            }
                        } else {
                            array_push($errors, [
                                "label" => $translator->translate("tr_melis_installer_web_form_module_label"),
                                "hasError" => $translator->translate("tr_melis_installer_web_config_invalid_vhost_module_name"),
                            ]);
                        }
                    } else {
                        array_push($errors, [
                            "label" => $translator->translate("tr_melis_installer_web_form_module_label"),
                            "hasError" => $translator->translate("tr_melis_installer_web_config_empty_vhost_module_name"),
                        ]);
                    }
                } elseif ($container['cms_data']['weboption'] == 'None') {
                    $success = 1;
                }
            } else {
                $errors = $webConfigOption->getMessages();
                $appConfigForm = $melisMelisInstallerConfig->getItem('melis_installer/forms/melis_installer_webconfig_option');
                $appConfigForm = $appConfigForm['elements'];
            }

            if (!empty($errors)) {
                foreach ($errors as $keyError => $valueError) {
                    foreach ($appConfigForm as $keyForm => $valueForm) {
                        if ($valueForm['spec']['name'] == $keyError && !empty($valueForm['spec']['options']['label'])) {
                            $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                        }
                    }
                }
            }
        }

        if ($success) {
            $container['steps'][$this->steps[5]] = ['page' => 7, 'success' => $success];
        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
            'requiredModules' => $requiredModules,
        ]);
    }

    /**
     * @return JsonModel
     */
    public function setDownloadableModulesAction()
    {

        $request = $this->getRequest();
        $packages = [];
        $status = true;
        $message = '';

        if ($request->isPost()) {

            $container = new Container('melisinstaller');

            $post = $request->getPost()->toArray();

            $packages = isset($post['packages']) ? $post['packages'] : null;
            $modules = isset($post['modules']) ? $post['modules'] : null;
            $siteLang = isset($post['siteLang']) ? $post['siteLang'] : [];
            $siteData = isset($post['siteData']) ? $post['siteData'] : [];
            $otherFWData = isset($post['otherFWData']) ? $post['otherFWData'] : [];

            if ($siteLang) {
                parse_str($siteLang, $siteLang);
            }

            if ($siteData) {
                parse_str($siteData, $siteData);
            }

            if ($packages && $modules) {

                $downloadModules = [];
                $container['install_modules'] = $modules;

                $ctr = 0;
                foreach ($modules as $module) {
                    $downloadModules[$module] = $packages[$ctr];
                    $ctr++;
                }

                $container['download_modules'] = $downloadModules;
            }

            $container['site_module'] = array_merge(['site' => ($post['site'] ?? $this->selectedSite())], $siteLang, $siteData);

            if ($this->isUsingCoreOnly()) {
                $container['download_modules'] = [];
                $container['install_modules'] = [];
            }

            /**
             * Checking installation for other framework
             */
            $fwStatus = $this->installOtherFramework($otherFWData);
            if(!$fwStatus['success']){
                $status = false;
                $message = $fwStatus['message'];
            }
        }

        return new JsonModel(['success' => $status, 'packages' => $packages, 'message' => $message]);

    }

    /**
     * Function to install other frameworks
     *
     * @param $otherFWData
     * @return array
     */
    protected function installOtherFramework($otherFWData)
    {
        $result = [
            'success' => true,
            'message' => ''
        ];

        if(!empty($otherFWData)){
            $container = new Container('melisinstaller');

            parse_str($otherFWData, $otherFWData);

            $isEnableMultiFw = (!empty($otherFWData['enable_multi_fw']) && $otherFWData['enable_multi_fw'] == 'true') ? true : false;
            $includeDemoTool = (!empty($otherFWData['include_demo_tool']) && $otherFWData['include_demo_tool'] == 'yes') ? true : false;
//            $includeDemoSite = (!empty($otherFWData['include_demo_site']) && $otherFWData['include_demo_site'] == 'true') ? true : false;
            $frameworkName = (!empty($otherFWData['framework_name'])) ? $otherFWData['framework_name'] : '';

            $container['is_multi_fw'] = $isEnableMultiFw;

            /**
             * Check if multi framework coding is enabled
             */
            if($isEnableMultiFw){
                //check if framework name is not empty
                if(!empty($frameworkName)) {
                    //add framework name to container
                    $container['framework_name'] = $frameworkName;
                    $ucFirstFrameworkName = ucfirst($frameworkName);

                    //Include MelisPlatformFrameworks module
                    $mpFwModule = ['MelisPlatformFramework'.$ucFirstFrameworkName => 'melisplatform/melis-platform-framework-'.$frameworkName];
//                    array_push($container['install_modules'], 'MelisPlatformFrameworks');
                    $container['download_modules'] = array_merge($container['download_modules'], $mpFwModule);
                    $container['install_platform_framework'] = $mpFwModule;

                    //prepare demo tool module path and name
                    $demoModuleName = 'MelisPlatformFramework' . $ucFirstFrameworkName . 'DemoTool';
                    $demoModulePath = 'melisplatform/melis-platform-framework-' . $frameworkName . '-demo-tool';
                    //prepare demo site module path and name
//                    $siteModuleName = 'MelisPlatformFramework' . $ucFirstFrameworkName . 'DemoSite';
//                    $siteModulePath = 'melisplatform/melis-platform-framework-' . $frameworkName . '-demo-site';


                    /**
                     * Check if we include demo tool
                     * or demo site
                     */
                    //store demo tool to container
                    $container['install_fw_demo_tool'] = [];
                    //store demo site on container
//                    $container['install_fw_demo_site'] = [];

                    if ($includeDemoTool) {
                        /**
                         * Include demo tool to the list of module
                         * to download
                         */
                        array_push($container['install_modules'], $demoModuleName);
                        $container['download_modules'] = array_merge($container['download_modules'], [$demoModuleName => $demoModulePath]);

                        $container['install_fw_demo_tool'] = [$demoModuleName => $demoModulePath];
                    }
                    //check if we include demo site
//                    if ($includeDemoSite) {
//
//                    }
                }else{
                    $result['success'] = false;
                    $result['message'] = 'Please choose a framework to install.';
                }
            }
        }

        return $result;
    }

    protected function isUsingCoreOnly()
    {
        if ($this->getSelectedSiteOption() == 'MelisCoreOnly') {
            return true;
        }

        return false;
    }

    protected function getSelectedSiteOption()
    {
        $container = new Container('melisinstaller');
        $site = $container['site_module'];
        $siteName = 'None';
        if ($site) {
            $siteName = $site['site'];
        }

        return $siteName;
    }

    /**
     * @return mixed
     */
    protected function isMultiFramework()
    {
        $container = new Container('melisinstaller');
        if(isset($container['is_multi_fw']))
            return $container['is_multi_fw'];

        return false;
    }

    public function addModulesToComposerAction()
    {
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {

            // check if composer.json is readable
            $composer = $_SERVER['DOCUMENT_ROOT'] . '/../composer.json';
            if (!is_readable($composer) || !is_writable($composer)) {
                chmod($composer, 0777);
            }

            $config = $this->getServiceLocator()->get('MelisInstallerConfig');

            $autoInstallModules = $config->getItem('melis_installer/datas/module_auto_install');
            $container = new Container('melisinstaller');
            $downloadableModules = isset($container['download_modules']) ? $container['download_modules'] : [];


            /**
             * Install needed modules for
             * thirdparty framework
             */
            if($this->isMultiFramework()) {
                if($this->isUsingCoreOnly()) {

                    if(!empty($container['install_platform_framework']))
                        $autoInstallModules = array_merge($autoInstallModules, $container['install_platform_framework']);

                    if (!empty($container['install_fw_demo_tool'])) {
                        $autoInstallModules = array_merge($autoInstallModules, $container['install_fw_demo_tool']);
                    }
//                    if (!empty($container['install_fw_demo_site'])) {
//                        $autoInstallModules = array_merge($autoInstallModules, $container['install_fw_demo_site']);
//                    }
                }
            }

            $downloadableModules = array_merge($autoInstallModules, $downloadableModules);
            $downloadableModules = implode(' ', $downloadableModules);

            $composerSvc = $this->getServiceLocator()->get('MelisComposerService');

            set_time_limit(0);
            ini_set('memory_limit', -1);

            if (!$this->isUsingCoreOnly()) {
                $composerSvc->download($downloadableModules, null, true);
            } else {
                $autoInstallModules = implode(' ', $autoInstallModules);
                $composerSvc->download($autoInstallModules, null, true);
            }
        }

        $view = new ViewModel();
        $view->setTerminal(true);

        return $view;

    }

    public function downloadModulesAction()
    {
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {

            set_time_limit(0);
            ini_set('memory_limit', -1);

            $composerSvc = $this->getServiceLocator()->get('MelisComposerService');
            $composerSvc->update();

        }

        $view = new ViewModel();
        $view->setTerminal(true);

        return $view;
    }

    public function rebuildAutoloaderAction()
    {

        $success = 0;
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            set_time_limit(0);
            ini_set('memory_limit', -1);

            $composerSvc = $this->getServiceLocator()->get('MelisComposerService');
            $composerSvc->dumpAutoload();

            $success = 1;
        }

        return new JsonModel(['success' => $success]);

    }

    public function activateModulesAction()
    {
        $request = $this->getRequest();
        $modules = [];

        if ($request->isXmlHttpRequest()) {

            set_time_limit(0);
            ini_set('memory_limit', -1);

            $config = $this->getServiceLocator()->get('MelisConfig');

            $autoInstallModules = array_keys($config->getItem('melis_installer/datas/module_auto_install'));
            $defaultModules = $config->getItem('melis_installer/datas/module_default');
            $container = new Container('melisinstaller');
            $downloadableModules = isset($container['download_modules']) ? array_keys($container['download_modules']) : [];
            $moduleSvc = $this->getServiceLocator()->get('MelisInstallerModulesService');

            // check if the module exists before activating
            $modules = array_merge($autoInstallModules, $downloadableModules);
            $ctr = 0;
            foreach ($modules as $module) {
                $modulePath = $moduleSvc->getModulePath($module);
                if (!$modulePath) {
                    unset($modules[$ctr]);
                }

                $ctr++;
            }

            // load site module in installer
            if (!$this->isUsingCoreOnly()) {
                $siteConfiguration = isset($container['site_module']) ? $container['site_module'] : null;

                if (!in_array('MelisEngine', $modules))
                    array_push($modules, 'MelisEngine');
                if (!in_array('MelisFront', $modules))
                    array_push($modules,'MelisFront');

//                if (in_array($siteConfiguration['site'], ['NewSite', 'None'])) {
//                    array_push($modules, getenv('MELIS_MODULE'));
//                }
            }

            /**
             * check if multi framework
             */
            if($this->isMultiFramework()) {
                //add MelisPlatformFrameworks module to activate
                if(!in_array('MelisPlatformFrameworks', $modules))
                    array_push($modules,'MelisPlatformFrameworks');
                //remove MelisPlatformFramework+FrameworkName since this is not a zend module
                if (in_array('MelisPlatformFramework' . ucfirst($container['framework_name']), $modules))
                    $modules = array_diff($modules, ['MelisPlatformFramework' . ucfirst($container['framework_name'])]);
            }

            $moduleSvc->createModuleLoader('config/', array_merge($modules, ['MelisInstaller']), $defaultModules);
            array_push($modules, 'MelisCore');
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->modules = $modules;

        return $view;
    }

    /**
     * Download thirdparty framework
     *
     * @return ViewModel
     */
    public function downloadFrameworkSkeletonAction()
    {
        $container = new Container('melisinstaller');
        $fwName = $container['framework_name'];
        //download framework skeleton
        try {
//            $result = $this->getEventManager()->trigger('melis_platform_frameworks_download_framework_skeleton', $this, ['framework_name' => $fwName]);
//            $result = $result->first();

            $success = false;
            $message = '';

            \MelisCore\ModuleComposerScript::setNoPrint();
            $result = \MelisCore\ModuleComposerScript::executeScripts();
            if(!empty($result)){
                foreach($result as $key => $val){
                    if($key == 'MelisPlatformFramework'.ucfirst($fwName)){
                        foreach($val as $res){
                            $message = $res['message'];
                            $success = $res['success'];
                            break;
                        }
                    }
                }
            }
            $color = ($success) ? '#02de02' : '#ff190d';
        }catch (\Exception $ex){
            $message = $ex->getMessage();
            $color = '#ff190d';
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->message = $message;
        $view->messageColor = $color;

        return $view;
    }

    public function execDbDeployAction()
    {

        $request = $this->getRequest();
        $modules = [];
        if ($request->isXmlHttpRequest()) {

            $container = new Container('melisinstaller');
            $database = isset($container['database']) ? $container['database'] : null;

            if ($database) {

                $installHelper = $this->getServiceLocator()->get('InstallerHelper');
                $config = $this->getServiceLocator()->get('MelisInstallerConfig');
                $autoInstallModules = array_keys($config->getItem('melis_installer/datas/module_auto_install'));

                $downloadableModules = isset($container['download_modules']) ? array_keys($container['download_modules']) : [];
                $modules = array_merge(['MelisCore'], $autoInstallModules, $downloadableModules);
                $moduleSvc = $this->getServiceLocator()->get('MelisInstallerModulesService');

                if ($modules && is_array($modules)) {

                    // -> Create Database data SQL file configured from what was filled in forms
                    $fileName = $installHelper->getMelisPlatform() . '.php';
                    $configValue = [
                        'db' => [
                            'dsn' => sprintf('mysql:dbname=%s;host=%s;charset=utf8', $database['database'], $database['hostname']),
                            'username' => $database['username'],
                            'password' => $database['password'],
                            'driver_options' => [
                                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                            ],
                        ],
                    ];

                    $config = new Config($configValue, true);
                    $writer = new PhpArray();
                    $conf = $writer->toString($config);
                    if (is_writable('config/autoload/platforms/')) {
                        file_put_contents('config/autoload/platforms/' . $fileName, $conf);
                    }

                    $deployDiscoveryService = $this->getServiceLocator()->get('MelisDbDeployDiscoveryService');

                    $ctr = 0;
                    set_time_limit(0);
                    ini_set('memory_limit', -1);

                    foreach ($modules as $module) {
                        $modulePath = $moduleSvc->getModulePath($module);

                        $dir = null;

                        if (file_exists($modulePath . '/install/dbdeploy')) {
                            $dir = scandir($modulePath . '/install/dbdeploy');
                        }


                        if ($modulePath && $dir) {
                            $deployDiscoveryService->processing($module);
                        } else {
                            unset($modules[$ctr]);
                        }

                        $ctr++;
                    }

                    $this->reprocessDbDeploy();
                }
            }
        }


        $view = new ViewModel();
        $view->setTerminal(true);
        $view->modules = $modules;

        return $view;
    }

    public function reprocessDbDeploy()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $service = new \MelisDbDeploy\Service\MelisDbDeployDeployService();

        if (false === $service->isInstalled()) {
            $service->install();
        }

        $service->applyDeltaPath(realpath('dbdeploy' . DIRECTORY_SEPARATOR . 'data/'));

        if ($service->changeLogCount() === $this->getTotalDataFile()) {
            return true;
        } else {
            return $this->reprocessDbDeploy();
        }

        return false;
    }

    private function getTotalDataFile()
    {
        $dbDeployPath = $_SERVER['DOCUMENT_ROOT'] . '/../dbdeploy/data/';

        if (!file_exists($dbDeployPath)) {
            return 0;
        }

        $files = glob($dbDeployPath . '*.sql');

        return count($files);

    }

    public function reprocessDbDeployAction()
    {

        header('Content-Type: application/json');
        set_time_limit(0);
        ini_set('memory_limit', -1);

        if ($this->reprocessDbDeploy()) {
            die(Json::encode(['success' => 1]));
        } else {
            die(Json::encode(['success' => 0, 'message' => 'Unable to process dbDeploy, please refresh the page and try again.']));
        }

    }

    /**
     * @return bool
     */
    public function isSiteIsInDefaultSelection()
    {
        $modules = $this->getNoneDemoSiteSelection();
        $modules[] = 'MelisCoreOnly';

        if (in_array($this->selectedSite(), $modules)) {
            return true;
        }

        return false;
    }

    public function checkSiteModuleAction()
    {
        $success = 1;
        $hasSite = null;
        $siteName = null;
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            $container = new Container('melisinstaller');
            $siteConfiguration = isset($container['site_module']) ? $container['site_module'] : null;
            if (in_array($siteConfiguration['site'], $this->getNoneDemoSiteSelection())) {
                $hasSite = true;
                $siteName = $siteConfiguration['site'];
            } else {
                if (isset($container['site']['website_module'])) {
                    $container['site']['website_module'] = $siteConfiguration['site'];
                }
            }
        }

        $response = [
            'success' => $success,
            'hasSite' => $hasSite,
            'siteName' => $siteName,
            'isMultiFramework' => $this->isMultiFramework(),
        ];

        return new JsonModel($response);

    }

    protected function getNoneDemoSiteSelection()
    {
        return ['None', 'NewSite'];
    }

    public function installSiteModuleAction()
    {
        $translator = $this->getServiceLocator()->get('translator');
        $success = 0;
        $message = $translator->translate('tr_melis_installer_no_site_install');
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {

            $container = new Container('melisinstaller');
            $site = $this->selectedSite();

            if (in_array($site, $this->getNoneDemoSiteSelection())) {

                $siteModule = $_SERVER['DOCUMENT_ROOT'] . '/../module/MelisSites/' . $site;
                if ($site == 'None') {
                    $message = 'Installed CMS with no site';
                } else {
                    $message = sprintf($translator->translate('melis_installer_site_installed'), $site);
                }

                $success = 1;
            }
        }

        $response = [
            'success' => $success,
            'message' => $message,
        ];

        return new JsonModel($response);

    }

    private function installDemoSite()
    {
        $container = new Container('melisinstaller');
        $moduleSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
        $siteConfiguration = isset($container['site_module']) ? $container['site_module'] : null;

        if ($this->isUsingDemoCms()) {

            set_time_limit(0);
            ini_set('memory_limit', -1);

            $siteModule = $siteConfiguration['website_module'];

            $installHelper = $this->getServiceLocator()->get('InstallerHelper');
            $melisSitePathModule = $_SERVER['DOCUMENT_ROOT'] . '/../module/MelisSites/' . '/' . $siteModule;

            if (file_exists($melisSitePathModule)) {
                // delete the first copy
                $installHelper->deleteDirectory($melisSitePathModule);
            }

            $installerPath = $moduleSvc->getModulePath('MelisInstaller') . '/etc/MelisDemoCms';

            // copy MelisDemoCms in MelisSites
            $installHelper->xcopy($installerPath, $melisSitePathModule, 0777);

            $this->mapDirectoryDemo($melisSitePathModule, 'MelisDemoCms', getenv('MELIS_MODULE'));
        }

    }

    protected function isUsingDemoCms()
    {
//        if (!in_array($this->getSelectedSiteOption(), $this->getNoneDemoSiteSelection())) {
//            return true;
//        }

        return false;
    }

    private function mapDirectoryDemo($dir, $targetModuleName, $newModuleName)
    {
        $installSvc = $this->getServiceLocator()->get('InstallerHelper');
        $result = [];

        $cdir = scandir($dir);

        $fileName = '';
        foreach ($cdir as $key => $value) {
            if (!in_array($value, [".", ".."])) {
                if (is_dir($dir . '/' . $value)) {

                    if ($value == $targetModuleName) {
                        rename($dir . '/' . $value, $dir . '/' . $newModuleName);
                        $value = $newModuleName;
                    } elseif ($value == $this->moduleNameToViewName($targetModuleName)) {
                        $newModuleNameSnakeCase = $this->moduleNameToViewName($newModuleName);

                        rename($dir . '/' . $value, $dir . '/' . $newModuleNameSnakeCase);
                        $value = $newModuleNameSnakeCase;
                    }

                    $result[$dir . '/' . $value] = $this->mapDirectoryDemo($dir . '/' . $value, $targetModuleName, $newModuleName);
                } else {

                    $newFileName = str_replace($targetModuleName, $newModuleName, $value);
                    if ($value != $newFileName) {
                        rename($dir . '/' . $value, $dir . '/' . $newFileName);
                        $value = $newFileName;
                    }

                    $result[$dir . '/' . $value] = $value;
                    $fileName = $dir . '/' . $value;
                    $installSvc->replaceFileTextContent($fileName, $fileName, $targetModuleName, $newModuleName);
                }
            }
        }

        return $result;
    }

    private function moduleNameToViewName($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }

    public function getModuleConfigurationFormsAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);

        $mm = $this->getServiceLocator()->get('ModuleManager');
        $modules = array_keys($mm->getLoadedModules());
        $modules = array_diff($modules, $this->getInstallerModules());
        $modulesExclusions = [
            'MelisEngine',
        ];
        $content = '';
        $tabs = '';
        $tabContent = '';

        $flag = 0;

        foreach ($modules as $module) {

            if (!in_array($module, $modulesExclusions)) {
                $moduleFormContent = $this->getModuleConfigurationForm($module);

                if ($moduleFormContent) {

                    $active = '';
                    $id = 'id' . $module;

                    if ($flag === 0) {
                        $active = 'active';
                    }

                    $tabs .= '<li class="' . $active . '"><a href="#' . $id . '" data-toggle="tab">' . $module . '</a></li>';

                    $tabContent .= '<div class="tab-pane ' . $active . '" id="' . $id . '">' . PHP_EOL;
                    $tabContent .= $moduleFormContent;
                    $tabContent .= '</div>' . PHP_EOL;

                    $flag++;
                }
            }
        }

        $translator = $this->getServiceLocator()->get('translator');

        $content .= '<div class="col-xs-12 col-md-4">';
        $content .= '    <ul class="nav nav-tabs nav-block">';
        $content .= $tabs;
        $content .= '    </ul>';
        $content .= '</div>';
        $content .= '    <div class="col-xs-12 col-md-6 col-md-push-1">';
        $content .= '<div class="melis-installer-module-configuration-form-content">        <div class="tab-content">';
        $content .= $tabContent;
        $content .= '        </div></div>';
        $content .= '    </div>';
        $content .= '<div class="setup-button-cont"><a class="btn btn-success setup-pass-page">' . $translator->translate('tr_melis_installer_common_next') . '</a></div>';

        die($content);

    }

    protected function getInstallerModules()
    {
        $modules = [
            'MelisAssetManager',
            'MelisDbDeploy',
            'MelisComposerDeploy',
            'MelisInstaller',
            'MelisModuleConfig',
        ];

        return $modules;
    }

    public function getModuleConfigurationForm($module)
    {
        $content = '';
        $controller = 'MelisSetupPostDownload';
        $action = 'getForm';

        $namespace = $module . '\\Controller\\' . $controller . 'Controller';

        try {
            if (class_exists($namespace) && method_exists($namespace, $action . 'Action')) {

                $viewModel = $this->forward()->dispatch($module . '\\Controller\\' . $controller, ['action' => $action]);

                $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
                $html = new \Zend\Mime\Part($renderer->render($viewModel));

                $content = (string) $html->getContent();

            }

        } catch (\Exception $e) {
            $content = $e->getMessage();
        }


        return $content;
    }

    public function validateModuleConfigurationFormAction()
    {
        $mm = $this->getServiceLocator()->get('ModuleManager');
        $modules = array_keys($mm->getLoadedModules());

        $params = $this->params()->fromQuery();

        $errors = [];
        $success = true;

        $container = new Container('melis_modules_configuration_status');

        $container->formData = $params;

        // validate form firm before calling submitModuleConfigurationForm

        foreach ($modules as $module) {

            $container->$module = true;
            $result = $this->validateModuleConfigurationForm($module, $params);

            if (is_array($result)) {

                if ($result['errors']) {
                    $errors[] = [
                        'errors' => $result['errors'],
                        'message' => $result['message'],
                        'name' => $module,
                        'success' => (bool) $result['success'],
                    ];
                    $success = false;
                    $container->$module = (bool) $success;
                } else {
                    $container->$module = true;
                }
            }
        }

        $data = [
            'success' => $success,
            'errors' => $errors,
        ];


        header('Content-Type: application/json');
        die(Json::encode($data));
    }

    public function validateModuleConfigurationForm($module, $params)
    {
        $controller = 'MelisSetupPostDownload';
        $action = 'validateForm';

        $namespace = $module . '\\Controller\\' . $controller . 'Controller';

        if (class_exists($namespace) && method_exists($namespace, $action . 'Action')) {

            $class = $module . '\\Controller\\' . $controller;
            $result = $this->forward()->dispatch($class, array_merge(['action' => $action, 'post' => $params]));

            if ($result instanceof JsonModel) {
                return $result->getVariables();
            }
        } else {
            return null;
        }
    }

    public function submitModuleConfigurationFormAction()
    {

        $mm = $this->getServiceLocator()->get('ModuleManager');
        $modules = array_keys($mm->getLoadedModules());

        $params = $this->params()->fromQuery();

        $errors = [];
        $success = true;

        $container = new Container('melis_modules_configuration_status');

        // validate form firm before calling submitModuleConfigurationForm

        foreach ($modules as $module) {

            $container->$module = true;
            $result = $this->submitModuleConfigurationForm($module, $params);

            if (is_array($result)) {

                if ($result['errors']) {
                    $errors[] = [
                        'errors' => $result['errors'],
                        'message' => $result['message'],
                        'name' => $module,
                        'success' => (bool) $result['success'],
                    ];
                    $success = false;
                    $container->$module = (bool) $success;
                } else {
                    $container->$module = true;
                }
            }
        }

        if ($success) {
            // Install the site
            if (!$this->isSiteIsInDefaultSelection()) {
                set_time_limit(0);
                ini_set('memory_limit', '-1');
                $requests = $this->getRequest()->getQuery()->toArray();
                $parameters = new \Zend\Stdlib\Parameters(array_merge($requests, ['module' => $this->selectedSite(), 'action' => $this->marketplace()::ACTION_DOWNLOAD]));
                $this->getRequest()->setPost($parameters);
                $this->marketplaceSite()->installSite($this->getRequest())->invokeSetup();
            }
        }

        $data = [
            'success' => $success,
            'errors' => $errors,
        ];


        header('Content-Type: application/json');
        die(Json::encode($data));

    }

    public function submitModuleConfigurationForm($module, $params)
    {

        $controller = 'MelisSetupPostDownload';
        $action = 'submit';

        $namespace = $module . '\\Controller\\' . $controller . 'Controller';

        if (class_exists($namespace) && method_exists($namespace, $action . 'Action')) {

            $class = $module . '\\Controller\\' . $controller;
            $result = $this->forward()->dispatch($class, array_merge(['action' => $action, 'post' => $params]));

            if ($result instanceof JsonModel) {
                return $result->getVariables();
            }
        } else {
            return null;
        }

    }

    function createNewUserAction()
    {
        $success = 0;
        $errors = [];

        if ($this->getRequest()->isPost()) {

            $createUserForm = $this->getForm('melis_installer/forms/melis_installer_user_data');
            $postValues = get_object_vars($this->getRequest()->getPost());
            $createUserForm->setData($postValues);

            if ($createUserForm->isValid()) {
                $container = new Container('melisinstaller');
                $password = md5($postValues['password']);
                $container['user_data'] = $postValues;
                $success = 1;
            } else {
                $errors = $createUserForm->getMessages();
            }

            $melisMelisInstallerConfig = $this->getServiceLocator()->get('MelisInstallerConfig');
            $appConfigForm = $melisMelisInstallerConfig->getItem('melis_installer/forms/melis_installer_user_data');
            $appConfigForm = $appConfigForm['elements'];

            foreach ($errors as $keyError => $valueError) {
                foreach ($appConfigForm as $keyForm => $valueForm) {
                    if ($valueForm['spec']['name'] == $keyError &&
                        !empty($valueForm['spec']['options']['label'])) {
                        $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                    }
                }
            }

        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    public function finalizeSetupAction()
    {
        $success = 0;
        $errors = [];
        $container = new Container('melisinstaller');
        $logs = [];

        if ($this->getRequest()->isXmlHttpRequest()) {

            // re-write the module that is being loaded
            $docPath = $_SERVER['DOCUMENT_ROOT'] . '/../';
            $moduleLoadFile = $docPath . 'config/melis.module.load.php';

            if (file_exists($moduleLoadFile)) {
                $logs[] = 'Module Load file exists';
                $siteModule = getenv('MELIS_MODULE');
                $content = file_get_contents($moduleLoadFile);
                $content = str_replace(["'MelisInstaller',\n"], '', $content);
                $logs[] = $content;
                $logs[] = 'Removed MelisInstaller in Module Load';

                $site = getenv('MELIS_MODULE');

                if ($site && !in_array($site, ['NewSite', 'None'])) {
                    $content = str_replace(["'$site',\n",], '', $content);
                }

                $content = str_replace(["\t", '    '], '  ', $content);

                file_put_contents($moduleLoadFile, $content);
                $logs[] = 'Module Load Rebuild';

            }

            unlink($docPath . 'config/melis.modules.path.php');

            $this->getEventManager()->trigger('melis_installer_last_process_start', $this, $container->getArrayCopy());

            // replace the application.config
            $moduleSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
            $melisInstallPath = $moduleSvc->getModulePath('MelisInstaller');
            $appLoader = $melisInstallPath . '/etc/application.config.php';

            if (file_exists($appLoader)) {
                unlink($docPath . '/config/application.config.php');
                copy($appLoader, $docPath . '/config/application.config.php');
                $logs[] = 'Replaced application.config.php';
            }

            $success = 1;
            $this->marketplace()->unplugModule('MelisInstaller');
            $this->unplugSite();

            file_put_contents($docPath . 'config/melis.install', '1');
            if (isset($_SESSION['melis_php_warnings'])) {
                unset($_SESSION['melis_php_warnings']);
            }

            // clear melis installer session
//            $container->getManager()->destroy();
        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
            'logs' => $logs,
        ]);
    }

    public function getDatabaseInstallStatusAction()
    {

        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        $tables = $installHelper->getImportedTables();
        $status = [];

        $container = new Container('melisinstaller');
        $container['db_install_tables'] = $tables;
        foreach ($tables as $table) {
            if ($installHelper->isDbTableExists($table)) {
                $status['installed'][] = $table;
            } else {
                $status['failed'][] = $table;
            }
        }

        return ['status' => $status];
    }

    /**
     * Execute this when setup has errors or setup has failed
     */
    public function rollBackAction()
    {

        $success = 0;
        $errors = [];
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        $container = new Container('melisinstaller');

        if (!empty($container->getArrayCopy()) && in_array(array_keys($container['steps']), [$this->steps])) {
            $tablesInstalled = isset($container['db_install_tables']) ? $container['db_install_tables'] : [];
            $siteModule = 'module/MelisSites/' . $container['cms_data']['website_module'] . '/';
            $dbConfigFile = 'config/autoload/platforms/' . $installHelper->getMelisPlatform() . '.php';
            $config = include($dbConfigFile);
            // drop table
            $installHelper->setDbAdapter($config['db']);

            foreach ($tablesInstalled as $table) {

                if ($installHelper->isDbTableExists($table)) {
                    $installHelper->executeRawQuery("DROP TABLE " . trim($table));
                }
            }

            // delete site module
            if (file_exists($siteModule)) {
                unlink($siteModule);
            }

            //delete db config file
            if (file_exists($dbConfigFile)) {
                unlink($dbConfigFile);
            }

            // clear session
            $container->getManager()->destroy();

            $success = 1;
        }

        return new JsonModel([
            'success' => $success,
        ]);
    }

    /**
     * Retrieve's the current set values for user credentials to array
     * @return Array
     */
    public function loadUserCrendetialFromSession()
    {
        $data = [];

        $container = new Container('melisinstaller');
        if (isset($container['user_data'])) {
            $data = $container['user_data'];
        }

        return $data;
    }

    public function checkConfigAction()
    {
        $success = 0;
        $errors = [];

        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->systemConfigurationChecker();
            $success = $response['success'];
            $errors = $response['errors'];

        }

        return new JsonModel([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    /**
     * Checks if the current project has MelisCms Module
     * @return Array
     */
    protected function hasMelisCmsModule()
    {
        $modulesSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
        $modules = $modulesSvc->getAllModules();
        $path = $modulesSvc->getModulePath('MelisCms');
        $isExists = 0;

        if (file_exists($path)) {
            $isExists = 1;
        }

        return $isExists;
    }

    private function mapDirectory($dir, $moduleName)
    {
        $installSvc = $this->getServiceLocator()->get('InstallerHelper');
        $result = [];

        $cdir = scandir($dir);
        $fileName = '';
        foreach ($cdir as $key => $value) {
            if (!in_array($value, [".", ".."])) {
                if (is_dir($dir . '/' . $value)) {
                    $result[$dir . '/' . $value] = $this->mapDirectory($dir . '/' . $value, $moduleName);
                } else {
                    $result[$dir . '/' . $value] = $value;
                    $fileName = $dir . '/' . $value;
                    $installSvc->replaceFileTextContent($fileName, $fileName, '[:ModuleName]', $moduleName);
                }
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    protected function selectedSite()
    {
        $container = new Container('melisinstaller');
        $siteModule = $container['site_module']['site'] ?? null;

        return $siteModule;
    }

    /**
     * @return bool
     */
    protected function unplugSite()
    {
        if ($site = $this->selectedSite()) {
            return $this->marketplace()->unplugModule($site);
        }

        return false;
    }

    /**
     * @return \MelisMarketPlace\Service\MelisMarketPlaceService
     */
    protected function marketplace()
    {
        /** @var \MelisMarketPlace\Service\MelisMarketPlaceService $marketplace */
        $marketplace = $this->getServiceLocator()->get('MelisMarketPlaceService');

        return $marketplace;
    }

    /**
     * @return \MelisMarketPlace\Service\MelisMarketPlaceSiteService
     */
    protected function marketplaceSite()
    {
        /** @var \MelisMarketPlace\Service\MelisMarketPlaceSiteService $marketplaceSite */
        $marketplaceSite = $this->getServiceLocator()->get('MelisMarketPlaceSiteService');

        return $marketplaceSite;
    }


    public function checkSessionAction()
    {
        $container = new Container('melisinstaller');
        //unset($container->platforms);
        print '<pre>';
        print_r($container->getArrayCopy());
        print '</pre>';
        die;
    }


    public function clearSessionAction()
    {
        $container = new Container('melisinstaller');
        $container->getManager()->destroy();

        dd('Session cleared!');
    }

    public function testAction()
    {
        dd($this->isSiteIsInDefaultSelection());
        dd('done');
    }

    /**
     * Parses the modules list.
     * @param $modules
     */
    public function parseModulesList(&$modules) {
        if (!empty($modules['packages'])) {
            foreach ($modules['packages'] as $key => $module) {
                $this->removeInactiveModules($modules, $module, $key);
                $this->removePrivateModule($modules, $module, $key);
            }
        }
    }

    /**
     * Removes inactive modules in the "Modules to install" list
     * @param $modules
     * @param $module
     * @param $key
     */
    public function removeInactiveModules(&$modules, $module, $key)
    {
        if (isset($module['packageIsActive'])) {
            if ($module['packageIsActive'] <= 0) {
                if (! in_array($module['packageModuleName'], $this->includedModules))
                    unset($modules['packages'][$key]);
            }
        }
    }

    /**
     * Removes private modules in the "Modules to install" list
     * @param $modules
     * @param $module
     * @param $key
     */
    public function removePrivateModule(&$modules, $module, $key)
    {
        if (isset($module['packageIsPrivate'])) {
            if ($module['packageIsPrivate'] == 1)
                unset($modules['packages'][$key]);
        }
    }
}
