<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisDemoCms\Controller\BaseController;

class ContentController extends BaseController
{
    public function listAction(){
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
    
    public function detailsAction(){
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
}