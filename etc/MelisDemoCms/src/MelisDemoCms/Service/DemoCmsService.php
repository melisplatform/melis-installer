<?php

/**
 * Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisDemoCms\Service;

use MelisCore\Service\MelisCoreGeneralService;

/**
 * MelisDemoCms Services
 */
class DemoCmsService extends MelisCoreGeneralService
{
    /**
     * This method will customize the Site menu
     * Customization of the menu will also depend on the template to render the Site menu
     * 
     * @param Array $siteMenu, hierarchyral array composed of pages
     * @param In $level, level of hierarchyral, in order to Identify the leveling of subpages
     * @param int $limit, to limit the subpage, set on config.MelisDemoCms.php
     * @param int $newsMenuPageId, custom data to be modified, set on config.MelisDemoCms.php
     * 
     * @return Array
     */
    public function customizeSiteMenu($siteMenu, $level, $limit = null, $newsMenuPageId = null)
    {
        // Modified Site Menu handler
        $modifiedSiteMenu = array();
    
        if (!empty($siteMenu))
        {
            foreach ($siteMenu As $key => $val)
            {
                // Scaping the recursive after customization of the News Menu
                $scapeRecursive = false;
                if ($val['idPage'] == $newsMenuPageId)
                {
                    // Retrieving the News list using another method
                    $val['pages'] = $this->getNewsListFormMenu($val['idPage'], $limit);
                    // scape recursive to avoid infinite loop
                    $scapeRecursive = true;
                }
                
                if (!empty($val['pages']) && !$scapeRecursive)
                {
                    // Calling function itself to retrieve the subpages, using the children as paramater
                    $val['pages'] = $this->customizeSiteMenu($val['pages'], $level + 1, $limit, $newsMenuPageId);
                }
                
                // Pushing Data to final modified sitemenu
                $modifiedSiteMenu[] = $val;
                
                /**
                 * Static level condition to filter the menu
                 * Layout to render the Menu uses 3 levels
                 */
                if ($level == 3)
                {
                    /**
                     * Checking the if length of the menu on 3rd level 
                     * is equal to the limit set on config then return
                     */
                    if (count($modifiedSiteMenu) == $limit)
                    {
                        return $modifiedSiteMenu;
                    }
                }
            }
        }
        
        return $modifiedSiteMenu;
    }
    
    /**
     * This method will return a list of recent News by months
     * News group by month and get only the recent 4 months from the current date
     * 
     * @param int $newsId, Id of the News page create on MelisCms
     * @param int $limit, to limit the subpage, set on config.MelisDemoCms.php
     * 
     * @return Array
     */
    public function getNewsListFormMenu($newsId, $limit = null)
    {
        // Getting the Site config "MelisDemoCms.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCms'];
        $siteDatas = $siteConfig['datas'];
        // Getting the Page Id where the News Details will render
        $newsDetailsIdPage = $siteDatas['news_details_page_id'];
        
        /**
         * Retriving the List of Recent 4 News, this is group by month
         * Sample result : 
         *      array(
         *          'month' => 1,
         *          'year' => 2017,
         *      ),
         *      array(
         *          'month' => 12,
         *          'year' => 2016,
         *      ),
         *      array(
         *          'month' => 11,
         *          'year' => 2016,
         *      ),
         *      array(
         *          'month' => 10,
         *          'year' => 2016,
         *      ),
         */
        $newsTable = $this->serviceLocator->get('MelisCmsNewsTable');
        $newsList = $newsTable->getNewsListByMonths(4, $siteDatas['site_id'])->toArray();
    
        // Page Tree service to generate Page Link
        $melisTree = $this->serviceLocator->get('MelisEngineTree');
        
        $newsListMenu = array();
        foreach ($newsList As $key => $val)
        {
            /**
             * Retrieving the list of the News filter by month and year
             */
            $news = array();
            if(!empty($val['month']) && !empty($val['year'])){
                $news = $newsTable->getNewsByMonthYear($val['month'], $val['year'], $limit, $siteDatas['site_id'])->toArray();
            }
            
            $newsSubMenu = array();
            foreach ($news As $nKey => $nVal)
            {
                $uri = $melisTree->getPageLink($newsDetailsIdPage, false);
                $newUri = $uri.'?'.http_build_query(array( 
                                    // Custom data added to the anchor of the link
                                    'newsId' => $nVal['cnews_id']
                                ));
                $tmp = array();
                $tmp['idPage'] = $newsDetailsIdPage;
                $tmp['pageType'] = 'Page';
                $tmp['pageStat'] = 1;
                $tmp['label'] = $nVal['cnews_title'];
                $tmp['uri'] = $newUri;
                array_push($newsSubMenu, $tmp);
            }
            
            /**
             * If the news Submenu if empty
             * The Header of the Submenu (level 2) will not included to the News menu submenu
             */
            if (!empty($newsSubMenu))
            {
                // Formating the Date to use as label
                $dateLabel = date('F Y', strtotime('01-'.$val['month'].'-'.$val['year']));
                
                $uri = $melisTree->getPageLink($newsId, 1);
                $newUri = $uri.'?'.http_build_query(array( 
                                    // Custom data added to the anchor of the link
                                    'datefilter' => date('Y-m-d', strtotime($val['year'].'-'.$val['month'].'-01'))
                                ));
                $tmp = array();
                $tmp['idPage'] = $newsId;
                $tmp['pageType'] = 'Page';
                $tmp['pageStat'] = 1;
                $tmp['label'] = $dateLabel;
                $tmp['uri'] = $newUri;
                $tmp['pages'] = $newsSubMenu;
                
                // Pushing the menuData to the newsListMenu as return
                array_push($newsListMenu, $tmp);
            }
        }
    
        
        
        return $newsListMenu;
    }
    
    /**
     * This method will return News list by year and months
     * @param $datefilter if specified this date will be set as selected
     * 
     * @return Array
     */
    public function getNewsListMonthsYears($datefilter = null, $monthDesc = false)
    {
        // Getting the Site config "MelisDemoCms.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCms'];
        $siteDatas = $siteConfig['datas'];
        // Getting the Page Id where the News Details will render
        $newsIdPage = $siteDatas['news_menu_page_id'];
        
        /**
         * Retreiving the the list of News group by months and year
         */
        $newsTable = $this->serviceLocator->get('MelisCmsNewsTable');
        $newsList = $newsTable->getNewsListByMonths(null, $siteDatas['site_id']);
        
        $list = array();
        $addedYear = array();
        
        foreach ($newsList As $key => $val)
        {
            // Checking if the year is already created for groupOption
            if (!in_array($val->year, $addedYear))
            {
                $optgroup = array(
                    'id' => $newsIdPage,
                    'text' => $val->year,
                    'type' => 'optgroup'
                );
                
                // Adding the year groupOption using the year as index
                $list[$val->year] = $optgroup;
                
                // Adding the year to the existing groupOption
                array_push($addedYear, $val->year);
            }
            
            
            // Creating option for each group
            $dataDate = date('Y-m-d', strtotime($val->year.'-'.$val->month.'-01'));
            $option = array(
                'id' => $newsIdPage,
                'text' => date('F', strtotime('01-'.$val->month.'-'.$val->year)),
                'opt-data' => array( // Custom data added to the anchor of the link
                    'datefilter' => $dataDate
                ),
                'type' => 'option',
                'selected' => false
            );
            
            /**
             * Checking if the date is match to the selected date
             * if the date match to the datefilter this date will set as select
             */
            if (!is_null($datefilter))
            {
                if ($dataDate ==  $datefilter)
                {
                    $option['selected'] = true;
                }
            }
            
            // Adding the option of the year
            $list[$val->year]['options'][] = $option;
        }
        
        if ($monthDesc)
        {
            /**
             * Sorting the months of each year to asc
             */
            foreach ($list As $key => $val)
            {
                rsort($list[$key]['options']);
            }
        }
        
        return $list;
    }
}