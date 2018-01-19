<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisDemoCms\Controller\BaseController;

class NewsController extends BaseController
{
    /**
     * This method will render the list of news
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function listAction()
    {
        // Getting the Site config "MelisDemoCms.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCms'];
        $siteDatas = $siteConfig['datas'];
        
        /**
         * Listing News using MelisCmsNewsListNewsPlugin
         */
		$listNewsPluginView = $this->MelisCmsNewsListNewsPlugin();
		$listNewsParameters = array(
		    'template_path' => 'MelisDemoCms/plugin/news-list',
            'pageId' => $this->idPage,
            'pageIdNews' => $siteDatas['news_details_page_id'],
	        'pagination' => array(
	            'nbPerPage' => 6
	        ),
	        'filter' => array(
	            'column' => 'cnews_publish_date',
	            'order' => 'DESC',
	            'unpublish_filter' => true,
	            'site_id' => $siteDatas['site_id'],
	        )
		);
		
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($listNewsPluginView->render($listNewsParameters), 'listNews');
        
		$this->view->setVariable('renderMode', $this->renderMode);
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    /**
     * This methos will render the Details of a single News
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        
        // Getting the Site config "MelisDemoCms.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCms'];
        $siteDatas = $siteConfig['datas'];
        
        $dateMax = date("Y-m-d H:i:s", strtotime("now"));
		$listNewsPluginView = $this->MelisCmsNewsShowNewsPlugin();
		$listNewsParameters = array(
		    'id' => 'newsDetails',
		    'template_path' => 'MelisDemoCms/plugin/news-details',
		);
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($listNewsPluginView->render($listNewsParameters), 'newsDetails');
		
		/**
		 * Generating Homepage Latest News slider using MelisCmsNewsLatestNewsPlugin Plugin
		 */
		$latestNewsPluginView = $this->MelisCmsNewsLatestNewsPlugin();
		$latestNewsParameters = array(
		    'template_path' => 'MelisDemoCms/plugin/latest-news',
            'pageIdNews' => $siteDatas['news_details_page_id'],
		    'filter' => array(
		        'column' => 'cnews_publish_date',
		        'order' => 'DESC',
		        'limit' => 10,
		        'unpublish_filter' => true,
		        'date_max' => null,
		        'site_id' => $siteDatas['site_id'],
		    )
		);
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($latestNewsPluginView->render($latestNewsParameters), 'latestNews');
        
        $this->view->setVariable('renderMode', $this->renderMode);
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
}