<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SiteMenuCustomizationListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'*',
            array(
                'melisfornt_site_menu_plugin',
            ),
        	function($e){
        	    // Getting the Service Locator from param target
        	    $this->serviceLocator = $e->getTarget()->getServiceLocator();
        	    // Getting the Site config "MelisDemoCms.config.php"
        	    $siteConfig = $this->serviceLocator->get('config');
        	    $siteConfig = $siteConfig['site']['MelisDemoCms'];
        	    $siteDatas = $siteConfig['datas'];
        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();
        	    
        	    if (!empty($params['menu']))
        	    {
        	        // Geeting the custom datas from site config
        	        $limit = (!empty($siteDatas['sub_menu_limit'])) ? $siteDatas['sub_menu_limit'] : null;
                    $newsMenuPageId = (!empty($siteDatas['news_menu_page_id'])) ? $siteDatas['news_menu_page_id'] : null;
                    
                    $sitePages = (!empty($params['menu'][0]->children)) ? $params['menu'][0]->children : array();
                    if (!empty($sitePages))
                    {
                        /**
                         * Modifying the heirarchy of site menu
                         * this process will make the Homepage and subpages at the same level
                         */
                        $homePage = $params['menu'];
                        $homePage[0]->children = array();
                        $sitePages = array_merge($homePage, $sitePages);
                    }
                    
                    // Customize Site menu using MelisDemoCmsService
                    $melisDemoCmsSrv = $this->serviceLocator->get('MelisDemoCmsService');
                    $params['menu'] = $melisDemoCmsSrv->customizeSiteMenu($sitePages, 1, $limit, $newsMenuPageId);
        	    }
        	},
        100);
        
        $this->listeners[] = $callBackHandler;
    }
    
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}