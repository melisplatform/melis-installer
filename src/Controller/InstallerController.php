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

class InstallerController extends AbstractActionController
{
    
    protected $steps = array('sysconfig', 'vhost', 'fsrights', 'environments', 'dbconn', 'selmod', 'pf_init' );
    
    public function indexAction() 
    {
        
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        
        $melisCoreConfig = $this->serviceLocator->get('MelisInstallerConfig');
        $resources = $melisCoreConfig->getItem('melis_installer/ressources');
        $translations = $this->getServiceLocator()->get('MelisCoreTranslation');
        $locales      = $translations->getTranslationsLocale();

        
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
        if(isset($container['cms_data']['language'])) {
            $webLangForm->get('language')->setValue($container['cms_data']['language']);
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
            
        $view = new ViewModel();
        // pre-loaded stuffs 
        $view->currentLocale            = $currentLocale;
        $view->setupLocales             = $locales;
        $view->setup1_0                 = $this->systemConfigurationChecker();
        $view->setup1_0_phpversion      = phpversion();
        $view->setup1_1                 = $this->vHostSetupChecker();
        $view->setup1_2                 = $this->checkDirectoryRights();
        $view->setup1_3                 = $this->getEnvironments();
        $view->setup1_3_env_name        = $installHelper->getMelisPlatform();
        $view->setup1_3_env_domain      = $this->getRequest()->getServer()->SERVER_NAME;
        $view->setup2                   = $this->loadDatabaseCredentialFromSession();
        $view->setup3_hasMelis          = $this->hasMelisCmsModule();
        $view->setup3_webConfigOption   = $webConfigOption;
        $view->setup3_showWebForm       = $showWebForm;
        $view->setup3_webLangForm       = $webLangForm;
        $view->setup3_webForm           = $webForm;
        $view->setup3_createUserForm    = $createUserForm;
        $view->setup3_3_modules         = $this->getModuleSvc()->getModulePlugins(array('MelisCms', 'MelisModuleConfig', 'MelisAssetManager'));
        $view->setup3_3_selected        = $selectedModules;
        $view->setup3_3_requiredModules = $requiredModules;
        
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
            $response = $this->vHostSetupChecker();
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
            $response = $this->systemConfigurationChecker();
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

            $currentDomain = $data['domain'];
            $domainEnv     = array();
            
            // remove domain key
            unset($data['domain']); 
            for($x = 0; $x <= count($data)/2; $x++) {
                $environmentName = 'environment_name_'.($x+1);
                $domainName     =  'domain_'.($x+1);
                if(isset($data[$environmentName]) && isset($data[$domainName]) &&
                    $data[$environmentName] != '' && $data[$domainName] != '') {
                    $domainEnv[] = array(
                        'environment' => $data[$environmentName],
                        'domain' => $data[$domainName]
                    );
                }

            }

            $request = array(
              'platformDomain' => $currentDomain, 
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
                                    $success = 1;
                                    // add status to session
                                    $container = new Container('melisinstaller');
                                    $container['steps'][$this->steps[4]] = array('page' => 5, 'success' => $success);
                                
                                    $container = new Container('melisinstaller');
                                    $container['database'] = $data;
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
                            if(file_exists($melisSite.'/'.$container['cms_data']['weboption']))
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
    
    function testFunctionAction()
    {
        $container = new Container('melisinstaller');
        
        //unset($container['cms_data']);
        echo '<pre>';
        print_r($container['cms_data']);
        echo '</pre>';
        
        return new JsonModel(array());
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
    
    public function completeInstallationAction()
    {
        $success = 0;
        $errors  = array();
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        $container = new Container('melisinstaller');
        $container['installation_process'] = array();
        $translator = $this->getServiceLocator()->get('translator');
        
        // make sure that the session is not empty
       if(!empty($container->getArrayCopy()) && in_array(array_keys($container['steps']), array($this->steps)) && $this->getRequest()->isXmlHttpRequest()) {

            $checkSteps     = $container['steps'];
            $platforms      = $container['platforms'];
            $environments   = $container['environments'];
            $database       = $container['database'];
            $userData       = $container['user_data'];
            $cmsData        = $container['cms_data'];
            $installModules = $container['install_modules'];

            foreach($checkSteps as $step => $content) {
                if((int) $content['success'] != 1) {
                    array_push($errors, array($step => array('page', $content['page'])));
                }
                $container['installation_process'] = $errors;
            }
            
            if(empty($errors)) {
               
           
                //Create the site module with basic files to start
                if($this->hasMelisCmsModule()) {
                    
                    // MelisSites dir
                    $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
                    
                    // create a new site inside MelisSite module
                    
                    if ($cmsData['weboption'] == 'NewSite') {
                        // make MelisSite module writable
                        $installHelper->filePermission($melisSite);
                        
                        if(file_exists($melisSite)) {
                            
                            // re-check if the MelisSite is now writable
                            if(is_writable($melisSite)) {
                                // let's do the magic
                                $siteModuleName  = $cmsData['web_form']['website_module'];
                                $siteWebsiteName = $cmsData['web_form']['website_name'];
                                $siteSample      = $this->getModuleSvc()->getModulePath('MelisInstaller').'/etc/SiteSample';
                                $siteDestination = $melisSite.'/'.$siteModuleName;
                                $siteModuleViewPath = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $siteModuleName));
                                $lowerSiteName = strtolower($siteModuleName);
                                
                                // rewrite if exists
                                if(file_exists($siteDestination))
                                {
                                    unlink($siteDestination);
                                }
                                    
                                // make a copy of a site template files into the MelisSites module
                                $makeCopy = $installHelper->xcopy($siteSample, $siteDestination);
                                if($makeCopy && file_exists($siteDestination)) 
                                {
                                    // rewrite directories and files
                                    $moduleConfigPath = $siteDestination.'/config/';
                                    $siteModuleSrc    = $siteDestination.'/src/SiteSample';
                                    $siteModuleFile   = $siteDestination.'/Module.php';
                                    
                                    rename($moduleConfigPath.'sitename.config.php', $moduleConfigPath.$siteModuleName.'.config.php');
                                    // replace file contents
                                    $this->mapDirectory($siteDestination, $siteModuleName);
                                }
                                else 
                                {
                                    array_push($errors, array(
                                        "hasError" => $translator->translate("tr_melis_installer_web_form_module_exists"),
                                        "label" => $translator->translate("tr_melis_installer_web_form_module_label")
                                    ));
                                }
                            }
                        }
                    }
                    elseif ($cmsData['weboption'] != 'None')
                    {
                        $siteModuleName = getenv('MELIS_MODULE');
                        // Copying the Demo Site from etc to MelisSites dir with full permission
                        $siteDestination = $melisSite.'/'.$cmsData['weboption'];
                        $demoSite = $this->getModuleSvc()->getModulePath('MelisInstaller').'/etc/'.$cmsData['weboption'];
                        $installHelper->xcopy($demoSite, $siteDestination, 0777);
                        
                        // Getting the current Modules loaded
                        $manager = $this->getServiceLocator()->get('ModuleManager');
                        $modules        = $manager->getLoadedModules();
                        $loadedModules      = array_keys($modules);
                        
                        /**
                         * Adding required modules from cms_data session to module.load.php 
                         * to access the modules needed to install demo site
                         */
                        $requiredModule = $cmsData['required_modules'];
                        /**
                         * Adding the new Site module name as required module
                         * to enable to call service for installation
                         */
                        array_push($requiredModule, $siteModuleName);
                        
                        // Unset MelisInstaller to make this last module loaded
                        unset($loadedModules['MelisInstaller']);
                        
                        $moduleSvc = $this->getServiceLocator()->get('ModulesService');
                        $moduleSvc->createModuleLoader($_SERVER['DOCUMENT_ROOT'].'/../config/', $requiredModule, $loadedModules, array('MelisInstaller'));

                        $siteDestination = $melisSite.'/'.$siteModuleName;
                        rename($melisSite.'/'.$cmsData['weboption'] , $siteDestination);
                        
                        // replace file contents using the weptoption value as target module name with the new Module Name
                        $this->mapDirectoryDemo($siteDestination, $cmsData['weboption'], $siteModuleName);
                    }
                } // end Write Site File
                
                if($database) {
                    $this->getEventManager()->trigger('melis_install_database_process_start', $this, array('install_modules' => $installModules, 'dbAdapter' => $database));
                    $success = 1;
                }
            }
        } // end check if empty session
        
        return new JsonModel(array(
            'success' => $success,
            'errors' => $errors
        ));
    }
    
    public function finalizeSetupAction()
    {
        $success   = 0;
        $iStatus   = array();
        $errors    = array();
        $container = new Container('melisinstaller');
        $installHelper  = $this->getServiceLocator()->get('InstallerHelper');
        $translator     = $this->getServiceLocator()->get('translator');
        $database       = $container['database'];
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $dbInstallationStatusResponse = $this->getEventManager()->trigger('melis_install_background_process_start', $this,
                array('db_tables' => $installHelper->getImportedTables(), 'dbAdapter' => $database));
            
            $dbInstallationStatusResponse = $dbInstallationStatusResponse[0]['status'];
            
            // check if all the selected tables and required tables has been installed properly
            foreach($dbInstallationStatusResponse as $status => $table) {
                if($status == 'failed')
                    $iStatus = array_merge($iStatus, $table);
            }
            
            // if no error, then proceed on creating the config file and module loader
            if(empty($iStatus))  {
                
                // -> Create Database datas SQL file configured from what was filled in forms
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
                
                    if(file_exists('config/autoload/platforms/'.$fileName)) {
                        @unlink('config/melis.modules.path.php');
                        $success = 1;
                    }
                   
            }
            else {
                foreach($iStatus as $table) {
                    array_push($errors, sprintf($translator->translate('tr_melis_installer_failed_table_install'), $table));
                }
            }
        }
       

        
        return new JsonModel(array(
            'success' => $success,
            'errors' => $errors
        ));
    }
    
    public function installDatabaseDataAction()
    {
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        $success = 0 ;
        if($this->getRequest()->isXmlHttpRequest()) {
            $container = new Container('melisinstaller');
            
            $this->getEventManager()->trigger('melis_install_last_process_start', $this, $container->getArrayCopy());
            $container->getManager()->destroy();
            $success = 1;
        }
        
        return new JsonModel(array(
            'success' => $success,
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
            'mcrypt'
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
                if(empty((trim($value)))) {
                    $dataVar[$var] = sprintf($translator->translate('tr_melis_installer_step_1_0_php_variable_not_set'), $var);
                    array_push($errors, sprintf($translator->translate('tr_melis_installer_step_1_0_php_variable_not_set'), $var));
                }
                else {
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
     * Checks if the AliasMatch set in the Vhost is accessible or not
     * @return Array
     */
    protected function vHostSetupChecker()
    {
        $translator = $this->getServiceLocator()->get('translator');
        $installHelper = $this->getServiceLocator()->get('InstallerHelper');
        
        $success = 0;
        $error   = array();
        $data    = array();
        
        $platform = $installHelper->getMelisPlatform();
        $modules  = array();
        foreach($installHelper->getDir('module') as $module) {
            if(in_array($module, array('MelisCore', 'MelisCms'))) {
                $modules[] = $module;
            }
        }
        
        // URLs to ping
        $pingUrls = array();
        foreach($modules as $path) {
            $pingUrls[] = $path.'/css';
            $pingUrls[] = $path.'/js';
        }
        
        // ping each URLs
        $urlStatus = array();
        foreach($pingUrls as $url) {

            $lastPath = explode('/', $url);
            $checkDir = 'module/'.str_replace('/'.$lastPath[1], '/public/'.$lastPath[1], $url);
            if(file_exists($checkDir)) {
                $checkFiles  = scandir($checkDir.'/');
                
                if($checkFiles) {
                    foreach($checkFiles as $f) 
                        if(is_file($checkDir.'/'.$f))
                            $urlStatus[$url] = $installHelper->getUrlStatus('/'.$url.'/'.$f);
                }
            }
            else {
                $urlStatus[$url] = $installHelper->getUrlStatus('/'.$url);
            }
        }

        // set the succcess status
        $urls = array();
        if(!empty($platform)) {
            foreach($urlStatus as $module => $child) {
                if((int) $child['status'] !== 200) {
                    $urls[$module] = sprintf($translator->translate('tr_melis_installer_step_1_1_alias_match_failed'), $module);
                    array_push($error, sprintf($translator->translate('tr_melis_installer_step_1_1_alias_match_failed'), $module));
                }
                else {
                    $mod = explode('/',$module);
                    $urls[$mod[0]] = 1;
                }
                
            }
        }
        
        if(empty($error)) {
            $success = 1;
        }

        
        $response = array(
            'success' => $success,
            'errors' =>  $error,
            'data' => array(
                'platform' => $platform,
                'aliasMatchStatus' => $urls
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
        array_push($configDir, 'module/MelisSites/');
        array_push($configDir, 'public/');
        array_push($configDir, 'cache/');
        
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
        $modulesSvc = $this->getServiceLocator()->get('ModulesService');
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
     * @param unknown $configPath
     */
    protected function getForm($configPath)
    {
        $form       = null;
        $melisCoreConfig = $this->serviceLocator->get('MelisInstallerConfig');
        $formConfig = $melisCoreConfig->getItem($configPath);
    
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
        $moduleSvc = $this->getServiceLocator()->get('ModulesService');
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
    
}