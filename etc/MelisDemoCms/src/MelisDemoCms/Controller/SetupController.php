<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisDemoCms\Controller\BaseController;
use Zend\View\Helper\ViewModel;
use Zend\View\Model\JsonModel;

class SetupController extends BaseController
{
    
    public function __construct()
    {
        set_time_limit (1000);
    }
    
    public function setupAction()
    {
        $setupSrv = $this->getServiceLocator()->get('SetupDemoCmsService');
        $setupSrv->siteConfigCheck();
        
        $this->layout('MelisDemoCms/setupLayout');
        $view = new ViewModel();
        
        $tablePlatform = $this->getServiceLocator()->get('MelisPlatformTable');
        $platform = $tablePlatform->getEntryByField('plf_name', getenv('MELIS_PLATFORM'))->current();
        
        if (empty($platform))
        {
            exit('Current Platform "'.getenv('MELIS_PLATFORM').'" has no data on database.');
        }
        
        return $view;
    }
    
    public function executeSetupAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $post = get_object_vars($request->getPost());
            
            // DemoCms Service that process the DemoCms pre-defined datas
            $setupSrv = $this->getServiceLocator()->get('SetupDemoCmsService');
            $setupSrv->setup(getenv('MELIS_PLATFORM'));
            $setupSrv->setupSiteDomain($post['protocol'], $post['domain']);
        }
        
        return new JsonModel(array());
    }

    public function setupFormAction()
    {
        $siteConfig = $this->getServiceLocator()->get('config');

        $formSetupDemoCms = $siteConfig['plugins']['melis_demo_cms_setup'];


        $view = new ViewModel();

        $view->formSetupDemoCms = $formSetupDemoCms;
        //$this->layout('MelisDemoCms/setup');
        $view->setTerminal(true);

        return $view;

    }
    public function setupResultAction()
    {

    }
}