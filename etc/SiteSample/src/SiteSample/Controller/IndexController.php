<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace [:ModuleName]\Controller;

use MelisFront\Controller\MelisSiteActionController;
use Zend\View\Model\ViewModel;

class IndexController extends MelisSiteActionController
{
    public function homeAction()
    { 	
    	$view = new ViewModel();

    	$view->setVariable('idPage', $this->idPage);
    	$view->setVariable('renderType', $this->renderType);
    	$view->setVariable('renderMode', $this->renderMode);
    	
    	return $view;
    }
}
