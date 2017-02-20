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
		    'template_path' => 'MelisDemoCms/plugin/contactus'
		);
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($prospectsForm->render($prospectsParamenter), 'prospectsForm');
        
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $this->layout()->setVariables(array(
            'pageJs' => array(
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyA-IIoucJ-70FQg6xZsORjQCUPHCVj9GV4',
                $renderer->basePath('/MelisDemoCms/js/google-map.js'),
                $renderer->basePath('/MelisDemoCms/js/melisSiteHelper.js'),
                $renderer->basePath('/MelisDemoCms/js/contactus.js'),
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
    
    public function submitAction()
    {
        // Default Values
        $status  = 0;
        $errors  = array();
         
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $postData = get_object_vars($request->getPost());
            /**
             * Prospects form can be use in different pages,
             * prospects type is required
             * to determine where the prospects had been submitted.
             * In this Demo we use in "Contact Us" page
             */
            $postData['pros_type'] = 'Contact Us';
            $prospectsForm = $this->MelisCmsProspectsShowFormPlugin();
            $prospectsFormParameters = array(
                'post' => $postData,
            );
            // add generated view to children views for displaying it in the contact view
            $result = $prospectsForm->render($prospectsFormParameters)->getVariables();
            
            // Retrieving view variable from view
            $status = $result->success;
            $errors = $result->errors;
        }
        
        $response = array(
            'success' => $status,
            'errors' => $errors,
        );
         
        return new JsonModel($response);
    }
}