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
        // Getting the Search for News value from query string and added to the layout variable
        $serachNews = $this->params()->fromQuery('search-news', '');
        $this->layout()->setVariable('searchNews', $serachNews);
        
        // Getting the Date from query string to set the selected date on <select> tag
        $dateMin = $this->params()->fromQuery('datefilter', null);
        $demoCmsSrv = $this->getServiceLocator()->get('MelisDemoCmsService');
        $list = $demoCmsSrv->getNewsListMonthsYears($dateMin);
        
        $dateMax = null;
        if (!is_null($dateMin))
        {
            if (is_null($dateMax))
            {
                $dateMax = date('Y-m-t 24:00:00',strtotime($dateMin));
            }
        }
        
        /**
         * Listing News using MelisCmsNewsListNewsPlugin
         */
		$listNewsPluginView = $this->MelisCmsNewsListNewsPlugin();
		$listNewsParameters = array(
		    'template_path' => 'MelisDemoCms/plugin/news-list',
	        'pagination' => array(
	            'current' => (int) $this->params()->fromQuery('page', 1),
	            'nbPerPage' => 6
	        ),
	        'filter' => array(
	            'column' => 'cnews_publish_date',
	            'order' => 'DESC',
	            'date_min' => $dateMin,
	            'date_max' => $dateMax,
	            'search' => $serachNews,
	        )
		);
		
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($listNewsPluginView->render($listNewsParameters), 'listNews');
        
		$this->view->setVariable('renderMode', $this->renderMode);
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('list', $list);
        return $this->view;
    }
    
    /**
     * This methos will render the Details of a single News
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
		$listNewsPluginView = $this->MelisCmsNewsShowNewsPlugin();
		$listNewsParameters = array(
		    'template_path' => 'MelisDemoCms/plugin/news-details',
		    'newsId' => (int) $this->params()->fromQuery('newsid', ''),
		);
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($listNewsPluginView->render($listNewsParameters), 'newsDetails');
		
		/**
		 * Generating Homepage Latest News slider using MelisCmsNewsLatestNewsPlugin Plugin
		 */
		$latestNewsPluginView = $this->MelisCmsNewsLatestNewsPlugin();
		$latestNewsParameters = array(
		    'template_path' => 'MelisDemoCms/plugin/latest-news',
		    'filter' => array(
		        'column' => 'cnews_publish_date',
		        'order' => 'DESC',
		        'limit' => 10,
		    )
		);
		// add generated view to children views for displaying it in the contact view
		$this->view->addChild($latestNewsPluginView->render($latestNewsParameters), 'latestNews');
        
        $this->view->setVariable('renderMode', $this->renderMode);
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
}