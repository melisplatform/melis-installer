<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisFront\Controller\MelisSiteActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class BaseController extends MelisSiteActionController
{
    public $view = null;
    
    function __construct()
    {
        $this->view = new ViewModel();
    }
    
    public function onDispatch(MvcEvent $event)
    {
        // Getting the Site config "MelisDemoCms.config.php"
        $sm = $event->getApplication()->getServiceManager();
        $siteConfig = $sm->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCms'];
        $siteDatas = $siteConfig['datas'];
        // Adding the SiteDatas to layout so views can access to the SiteDatas easily
        $this->layout()->setVariable('siteDatas', $siteDatas);
        $this->layout()->setVariable('homepage', $siteConfig['conf']['home_page']);
        
        $pageId = $this->params()->fromRoute('idpage');
        $renderMode = $this->params()->fromRoute('renderMode');
        
		/**
		 * Generating Site Menu using MelisFrontMenuPlugin Plugin
		 */
	    $menuPlugin = $this->MelisFrontMenuPlugin();
	    $menuParameters = array(
	        'template_path' => 'MelisDemoCms/plugin/menu',
	        'pageIdRootMenu' => $siteConfig['conf']['home_page'],
	    );
	    
		// add generated view to children views for displaying it in the contact view
		$menu = $menuPlugin->render($menuParameters);
		$this->layout()->addChild($menu, 'siteMenu');
		
		/**
		 * Generating Page Breadcrumb using MelisFrontBreadcrumbPlugin Plugin
		 * @var unknown $breadcrumbPlugin
		 */
	    $breadcrumbPlugin = $this->MelisFrontBreadcrumbPlugin();
	    $breadcrumbParameters = array(
	        'template_path' => 'MelisDemoCms/plugin/breadcrumb',
	        'pageIdRootBreadcrumb' => $pageId,
	    );
		// add generated view to children views for displaying it in the contact view
		$breadcrumb = $breadcrumbPlugin->render($breadcrumbParameters);
        $this->layout()->addChild($breadcrumb, 'pageBreadcrumb');
        
        return parent::onDispatch($event);
    }
}