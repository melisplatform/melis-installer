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
 * Setup DemoCms Services
 */
class SetupDemoCmsService extends MelisCoreGeneralService
{
    private $siteId;
    private $mainPageId;
    private $curPlatformId;
    private $tplIds = array();
    private $config;
    
    public function setup($environmentName = 'development')
    {
        $tablePlatform = $this->getServiceLocator()->get('MelisPlatformTable');
        $platform = $tablePlatform->getEntryByField('plf_name', $environmentName)->current();
        
        if ($platform)
        {
            $this->curPlatformId = $platform->plf_id;
            
            // Getting the DemoSite config
            $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
            $outputFileName = 'MelisDemoCms.config.php';
            $configDir = $melisSite.'/MelisDemoCms/config/'.$outputFileName;
            $this->config = file_get_contents($configDir);
            
            // Getting the Pre-defined data for MelisDemoSite in config DIR
            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCms.setup.php';
            
            foreach ($setupDatas As $key => $val)
            {
                switch ($key)
                {
                    case 'melis_site' :
                        $this->setupSite($val);
                        break;
                    case 'melis_templates' :
                        $this->setTemplates($val);
                        break;
                    case 'melis_pages' :
                        $this->setupPages($val);
                        break;
                    case 'melis_slider' :
                        $this->setupSliders($val);
                        break;
                    case 'melis_news' :
                        $this->setupNews($val);
                        break;
                    case 'melis_prospects_theme' :
                        $this->setupProspectsThemes($val);
                        break;
                }
            }
            
            // Update the main page id of the Site created for MelisDemoSite
            $this->setupSite(array('site_main_page_id' => $this->mainPageId), $this->siteId);
            
            // Replacing siteId to actual ID of the Site created
            $this->config = str_replace('\'[:siteId]\'', $this->siteId, $this->config);
            
            // Rewrite MelisDemoCms config
            file_put_contents($configDir, $this->config);
        }
    }
    
    public function siteConfigCheck()
    {
        // Getting the DemoSite config
        $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
        $outputFileName = 'MelisDemoCms.config.php';
        $configDir = $melisSite.'/MelisDemoCms/config/'.$outputFileName;
        $moduleConfigFileName = 'module.config.php';
        $moduleConfigDir = $melisSite.'/MelisDemoCms/config/'.$moduleConfigFileName;
        
        if (!is_writable($configDir) || !is_writable($moduleConfigDir))
        {
            exit('Access permission denied, Please make /MelisDemoCms/config/'.$outputFileName.' and /MelisDemoCms/config/'.$moduleConfigFileName.' files writable');
        }
    }
    
    /**
     * Create entry to melis_cms_site for MelisDemoSite
     * @param array $site
     * @param array $siteId
     */
    public function setupSite($site, $siteId = null)
    {
        $siteTbl = $this->getServiceLocator()->get('MelisEngineTableSite');
        if (is_null($siteId))
        {
            /**
             * Set Site main page id to temporary value,
             * Becuase main page of MelisDemoCms is not yet create
             */
            $site['site_main_page_id'] = '-1';
        }
        
        $this->siteId = $siteTbl->save($site, $siteId);
    }
    
    public function setupSiteDomain($protocol, $domain)
    {
        $siteDomainTbl = $this->getServiceLocator()->get('MelisEngineTableSiteDomain');

        $siteDomainTbl->save(array(
            'sdom_site_id' => $this->siteId,
            'sdom_env' => getenv('MELIS_PLATFORM'),
            'sdom_scheme' => $protocol,
            'sdom_domain' => $domain,
        ));
    }
    
    /**
     * Create entry to melis_cm_templates for MelisDemoSite
     * @param array $templates
     */
    private function setTemplates($templates)
    {
        $tplTbl = $this->getServiceLocator()->get('MelisEngineTableTemplate');
        $platformIdsTbl = $this->getServiceLocator()->get('MelisEngineTablePlatformIds');
        
        foreach ($templates As $tpl)
        {
            $platformIds = $platformIdsTbl->getEntryById($this->curPlatformId)->current();
            $tplId = $platformIds->pids_tpl_id_current;
            
            $tpl['tpl_id'] = $tplId;
            $tpl['tpl_site_id'] = $this->siteId;
            $tpl['tpl_creation_date'] = date('Y-m-d H:i:s');
            $tplTbl->save($tpl);
            
            $this->tplIds[$tpl['tpl_zf2_controller'].ucfirst($tpl['tpl_zf2_action'])] = $tplId;
            
            $platformIdsTbl->save(array('pids_tpl_id_current' => ++$tplId), $platformIds->pids_id);
        }
    }
    
    /**
     * Create entry for melis_cms_page_published, melis_cms_page_saved,
     * melis_cms_page_lang, melis_cms_page_tree for MelisDemoSite
     * @param array $pages
     * @param int $fatherId
     */
    private  function setupPages($pages, $fatherId = -1)
    {
        $pageTreeTbl = $this->getServiceLocator()->get('MelisEngineTablePageTree');
        $pageLangTbl = $this->getServiceLocator()->get('MelisEngineTablePageLang');
        $pageSavedTbl = $this->getServiceLocator()->get('MelisEngineTablePageSaved');
        $pagePublishedTbl = $this->getServiceLocator()->get('MelisEngineTablePagePublished');
        $platformIdsTbl = $this->getServiceLocator()->get('MelisEngineTablePlatformIds');
        $melisTablePageSeo = $this->getServiceLocator()->get('MelisEngineTablePageSeo');
        
        // Getting the DemoSite config
        $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
        $outputFileName = 'module.config.php';
        $moduleConfigDir = $melisSite.'/MelisDemoCms/config/'.$outputFileName;
        
        $moduleConfig = file_get_contents($moduleConfigDir);
        
        $pageOrder = 1;
        
        foreach ($pages As $page)
        {
            // Retrieving Next id for Page on Plaform Ids
            $platformIds = $platformIdsTbl->getEntryById($this->curPlatformId)->current();
            $tmpPageId = $platformIds->pids_page_id_current;
            
            $page['columns']['page_id'] = $tmpPageId;
            $page['columns']['page_taxonomy'] = '';
            $page['columns']['page_creation_date'] = date('Y-m-d H:i:s');
            $page['columns']['page_tpl_id'] = ($page['columns']['page_tpl_id'] != '-1') ? $this->tplIds[$page['columns']['page_tpl_id']] : '-1';
            
            if ($page['page_type'] == 'published')
            {
                // Save page to page published table
                $pagePublishedTbl->save($page['columns']);
            }
            else
            {
                // Save page to page saved table
                $pageSavedTbl->save($page['columns']);
            }
            
            // page tree
            $pageTree = array(
                'tree_page_id' => $tmpPageId,
                'tree_father_page_id' => $fatherId,
                'tree_page_order' => $pageOrder++
            );
            $pageTreeTbl->save($pageTree);
            
            // page lang
            $pageLang = array(
                'plang_page_id' => $tmpPageId,
                'plang_lang_id' => 1,
                'plang_page_id_initial' => $tmpPageId,
            );
            $pageLangTbl->save($pageLang);
            
            if (!empty($page['is_main_page']))
            {
                $this->mainPageId = $tmpPageId;
                $this->config = str_replace('\'[:homePageId]\'', $tmpPageId, $this->config);
            }
            
            if (!empty($page['site_config']))
            {
                $this->config = str_replace('\'[:'.$page['site_config'].']\'', $tmpPageId, $this->config);
                $moduleConfig = str_replace('\'[:'.$page['site_config'].']\'', $tmpPageId, $moduleConfig);
            }
            
            // Page SEO
            if (!empty($page['seo']))
            {
                $page['seo']['pseo_id'] = $tmpPageId;
                $melisTablePageSeo->save($page['seo']);
            }
            
            // Updating the CMS Platform Ids
            $platformIdsTbl->save(array('pids_page_id_current' => ($tmpPageId + 1)), $platformIds->pids_id);
            
            if (!empty($page['page_subpages']))
            {
                $this->setupPages($page['page_subpages'], $tmpPageId);
            }
        }
        
        file_put_contents($moduleConfigDir, $moduleConfig);
    }
    
    /**
     * Create entry for melis_cms_slider and melis_cms_slider_details for MelisDemoSite
     * @param array $sliders
     */
    private  function setupSliders($sliders)
    {
        $sliderTbl = $this->getServiceLocator()->get('MelisCmsSliderTable');
        $sliderDetailsTbl = $this->getServiceLocator()->get('MelisCmsSliderDetailTable');
        
        foreach ($sliders As $slider)
        {
            $slider['columns']['mcslide_date'] = date('Y-m-d H:i:s');
            $sliderId = $sliderTbl->save($slider['columns']);
            
            if (!empty($slider['site_config']))
            {
                $this->config = str_replace('\'[:'.$slider['site_config'].']\'', $sliderId, $this->config);
            }
            
            if (!empty($slider['slider_details']))
            {
                $sliderOrder = 1;
                foreach ($slider['slider_details'] As $sliderDetail)
                {
                    $sliderDetail['mcsdetail_mcslider_id'] = $sliderId;
                    $sliderDetail['mcsdetail_order'] = $sliderOrder++;
                    $sliderDetailsTbl->save($sliderDetail);
                }
            }
        }
    }
    
    /**
     * Create entry for melis_cms_news for MelisDemoSite
     * @param array $news
     */
    private function setupNews($news)
    {
        $newsTbl = $this->getServiceLocator()->get('MelisCmsNewsTable');
        
        $ctr = 0;
        $monthCtr = 0;
        foreach ($news As $val)
        {
            $val['cnews_creation_date'] = date('Y-m-d H:i:s', strtotime(' - '.$monthCtr.' month'));
            $val['cnews_publish_date'] = date('Y-m-d H:i:s', strtotime(' - '.$monthCtr.' month'));
            $val['cnews_site_id'] = $this->siteId;
            $newsTbl->save($val);
            
            if (++$ctr == 4) {
                $ctr = 0;
                $monthCtr++;
            }
        }
    }
    
    /**
     * Creating entry for melis_cms_prospects_themes and melis_cms_prospects_theme_items
     * @param array $items
     */
    private function setupProspectsThemes($items)
    {
        $themeTbl = $this->getServiceLocator()->get('MelisCmsProspectsThemeTable');
        $themeItemTbl = $this->getServiceLocator()->get('MelisCmsProspectsThemeItemTable');
        $themeItemTransTbl = $this->getServiceLocator()->get('MelisCmsProspectsThemeItemTransTable');
        
        foreach ($items As $val)
        {
            $themItems = $val['pros_theme_items_trans'];
            unset($val['pros_theme_items_trans']);
            $themeId = $themeTbl->save($val);
                        
            foreach ($themItems As $vItems)
            {
                $themItemData = array(
                    'pros_theme_id' =>  $themeId 
                );
                
                $itemId = $themeItemTbl->save($themItemData);
                
                $vItems['item_trans_theme_item_id'] = $itemId;
                
                $themeItemTransTbl->save($vItems);
                
            }
        }
    }
}