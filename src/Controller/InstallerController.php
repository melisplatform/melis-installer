<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)b
 *
 */

namespace MelisInstaller\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;
use Zend\Json\Json;
class InstallerController extends AbstractActionController
{
    
    protected $steps = array('sysconfig', 'vhost', 'fsrights', 'environments', 'dbconn', 'selmod', 'pf_init' );
    
    public function indexAction() 
    {
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        
        $melisConfig = $this->serviceLocator->get('MelisInstallerConfig');
        $resources = $melisConfig->getItem('melis_installer/ressources');

        $locales      = $this->getTranslationsLocale();

        
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent();
        
        $route = $routeMatch->getRouteMatch()->getMatchedRouteName();

        if($route == 'melis-backoffice/setup' || $route == 'melis-backoffice/application-MelisInstaller') {
            $this->layout()->isMelisInstallerRoute = true;
            $this->layout()->installerJsFiles  = $resources['js'];
            $this->layout()->installerCssFiles = $resources['css'];
        }
        
        $container = new Container('melisinstaller');
        
        // Website configuration
        $webConfigOption     = $this->getForm('melis_installer/forms/melis_installer_webconfig_option');
        $webLangForm         = $this->getForm('melis_installer/forms/melis_installer_web_lang');
        $webForm             = $this->getForm('melis_installer/forms/melis_installer_webform');
        
        $showWebForm = false;
        // WebConfigOption preload values from Session/Container
        if (isset($container['cms_data']['weboption'])) {
            
            // Getting the default values for Website confguration options from app.interface.php datas
            $config = $this->getServiceLocator()->get('config');
            $defaultWebConfigOptions = $config['plugins']['melis_installer']['datas']['default_website_config_options'];
            if (array_key_exists($container['cms_data']['weboption'], $defaultWebConfigOptions) && $container['cms_data']['weboption'] != 'None'){
                $showWebForm = true;
            }
            
            $webConfigOption->get('weboption')->setValue($container['cms_data']['weboption']);
        }
        
        // WebLangForm preload values from Session/Container
        if(isset($container['cms_data']['web_lang'])) {
            $webLangForm->get('language')->setValue($container['cms_data']['web_lang']);
        }
        
        // WebForm preload from Session/Container
        if (isset($container['cms_data']['web_form'])) {
            foreach($container['cms_data']['web_form'] as $key => $val) {
                $webForm->get($key)->setValue($val);
            }
        }
        
        // Melis Configuration
        $createUserForm = $this->getForm('melis_installer/forms/melis_installer_user_data');

        /*
         * create session for steps, 
         * this makes sure that we reset the steps status if the page/browser has been refreshed or closed
         */
        $container->steps = array();
             
        // pre-load user data if set from the session
        if(isset($container['user_data'])) {
            foreach($container['user_data'] as $key => $userData) {
                $createUserForm->get($key)->setValue($userData);
            }
        }
        
        $selectedModules = array();
        if(!empty($container['install_modules'])){
            $selectedModules = $container['install_modules'];
        }
        
        $currentLocale = isset($container['setup-language']) ? $container['setup-language'] : 'en_EN';
        
        $requiredModules = array();
        if (!empty($container['cms_data']['required_modules'])){
            $requiredModules = $container['cms_data']['required_modules'];
        }

        $currentSite = isset($container['site_module']['site']) ? $container['site_module']['site'] : 'NewSite';
        $webConfigOption->get('weboption')->setValue($currentSite);
            
        $view = new ViewModel();
        // pre-loaded stuffs 
        $view->currentLocale            = $currentLocale;
        $view->setupLocales             = $locales;
        $view->setup1_0                 = $this->systemConfigurationChecker();
        $view->setup1_0_phpversion      = phpversion();
        $view->setup1_1                 = $this->vHostSetupChecker();
        $view->setup1_2                 = $this->checkDirectoryRights();
        $view->setup1_3                 = $this->getEnvironments();
        $view->setup1_3_current         = $this->getCurrentPlatform();
        $view->setup1_3_env_name        = $installHelper->getMelisPlatform();
        $view->setup1_3_env_domain      = $this->getRequest()->getServer()->SERVER_NAME;
        $view->setup2                   = $this->loadDatabaseCredentialFromSession();

        $view->setup3_webConfigOption   = $webConfigOption;
        $view->setup3_showWebForm       = $showWebForm;
        $view->setup3_webLangForm       = $webLangForm;
        $view->setup3_webForm           = $webForm;
        $view->setup3_createUserForm    = $createUserForm;



        $view->setup3_3_selected        = $selectedModules;
        $view->setup3_3_requiredModules = $requiredModules;

        $view->packagistMelisModules   = $installHelper->getPackagistMelisModules();
        
        return $view;
    }

    public function getTranslationsLocale()
    {
        $modulesSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
        $modules = $modulesSvc->getAllModules();
        $modulePath = $modulesSvc->getModulePath('MelisInstaller');
        $path = $modulePath.'/language/';
        $dir  = scandir($path);
        $files = array();
        foreach($dir as $file) {
            if(is_file($path.$file)) {
                $files[] = $file;
            }
        }
        $locales = array();
        foreach($files as $file) {
            $locale = explode('.',$file);
            $locales[] = $locale[0];
        }
        // re-add locales to get the unique locales and fix proper array indexing
        $uniqueLocales = array_unique($locales);
        $newUniqueLocales = array();
        foreach($uniqueLocales as $locale) {
            $newUniqueLocales[] = $locale;
        }
        return $newUniqueLocales;
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
        
        if($this->getRequest()->isPost()) {
            $locale = $this->getRequest()->getPost('langLocale');
            $container = new Container('melisinstaller');
            $container['setup-language'] = $locale;
            $success = 1;
        }
        
        return new JsonModel(array('success' => $success));
    }
    
    /**
     * This function is used for rechecking the status the desired step
     * @return \Zend\View\Model\JsonModel
     */
    public function checkSysConfigAction()
    {
        $success = 0;
        $errors  = array();
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->systemConfigurationChecker();
            $success  = $response['success'];
            $errors   = $response['errors'];
            
            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[0]] = array('page' => 1, 'success' => $success);
        }
        
        return new JsonModel(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }
    
    /**
     * This step rechecks the Step 1.1 which is the Vhost Setup just to check
     * that everything fine.
     * @return \Zend\View\Model\JsonModel
     */
    public function checkVhostSetupAction() 
    {
        $success = 0;
        $errors  = array();
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->vHostSetupChecker();
            $success  = $response['success'];
            $errors   = $response['errors'];
            
            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[1]] = array('page' => 2, 'success' => $success);
        }
        
        return new JsonModel(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }
    
    /**
     * Rechecks Step 1.2 File System Rights
     * @return \Zend\View\Model\JsonModel
     */
    public function checkFileSystemRightsAction()
    {
        $success = 0;
        $errors  = array();
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->checkDirectoryRights();
            $success  = $response['success'];
            $errors   = $response['errors'];
            
            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[2]] = array('page' => 3, 'success' => $success);
        }
        
        return new JsonModel(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }
    
    public function newEnvironmentAction()
    {
        $success  = 0;
        $errors   = array();
        $request = array();
        // add listeners here for MelisCms and MelisCore to listen
        if($this->getRequest()->isPost()) {
            $data = get_object_vars($this->getRequest()->getPost());
            $currentPlatformDomain = $data['domain'];
            $domainEnv             = array();

            // remove domain key
            unset($data['domain']); 
            for($x = 0; $x <= count($data)/2; $x++) {
                $environmentName = 'environment_name_'.($x+1);
                $domainName      = 'domain_'.($x+1);
                $sendEmail       = 'send_email_'.($x+1);
                $errorReporting  = 'error_reporting_' . ($x+1);
                if(isset($data[$environmentName]) && isset($data[$domainName]) &&
                    $data[$environmentName] != '' && $data[$domainName] != '') {
                    $domainEnv[] = array(
                        'environment' => $data[$environmentName],
                        'domain' => $data[$domainName],
                        'send_email' => isset($data[$sendEmail]) ? $data[$sendEmail] : 'off',
                        'error_reporting' => isset($data[$errorReporting]) ? $data[$errorReporting] : 0,
                    );
                }

            }

            $request = array(
              'currentPlatform' => [
                  'platform_domain' => $currentPlatformDomain,
                  'send_email' =>  isset($data['send_email']) && $data['send_email'] == 'on' ? 1 : 0,
                  'error_reporting' => $data['error_reporting'],
              ],
              'siteDomain' => $domainEnv
                
            );
            // add the new values
            $this->getEventManager()->trigger('melis_install_new_platform_start', $this, $request);
            
            $success = 1;
            
            // add status to session
            $container = new Container('melisinstaller');
            $container['steps'][$this->steps[3]] = array('page' => 4, 'success' => $success);;

        }
        
        return new JsonModel(array(
           'success' => $success,
            'errors' => $errors,
        ));
        // also here, add listeners
    }
    
    public function deleteEnvironmentAction()
    {
        $success = 0;
        if($this->getRequest()->isPost()) {
            $data = get_object_vars($this->getRequest()->getPost());
            $response = $this->getEventManager()->trigger('melis_install_delete_environment_start', $this, $data);
            
            if(!empty($response)) {
                $success  = $response[0]['success'];
            }
            
            
        }
        
        return new JsonModel(array(
            'success' => $success,
        ));
    }
    
    public function testDatabaseConnectionAction()
    {
        $success = 0;
        $errors  = array();
        $translator = $this->getServiceLocator()->get('translator');
        if($this->getRequest()->isPost()) {
            $data = get_object_vars($this->getRequest()->getPost());
            $installHelper = $this->getServiceLocator()->get('InstallerHelper'); 
            
            
            if(!empty($data['hostname'])) {
                if(!empty($data['database'])) {
                    if(!empty($data['username'])) {
                        $response = $installHelper->checkMysqlConnection($data['hostname'], $data['database'], $data['username'], $data['password']);
                        
                        if($response['isConnected']) {
                            if($response['isMysqlPasswordCorrect']) {
                                if($response['isDatabaseExists']) {
                                    if ($response['isDatabaseCollationNameValid']) {
                                        $success = 1;
                                        // add status to session
                                        $container = new Container('melisinstaller');
                                        $container['steps'][$this->steps[4]] = array('page' => 5, 'success' => $success);
                                        
                                        $container = new Container('melisinstaller');
                                        $container['database'] = $data;

                                        $_SESSION['database'] = $data;

                                    }
                                    else {
                                        $errors = array(
                                            'Collation' => array('invalidCollation' => $translator->translate('tr_melis_installer_layout_dbcon_collation_name_invalid'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_collation_name'))
                                        );
                                    }
                                }
                                else {
                                    $errors = array(
                                        'database' => array('invalidDatabase' => $translator->translate('tr_melis_installer_dbcon_form_db_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_db')),
                                        'username' => array('invalidUsername' => $translator->translate('tr_melis_installer_dbcon_form_user_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_user')),
                                        'password' => array('invalidPassword' => $translator->translate('tr_melis_installer_dbcon_form_pass_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_pass'))
                                    );
                                }
                            }
                            else {
                                $errors = array(
                                    'password' => array('invalidPassword' => $translator->translate('tr_melis_installer_dbcon_form_pass_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_pass'))
                                );
                            }
                        }
                        else {
                            $errors = array('Host' => array('unreachableHost' => $translator->translate('tr_melis_installer_dbcon_form_host_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_host')));
                        }
                    }
                    else {
                        $errors = array('username' => array('emptyUsername' => $translator->translate('tr_melis_installer_dbcon_form_user_empty'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_user')));
                    }

                }
                else {
                    $errors = array('database' => array('emptyDatabase' => $translator->translate('tr_melis_installer_dbcon_form_db_empty'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_db')));
                }

            }
            else {
                $errors = array('Host' => array('unreachableHost' => $translator->translate('tr_melis_installer_dbcon_form_host_fail'), 'label' => $translator->translate('tr_melis_installer_layout_dbcon_form_host')));
            }

        }
        
        return new JsonModel(array(
           'success' => $success,
           'errors'  => $errors
        ));
    }
    
    public function addInstallableModulesAction()
    {
       $container = new Container('melisinstaller');
       $container['steps'][$this->steps[6]] = array('page' => 6, 'success' => 1);
       $container['install_modules'] = array();
       if($this->getRequest()->isXmlHttpRequest()) {
            $data = get_object_vars($this->getRequest()->getPost());
            
            // remove pre-set data
            unset($data['_default']);
            
            if(!empty($data)) {
                $container['install_modules'] = array_values($data);
            }
       }
       
       return new JsonModel(array('success' => 1));
    }
    
    public function setWebConfigAction()
    {
        $success = 0;
        $errors  = array();
        $appConfigForm = array();
        $requiredModules = array();
        
        $translator = $this->getServiceLocator()->get('translator');
        $melisMelisInstallerConfig = $this->getServiceLocator()->get('MelisInstallerConfig');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
    
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $postValues = $request->getPost();
            // Website configuration option
            $webConfigOption = $this->getForm('melis_installer/forms/melis_installer_webconfig_option');
            $webConfigOption->setData($postValues);
    
            if ($webConfigOption->isValid())
            {
                $data = $webConfigOption->getData();
                $container = new Container('melisinstaller');
                $container['cms_data'] = $data;
                
                // Getting the default values for Website confguration options from app.interface.php datas
                $config = $this->getServiceLocator()->get('config');
                $defaultWebConfigOptions = $config['plugins']['melis_installer']['datas']['default_website_config_options'];
                // Checking if the Website option
                if (array_key_exists($container['cms_data']['weboption'], $defaultWebConfigOptions) && $container['cms_data']['weboption'] != 'None'){
                    
                    $webForm = $this->getForm('melis_installer/forms/melis_installer_webform');
                    $webForm->setData($postValues);
                    if ($webForm->isValid())
                    {
                        
                        $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
                        if(!file_exists($melisSite)) 
                        {
                            mkdir($melisSite, 0777);
                            $installHelper->filePermission($melisSite);
                        }
                        
                        // checking if the target module name is existing on the target dir
                        if(!file_exists($melisSite.'/'.$postValues['website_module'])) 
                        {
                            $data = $webForm->getData();
                            $container['cms_data']['web_form'] = $data;
                            $container['cms_data']['web_lang'] = $postValues['language'];
                            $success = 1;
                        }
                        else 
                        {
                            array_push($errors, array(
                                "hasError" => sprintf($translator->translate("tr_melis_installer_web_form_module_exists"), $postValues['website_module']),
                                "label" => $translator->translate("tr_melis_installer_web_form_module_label")
                            ));
                        }
                    }
                    else
                    {
                        $errors = $webForm->getMessages();
                        $appConfigForm = $melisMelisInstallerConfig->getItem('melis_installer/forms/melis_installer_webform');
                        $appConfigForm = $appConfigForm['elements'];
                    }
                }
                elseif ($container['cms_data']['weboption'] != 'None')
                {
                    if (!empty(getenv('MELIS_MODULE')))
                    {
                        if(!preg_match('/[^a-z_\-0-9]/i', getenv('MELIS_MODULE')))
                        {
                        
                            // Website Configuration chooses a Demo Site
                            $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
                        
                            if(!file_exists($melisSite))
                            {
                                mkdir($melisSite, 0777);
                                $installHelper->filePermission($melisSite);
                            }
                        
                            // checking if the target module name is existing on the target dir
                            //if(file_exists($melisSite.'/'.$container['cms_data']['weboption']))
                            if(file_exists($melisSite.'/'.getenv('MELIS_MODULE')))
                            {
                                array_push($errors, array(
                                    "hasError" => sprintf($translator->translate("tr_melis_installer_web_form_module_exists"), $container['cms_data']['weboption']),
                                    "label" => $translator->translate("tr_melis_installer_web_form_module_label")
                                ));
                            }
                        
                            /**
                             * Required modules needed to install Demo Site from config
                             */
                            $demoDir = $this->getModuleSvc()->getModulePath('MelisInstaller').'/etc/'.$container['cms_data']['weboption'];
                            $siteConfig = require $demoDir.'/config/'.$container['cms_data']['weboption'].'.config.php';
                        
                            $requiredModules = $siteConfig['site'][$container['cms_data']['weboption']]['datas']['required_modules'];
                            $container['cms_data']['required_modules'] = $requiredModules;
                        
                            if (empty($errors))
                            {
                                $success = 1;
                            }
                        }
                        else
                        {
                            array_push($errors, array(
                                "label" => $translator->translate("tr_melis_installer_web_form_module_label"),
                                "hasError" => $translator->translate("tr_melis_installer_web_config_invalid_vhost_module_name")
                            ));
                        }
                    }
                    else 
                    {
                        array_push($errors, array(
                            "label" => $translator->translate("tr_melis_installer_web_form_module_label"),
                            "hasError" => $translator->translate("tr_melis_installer_web_config_empty_vhost_module_name")
                        ));
                    }
                }
                elseif ($container['cms_data']['weboption'] == 'None')
                {
                    $success = 1;
                }
            }
            else
            {
                $errors = $webConfigOption->getMessages();
                $appConfigForm = $melisMelisInstallerConfig->getItem('melis_installer/forms/melis_installer_webconfig_option');
                $appConfigForm = $appConfigForm['elements'];
            }
            
            if (!empty($errors))
            {
                foreach ($errors as $keyError => $valueError)
                {
                    foreach ($appConfigForm as $keyForm => $valueForm)
                    {
                        if ($valueForm['spec']['name'] == $keyError && !empty($valueForm['spec']['options']['label']))
                        {
                            $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                        }
                    }
                }
            }
        }
        
        if ($success)
        {
            $container['steps'][$this->steps[5]] = array('page' => 7, 'success' => $success);
        }
        
        return new JsonModel(array(
            'success' => $success,
            'errors'  => $errors,
            'requiredModules' => $requiredModules
        ));
    }

    public function setDownloadableModulesAction()
    {

        $request  = $this->getRequest();
        $packages = [];


        if($request->isPost()) {

            $container = new Container('melisinstaller');

            $post = $request->getPost()->toArray();

            $packages = isset($post['packages']) ? $post['packages'] : null;
            $modules  = isset($post['modules'])  ? $post['modules']  : null;
            $siteLang = isset($post['siteLang']) ? $post['siteLang'] : [];
            $siteData = isset($post['siteData']) ? $post['siteData'] : [];

            if($siteLang)
                parse_str($siteLang, $siteLang);

            if($siteData)
                parse_str($siteData, $siteData);

            if($packages && $modules) {

                $downloadModules               = array();
                $container['install_modules']  = $modules;

                $ctr = 0;
                foreach($modules as $module) {
                    $downloadModules[$module] = $packages[$ctr];
                    $ctr++;
                }

                $container['download_modules'] = $downloadModules;

            }

            $container['site_module']  = array_merge(array('site' => $post['site']), $siteLang, $siteData);



        }

        return new JsonModel(['success' => 1, 'packages' => $packages]);

    }

    public function downloadModulesAction()
    {

        $request = $this->getRequest();

        if($request->isXmlHttpRequest()) {

            $config = $this->getServiceLocator()->get('MelisInstallerConfig');

            $autoInstallModules  = $config->getItem('melis_installer/datas/module_auto_install');
            $container           = new Container('melisinstaller');
            $downloadableModules = isset($container['download_modules']) ? $container['download_modules'] : [];

            $downloadableModules = array_merge($autoInstallModules, $downloadableModules);

            $downloadableModules = implode(' ', $downloadableModules);

            $composerSvc = $this->getServiceLocator()->get('MelisComposerService');

            set_time_limit(0);
            ini_set('memory_limit', -1);

            $composerSvc->download($downloadableModules, null, true);

        }

        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;

    }

    private function installDemoSite()
    {
        $container         = new Container('melisinstaller');
        $moduleSvc         = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
        $siteConfiguration = isset($container['site_module']) ? $container['site_module'] : null;

        if(!in_array($siteConfiguration['site'], array('NewSite', 'None'))) {

            echo '<i class="fa fa-plus"></i> Adding ' . $siteConfiguration['site'] .'<br/>';

            $siteModule = $siteConfiguration['website_module'];

            $installHelper       = $this->getServiceLocator()->get('InstallerHelper');
            $melisSitePathModule = $_SERVER['DOCUMENT_ROOT'] . '/../module/MelisSites/'.'/'.$siteModule;

            if(file_exists($melisSitePathModule)) {
                // delete the first copy
                $installHelper->deleteDirectory($melisSitePathModule);
            }

            $installerPath = $moduleSvc->getModulePath('MelisInstaller').'/etc/MelisDemoCms';

            // copy MelisDemoCms in MelisSites
            $installHelper->xcopy($installerPath, $melisSitePathModule, 0777);
        }

    }

    public function activateModulesAction()
    {
        $request = $this->getRequest();
        $modules = array();

        if($request->isXmlHttpRequest()) {

            $config = $this->getServiceLocator()->get('MelisInstallerConfig');

            $autoInstallModules  = array_keys($config->getItem('melis_installer/datas/module_auto_install'));
            $defaultModules      = $config->getItem('melis_installer/datas/module_default');
            $container           = new Container('melisinstaller');
            $downloadableModules = isset($container['download_modules']) ? array_keys($container['download_modules']) : [];
            $moduleSvc           = $this->getServiceLocator()->get('MelisInstallerModulesService');

            // check if the module exists before activating
            $modules = array_merge($autoInstallModules, $downloadableModules);
            $ctr = 0;
            foreach($modules as $module) {
                $modulePath = $moduleSvc->getModulePath($module);
                if(!$modulePath)
                    unset($modules[$ctr]);

                $ctr++;
            }

            // load site module in installer
            $siteConfiguration = isset($container['site_module']) ? $container['site_module'] : null;
            if(!in_array($siteConfiguration['site'], array('NewSite', 'None'))) {
                array_push($downloadableModules, $siteConfiguration['site']);
            }

            $modules = array_merge($autoInstallModules, $downloadableModules);
            $moduleSvc->createModuleLoader('config/', array_merge($modules, array('MelisInstaller')), $defaultModules);

        }

        $view          = new ViewModel();
        $view->setTerminal(true);
        $view->modules = $modules;

        return $view;
    }

    public function execDbDeployAction()
    {

        $request = $this->getRequest();
        $modules = array();
        if($request->isXmlHttpRequest()) {

            $installHelper       = $this->getServiceLocator()->get('InstallerHelper');
            $config              = $this->getServiceLocator()->get('MelisInstallerConfig');
            $autoInstallModules  = array_keys($config->getItem('melis_installer/datas/module_auto_install'));
            $container           = new Container('melisinstaller');
            $downloadableModules = isset($container['download_modules']) ? array_keys($container['download_modules']) : [];
            $modules             = array_merge($autoInstallModules, $downloadableModules);
            $moduleSvc           = $this->getServiceLocator()->get('MelisInstallerModulesService');

            if($modules && is_array($modules)) {

                $database = isset($container['database']) ? $container['database'] : null;
                if($database) {
                    $deployDiscoveryService = $this->getServiceLocator()->get('MelisDbDeployDiscoveryService');

                    $ctr = 0;
                    foreach($modules as $module) {
                        $modulePath = $moduleSvc->getModulePath($module);

                        $dir = null;

                        if(file_exists($modulePath.'/install/dbdeploy'))
                            $dir = scandir($modulePath.'/install/dbdeploy');


                        if($modulePath && $dir)
                            $deployDiscoveryService->processing($module, $database);

                        else
                            unset($modules[$ctr]);

                        $ctr++;
                    }
                }
            }

            // -> Create Database data SQL file configured from what was filled in forms
            $fileName = $installHelper->getMelisPlatform().'.php';
            $configValue = array(
                'db' => array(
                    'dsn'      => sprintf('mysql:dbname=%s;host=%s',$database['database'],$database['hostname']),
                    'username' => $database['username'],
                    'password' => $database['password'],
                ),
            );

            $config = new Config($configValue, true);
            $writer = new PhpArray();
            $conf = $writer->toString($config);
            if(is_writable('config/autoload/platforms/'))
                file_put_contents('config/autoload/platforms/'.$fileName, $conf);


            $this->installDemoSite();
        }

        $view          = new ViewModel();
        $view->setTerminal(true);
        $view->modules = $modules;

        return $view;
    }

    public function getModuleConfigurationFormsAction()
    {

        $mm      = $this->getServiceLocator()->get('ModuleManager');
        $config  = $this->getServiceLocator()->get('MelisInstallerConfig');
        $modules = array_keys($mm->getLoadedModules());

        $defaultModules  = $config->getItem('melis_installer/datas/module_default');
        $modules         = array_diff($modules, array_merge($defaultModules, array('MelisInstaller', 'MelisModuleConfig')));

        $content         = '';
        $tabs            = '';
        $tabContent      = '';

        $flag = 0;


        foreach($modules as $module) {

            $moduleFormContent = $this->getModuleConfigurationForm($module);

            if($moduleFormContent !== null) {

                $active = '';
                $id     = 'id'.$module;

                if($flag === 0) {
                    $active = 'active';
                }

                $tabs       .= '<li class="'.$active.'"><a href="#'.$id.'" data-toggle="tab">'.$module.'</a></li>';

                $tabContent .= '<div class="tab-pane '.$active.'" id="'.$id.'">'.PHP_EOL;
                $tabContent .= $moduleFormContent;
                $tabContent .= '</div>'.PHP_EOL;

                $flag++;
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
        $content .= '<div class="setup-button-cont"><a class="btn btn-success setup-pass-page">'.$translator->translate('tr_melis_installer_common_next').'</a></div>';

        die($content);

    }

    public function getModuleConfigurationForm($module)
    {
        $content    = '';
        $controller = 'MelisSetup';
        $action     = 'setupForm';

        $namespace  = $module.'\\Controller\\'.$controller .'Controller';

        if(class_exists($namespace) && method_exists($namespace, $action.'Action')) {

            $viewModel  = $this->forward()->dispatch($module.'\\Controller\\'.$controller, array('action' => $action));

            $renderer   = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
            $html       = new \Zend\Mime\Part($renderer->render($viewModel));

            $content    = (string) $html->getContent();

        }
        else {
            $content = null;
        }

        return $content;
    }

    public function submitModuleConfigurationForm($module, $params)
    {

        $controller = 'MelisSetup';
        $action     = 'setupResult';

        $namespace  = $module.'\\Controller\\'.$controller .'Controller';

        if(class_exists($namespace) && method_exists($namespace, $action.'Action')) {

            $class   = $module.'\\Controller\\'.$controller;
            $result  = $this->forward()->dispatch($class, array_merge(array('action' => $action), $params));

            if($result instanceof JsonModel) {
                return $result->getVariables();
            }
        }
        else {
            return null;
        }

    }


    public function submitModuleConfigurationFormAction()
    {

        $mm      = $this->getServiceLocator()->get('ModuleManager');
        $config  = $this->getServiceLocator()->get('MelisInstallerConfig');
        $modules = array_keys($mm->getLoadedModules());

        $params  = $this->params()->fromQuery();


        $defaultModules  = $config->getItem('melis_installer/datas/module_default');
        $modules         = array_diff($modules, array_merge($defaultModules, array('MelisInstaller', 'MelisModuleConfig')));

        $errors = array();
        $success = true;

        $container = new Container('melis_modules_configuration_status');

        foreach($modules as $module) {

            $container->$module = true;

            $result = $this->submitModuleConfigurationForm($module, $params);


            if(is_array($result)) {

                if($result['errors']) {
                    $errors[] = array(
                        'errors'  => $result['errors'],
                        'message' => $result['message'],
                        'name'    => $module,
                        'success' => (bool) $result['success']
                    );
                    $success = false;
                    $container->$module = (bool) $success;
                }
                else {
                    $container->$module = true;
                }
            }
        }

        $data = array(
            'success' => $success,
            'errors' => $errors
        );
        header('Content-Type: application/json');
        die(Json::encode($data));

    }

    public function testViewSessionAction()
    {
        $data = new Container('melis_modules_configuration_status');
        print_r($data->getArrayCopy());
        die;
    }
    
    function createNewUserAction()
    {
        $success = 0;
        $errors  = array();
    
        if($this->getRequest()->isPost()) {
            
            $createUserForm  = $this->getForm('melis_installer/forms/melis_installer_user_data');
            $postValues      = get_object_vars($this->getRequest()->getPost());
            $createUserForm->setData($postValues);
            
            if($createUserForm->isValid()) {
                $container = new Container('melisinstaller');
                $password  = md5($postValues['password']);
                $container['user_data'] = $postValues;
                $success = 1;
            }
            else {
                $errors = $createUserForm->getMessages();
            }
            
            $melisMelisInstallerConfig = $this->getServiceLocator()->get('MelisInstallerConfig');
            $appConfigForm = $melisMelisInstallerConfig->getItem('melis_installer/forms/melis_installer_user_data');
            $appConfigForm = $appConfigForm['elements'];
            
            foreach ($errors as $keyError => $valueError)
            {
                foreach ($appConfigForm as $keyForm => $valueForm)
                {
                    if ($valueForm['spec']['name'] == $keyError &&
                        !empty($valueForm['spec']['options']['label']))
                        $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                }
            }
            
        }
        return new JsonModel(array(
            'success' => $success,
            'errors'  => $errors,
        ));
    }


    public function finalizeSetupAction()
    {
        $success   = 0;
        $errors    = array();
        $container = new Container('melisinstaller');

        
        if($this->getRequest()->isXmlHttpRequest()) {

            $docPath = $_SERVER['DOCUMENT_ROOT'].'/../';

            // re-write the module that is being loaded
            $docPath        = $_SERVER['DOCUMENT_ROOT'] . '/../';
            $moduleLoadFile = $docPath.'config/melis.module.load.php';
            if(file_exists($moduleLoadFile)) {
                $content = file_get_contents($moduleLoadFile);
                $content = str_replace(array("'MelisInstaller',\n",), '', $content);

                file_put_contents($moduleLoadFile, $content);

            }

            unlink($docPath.'config/melis.modules.path.php');

            $this->getEventManager()->trigger('melis_install_last_process_start', $this, $container->getArrayCopy());

            // replace the application.config
            $moduleSvc = $this->getServiceLocator()->get('MelisInstallerModulesService');
            $melisInstallPath = $moduleSvc->getModulePath('MelisInstaller');
            $appLoader        = $melisInstallPath . '/etc/application.config.php';

            if(file_exists($appLoader)) {
                unlink($docPath.'/config/application.config.php');
                copy($appLoader, $docPath.'/config/application.config.php');
            }

            $success = 1;

            // clear melis installer session
            //$container->getManager()->destroy();
        }

        return new JsonModel(array(
            'success' => $success,
            'errors' => $errors
        ));
    }
    

    
    public function getDatabaseInstallStatusAction()
    {
        
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        $tables = $installHelper->getImportedTables();
        $status = array();
        
        $container = new Container('melisinstaller');
        $container['db_install_tables'] = $tables;
        foreach($tables as $table) {
            if($installHelper->isDbTableExists($table)) {
                $status['installed'][] = $table;
            }else {
                $status['failed'][] = $table;
            }
        }
        
        return array('status' => $status);
    }
    
    /**
     * Execute this when setup has errors or setup has failed
     */
    public function rollBackAction()
    {
        
        $success = 0;
        $errors  = array();
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        $container = new Container('melisinstaller');
        
        if(!empty($container->getArrayCopy()) && in_array(array_keys($container['steps']), array($this->steps))) {
            $tablesInstalled = isset($container['db_install_tables']) ? $container['db_install_tables'] : array();
            $siteModule = 'module/MelisSites/'.$container['cms_data']['website_module'].'/';
            $dbConfigFile = 'config/autoload/platforms/'.$installHelper->getMelisPlatform().'.php';
            $config = include($dbConfigFile);
            // drop table
            $installHelper->setDbAdapter($config['db']);
            
            foreach($tablesInstalled as $table) {

                  if($installHelper->isDbTableExists($table)) {
                      $installHelper->executeRawQuery("DROP TABLE " . trim($table));
                  }
            }
            
            // delete site module
            if(file_exists($siteModule))
                unlink($siteModule);
            
            //delete db config file
            if(file_exists($dbConfigFile))
                unlink($dbConfigFile);
            
            // clear session
            $container->getManager()->destroy();
            
            $success = 1;
        }
        return new JsonModel(array(
           'success' => $success 
        ));
    }
    
    
    /**
     * Checks the PHP Environment and Variables
     * @return Array
     */
    protected function systemConfigurationChecker()
    {
        $response = array();
        $data     = array();
        $errors   = array();
        $dataExt  = array();
        $dataVar  = array();
        $checkDataExt = 0;
        $success      = 0;
        
        $translator = $this->getServiceLocator()->get('translator');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        
        $installHelper->setRequiredExtensions(array(
            'openssl',
            'json',
            'pdo_mysql',
            'intl',
            'mcrypt',
        ));

        
        foreach($installHelper->getRequiredExtensions() as $ext) {
            if(in_array($ext, $installHelper->getPhpExtensions())) {
                $dataExt[$ext] = $installHelper->isExtensionsExists($ext);
            }
            else {
                $dataExt[$ext] = sprintf($translator->translate('tr_melis_installer_step_1_0_extension_not_loaded'), $ext);
            }
            
        }
        
        $dataVar = $installHelper->checkEnvironmentVariables();

        // checks if all PHP configuration is fine
        if(!empty($dataExt)) {
            foreach($dataExt as $ext => $status) {
                if((int) $status === 1) {
                    $checkDataExt = 1;
                }
                else {
                    $checkDataExt = 0;
                }
            }
        }

        if(!empty($dataVar)) {
            foreach($dataVar as $var => $value) {
                $currentVal = trim($value);
                if(is_null($currentVal)) {
                    $dataVar[$var] = sprintf($translator->translate('tr_melis_installer_step_1_0_php_variable_not_set'), $var);
                    array_push($errors, sprintf($translator->translate('tr_melis_installer_step_1_0_php_variable_not_set'), $var));
                }
                elseif($currentVal || $currentVal == '0' || $currentVal == '-1') {
                    $dataVar[$var] = 1;
                }
            }
        }
        else {
            array_push($errors, $translator->translate('tr_melis_installer_step_1_0_php_requied_variables_empty'));
        }

        // last checking
        if(empty($errors) && $checkDataExt === 1) {
            $success = 1;
        }

        $response = array(
            'success' => $success,
            'errors'  => $errors,
            'data'    => array(
                'extensions' => $dataExt,
                'variables'  => $dataVar,
            ),
        
        );
        
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
        $error = array();
        
        $platform = null;
        $module = null;
        
        
        if (!empty(getenv('MELIS_PLATFORM')))
        {
            $platform = getenv('MELIS_PLATFORM');
        }
        else 
        {
            $error['platform'] = $translator->translate('tr_melis_installer_step_1_1_no_paltform_declared');
        }
        
        if (!empty(getenv('MELIS_MODULE')))
        {
            $module = getenv('MELIS_MODULE');
        }
        else 
        {
            $error['module'] = $translator->translate('tr_melis_installer_step_1_1_no_module_declared');
        }
        
        if (empty($error))
        {
            $success = 1;
        }
        
        $response = array(
            'success' => $success,
            'errors' =>  $error,
            'data' => array(
                'platform' => $platform,
                'module' => $module
            )
        );
        
        return $response;
    }
    
    /**
     * Check the directory rights if it is writable or not
     * @return Array
     */
    protected function checkDirectoryRights()
    {
        $translator    = $this->getServiceLocator()->get('translator');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        
        $configDir = $installHelper->getDir('config');
        $module = $this->getModuleSvc()->getAllModules();
        
        $success = 0;
        $errors  = array();
        $data    = array();

        for($x = 0; $x < count($configDir); $x++) {
            $configDir[$x] = 'config/'.$configDir[$x];
        }
        array_push($configDir,'config');
        
        /**
         * Add config platform, MelisSites and public dir to check permission
         */
        array_push($configDir, 'config/autoload/platforms/');
        array_push($configDir, 'module/MelisModuleConfig/');
        array_push($configDir, 'module/MelisModuleConfig/languages');
        array_push($configDir, 'module/MelisModuleConfig/config');
        array_push($configDir, 'module/MelisSites/');
        array_push($configDir, 'public/');
        array_push($configDir, 'cache/');
        array_push($configDir, 'test/');

        for($x = 0; $x < count($module); $x++) {
            $module[$x] = $this->getModuleSvc()->getModulePath($module[$x], false).'/config';
        }
        
        $dirs = array_merge($configDir, $module);

        $results = array();
        foreach($dirs as $dir) {
            if(file_exists($dir)) {
                if($installHelper->isDirWritable($dir)) {
                    $results[$dir] = 1;
                }
                else {
                    $results[$dir] = sprintf($translator->translate('tr_melis_installer_step_1_2_dir_not_writable'), $dir);
                    array_push($errors, sprintf($translator->translate('tr_melis_installer_step_1_2_dir_not_writable'), $dir));
                }
            }
        }
        
        if(empty($errors)) {
            $success = 1;
        }
        
        $response = array(
            'success' => $success,
            'errors' => $errors,
            'data' => $results
        );
        
        
        return $response;
    }
    
    /**
     * Returns the set environments in the session
     * @return Array
     */
    protected function getEnvironments()
    {
        $env = array();

        $container = new Container('melisinstaller');
        if(isset($container['environments']) && isset($container['environments']['new'])) {
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
        $env = array();

        $container = new Container('melisinstaller');
        if(isset($container['environments']) && isset($container['environments']['default_environment'])) {
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
        $data = array();
        
        $container = new Container('melisinstaller');
        if(isset($container['database'])) {
            $data = $container['database'];
        }
        
        return $data;
    }
    
    /**
     * Retrieve's the current set values for user credentials to array
     * @return Array
     */
    public function loadUserCrendetialFromSession()
    {
        $data = array();
        
        $container = new Container('melisinstaller');
        if(isset($container['user_data'])) {
            $data = $container['user_data'];
        }
        
        return $data;
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
        
        if(file_exists($path)) {
            $isExists = 1;
        }
        
        return $isExists;
    }
    
    /**
     * Retrieves the array configuration from app.forms
     * @param string $configPath
     * @return Form
     */
    protected function getForm($configPath)
    {
        $form       = null;
        $melisConfig = $this->serviceLocator->get('MelisInstallerConfig');
        $formConfig = $melisConfig->getItem($configPath);
    
        if($formConfig) {
            $factory = new \Zend\Form\Factory();
            $formElements = $this->getServiceLocator()->get('FormElementManager');
            $factory->setFormElementManager($formElements);
            $form = $factory->createForm($formConfig);
        }
    
        return $form;
    }
    
    public function getModuleSvc()
    {
        $moduleSvc = $this->getServiceLocator()->get('MelisAssetManagerModulesService');
        return $moduleSvc;
    }
    
    private function mapDirectory($dir, $moduleName) {
        $installSvc = $this->getServiceLocator()->get('InstallerHelper');
        $result = array();
    
        $cdir = scandir($dir);
        $fileName = '';
        foreach ($cdir as $key => $value) {
            if (!in_array($value,array(".",".."))) {
                if (is_dir($dir . '/' . $value)) {
                    $result[$dir . '/' .$value] = $this->mapDirectory($dir . '/' . $value, $moduleName);
                }
                else {
                    $result[$dir . '/' .$value] = $value;
                    $fileName = $dir . '/' .$value;
                    $installSvc->replaceFileTextContent($fileName, $fileName,'[:ModuleName]', $moduleName);
                }
            }
        }
         
        return $result;
    }
    public function checkConfigAction()
    {
        $success = 0;
        $errors  = array();

        if($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->systemConfigurationChecker();
            $success  = $response['success'];
            $errors   = $response['errors'];

        }

        return new JsonModel(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    private function mapDirectoryDemo($dir, $targetModuleName, $newModuleName) {
        $installSvc = $this->getServiceLocator()->get('InstallerHelper');
        $result = array();
    
        $cdir = scandir($dir);

        $fileName = '';
        foreach ($cdir as $key => $value) {
            if (!in_array($value,array(".",".."))) {
                if (is_dir($dir . '/' . $value)) {

                    if ($value == $targetModuleName) {
                        rename($dir . '/' . $value, $dir . '/' . $newModuleName);
                        $value = $newModuleName;
                    }elseif ($value == $this->moduleNameToViewName($targetModuleName)) {
                        $newModuleNameSnakeCase = $this->moduleNameToViewName($newModuleName);

                        rename($dir . '/' . $value, $dir . '/' . $newModuleNameSnakeCase);
                        $value = $newModuleNameSnakeCase;
                    }

                    $result[$dir . '/' .$value] = $this->mapDirectoryDemo($dir . '/' . $value, $targetModuleName, $newModuleName);
                }
                else {

                    $newFileName = str_replace($targetModuleName, $newModuleName, $value);
                    if ($value != $newFileName) {
                        rename($dir . '/' . $value, $dir . '/' . $newFileName);
                        $value = $newFileName;
                    }

                    $result[$dir . '/' .$value] = $value;
                    $fileName = $dir . '/' .$value;
                    $installSvc->replaceFileTextContent($fileName, $fileName, $targetModuleName, $newModuleName);
                }
            }
        }
         
        return $result;
    }
    
    function moduleNameToViewName($string) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
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
    
        die;
    }

    public function testAction()
    {
        $svc = $this->getServiceLocator()->get('InstallerHelper');

        $module = $svc->getPackagistMelisModules();

        print_r($module);
        die;
    }


    
}