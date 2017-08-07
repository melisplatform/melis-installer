<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisDemoCms\Controller\BaseController;
use Zend\View\Model\JsonModel;

class ContactController extends BaseController
{
    public function contactusAction()
    {
		$prospectsForm = $this->MelisCmsProspectsShowFormPlugin();
		$prospectsParamenter = array(
		    'template_path' => 'MelisDemoCms/plugin/contactus',
		    'fields' => 'pros_name,pros_company,pros_country,pros_telephone,pros_email,pros_theme,pros_message',
		    'required_fields' => 'pros_name,pros_telephone,pros_email,pros_theme,pros_message',
		    'theme' => 1
		);
		// add generated view to children views for displaying it in the contact view
        $result = $prospectsForm->render($prospectsParamenter);
        
        if($this->request->isPost()) 
        {
            $pluginVariables = $result->getVariables();
            
            $response = array(
                'success' => $pluginVariables->success,
                'errors' => $pluginVariables->errors,
            );
            
            // return JsonModel
            return new JsonModel($response);
        }
        else 
        {
            // return ViewModel
            $this->view->addChild($result, 'prospectsForm');
            $this->layout()->setVariables(array(
                'pageJs' => array(
                    'https://maps.googleapis.com/maps/api/js?key=AIzaSyA-IIoucJ-70FQg6xZsORjQCUPHCVj9GV4',
                    '/MelisDemoCms/js/google-map.js',
                    '/MelisDemoCms/js/melisSiteHelper.js',
                    '/MelisDemoCms/js/contactus.js',
                ),
            ));
            
            $this->view->setVariable('idPage', $this->idPage);
            $this->view->setVariable('renderMode', $this->renderMode);
            return $this->view;
        }
    }
}