<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisCore\MelisSetupInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class MelisSetupPostDownloadController extends AbstractActionController implements MelisSetupInterface
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function getFormAction()
    {

        $form = $this->getFormSiteDemo();
        $container = new Container('melis_modules_configuration_status');
        $formData = isset($container['formData']) ? (array) $container['formData'] : null;

        if ($formData) {
            $form->setData($formData);
        }

        # Set site label
        $form->get('site_label')->setValue('Melis Demo Cms Site');
        $view = new ViewModel();
        $view->setVariable('siteDemoCmsForm', $form);

        $view->setTerminal(true);

        //$view->btnStatus = $btnStatus;
        return $view;

    }

    /**
     * Create a form from the configuration
     *
     * @param $formConfig
     *
     * @return \Zend\Form\ElementInterface
     */
    private function getFormSiteDemo()
    {
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getItem('MelisDemoCms/setup/download/form/melis_demo_cms_setup/forms/melis_demo_cms_setup_download_form');


        $factory = new \Zend\Form\Factory();
        $formElements = $this->getServiceLocator()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $form = $factory->createForm($appConfigForm);

        // default data
        $scheme = 'https';
        $domain = $this->getRequest()->getUri()->getHost();

        $data = [
            'sdom_scheme' => $scheme,
            'sdom_domain' => $domain,
        ];

        $form->setData($data);

        return $form;

    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function validateFormAction()
    {
        $success = 0;
        $message = 'tr_install_setup_message_ko';
        $errors = [];

        $data = $this->getTool()->sanitizeRecursive($this->params()->fromRoute());

        $siteDemoCmsForm = $this->getFormSiteDemo();
        $siteDemoCmsForm->setData($data);

        if ($siteDemoCmsForm->isValid()) {
            $success = 1;
            $message = 'tr_install_setup_message_ok';
        } else {
            $errors = $this->formatErrorMessage($siteDemoCmsForm->getMessages());
        }


        $response = [
            'success' => $success,
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors,
            'siteDemoCmsForm' => 'melis_installer_demo_cms',
            'domainForm' => 'melis_installer_domain',
        ];

        return new JsonModel($response);
    }

    /**
     * Returns the Tool Service Class
     * @return MelisCoreTool
     */
    private function getTool()
    {
        $melisTool = $this->getServiceLocator()->get('MelisCoreTool');


        return $melisTool;

    }

    private function formatErrorMessage($errors = [])
    {

        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');

        $appConfigForm = $melisMelisCoreConfig->getItem('MelisDemoCms/setup/download/form/melis_demo_cms_setup/forms/melis_demo_cms_setup_download_form');

        $appConfigForm = $appConfigForm['elements'];

        foreach ($errors as $keyError => $valueError) {
            foreach ($appConfigForm as $keyForm => $valueForm) {
                if ($valueForm['spec']['name'] == $keyError &&
                    !empty($valueForm['spec']['options']['label'])) {
                    $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                }
            }
        }


        return $errors;
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function submitAction()
    {
        $success = 0;
        $message = 'tr_install_setup_message_ko';
        $errors = [];

        $data = null;
        if (!$data) {
            $data = $this->getTool()->sanitizeRecursive($this->params()->fromRoute());
        }
        // $data = $this->getTool()->sanitizeRecursive($this->params()->fromQuery());

        // Getting the DemoSite config
        $config = $this->getServiceLocator()->get('config');
        $siteId = $config['site']['MelisDemoCms']['datas']['site_id'];

        $docPath = $_SERVER['DOCUMENT_ROOT'];

        $setupDatas = include $docPath . '/../module/MelisSites/MelisDemoCms/install/MelisDemoCms.setup.php';
        $siteData = $setupDatas['melis_site'];

        $siteDemoCmsForm = $this->getFormSiteDemo();
        $siteDemoCmsForm->setData($data);

        //Services
        // $tablePlatformIds = $this->getServiceLocator()->get('MelisEngineTablePlatformIds');

        $container = new \Zend\Session\Container('melis_modules_configuration_status');
        $hasErrors = false;


        if ($siteDemoCmsForm->isValid()) {

            try {
                foreach ($container->getArrayCopy() as $module) {
                    if (!$module) {
                        $hasErrors = true;
                    }
                }

                $container = new \Zend\Session\Container('melismodules');
                $installerModuleConfigurationSuccess = isset($container['module_configuration']['success']) ?
                    (bool) $container['module_configuration']['success'] : false;


                //siteDemoCms installation start
                $scheme = $siteDemoCmsForm->get('sdom_scheme')->getValue();
                $domain = $siteDemoCmsForm->get('sdom_domain')->getValue();
                $siteLabel = $siteDemoCmsForm->get('site_label')->getValue();


                //Save siteDemoCms config
                if (false === $hasErrors) {

                    /*
                    * For auto save data
                    */
                    // DemoCms Service that process the DemoCms pre-defined datas


                    $setupSrv = $this->getServiceLocator()->get('SetupDemoCmsService');

                    // $setupSrv->setupSite($siteData);
                    $setupSrv->setup(getenv('MELIS_PLATFORM'), $siteLabel);
                    //$setupSrv->setupSiteDomain($scheme, $domain);

                    $success = 1;
                    $message = 'tr_install_setup_message_ok';
                }
            } catch (\Exception $e) {
                $errors = $e->getMessage();
            }

        } else {
            $errors = $this->formatErrorMessage($siteDemoCmsForm->getMessages());
        }


        $response = [
            'success' => $success,
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors,
            'siteDemoCmsForm' => 'melis_installer_demo_cms',
            'domainForm' => 'melis_installer_domain',
        ];

        return new JsonModel($response);
    }
}
