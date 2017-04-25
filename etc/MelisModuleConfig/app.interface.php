<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

/**
 * This configuration file is the  one you should modified
 * in order to adjust the interface to your needs.
 */

return array(
    'plugins' => array(
        'meliscore' => array(
            'datas' => array(
                'default' => array(
                ),
                [:environment_configurations]
            ),
        ),
        'melisZS' => array(
            'conf' => array(
                'rightsDisplay' => 'none',
            ),
            'datas' => array(
                'zend_server_conf' => array(
                    'development' => array(
                        'api' => array(
                            'serverURI' => 'http://localhost:10081/ZendServer',
                            'username' => 'admin',
                            'apikey' => '23d532ae7b6436839e04ac2e1d04c7ecbb8e8e26e29cddb411441b7947011895',
                        ),
                        'web' => array(
                            'user' => 'admin',
                            'password' => 'melis',
                        )
                    ),
                    'preprod' => array(
                        'api' => array(
                            'serverURI' => 'http://melis2.melistechnology.fr:10081/ZendServer',
                            'username' => 'admin',
                            'apikey' => 'f97c1b88631232feae7ea6b91bd05a4f1e73a43d5127278a3fefd5df24e06792',
                        ),
                        'web' => array(
                            'user' => 'user',
                            'password' => 'password',
                        )
                    ),
                ),
            ),
        ),
    ),
    'interface_ordering' => array(
        'meliscore_leftmenu' => array(
            'meliscore_leftmenu_identity',
            'meliscore_leftmenu_dashboard',
            'meliscms_sitetree',
            'meliscore_toolstree',
            'meliscore_footer',
        ),
        'meliscore_toolstree' => array(
            'meliscore_tool_admin_module' => array(
                'meliscore_tool_user_module_management'
            ),
            'meliscore_tool_admin_section' => array(
                'meliscore_tool_user',
            ),
            'meliscms_tools_section' => array(
                'meliscms_tool_site',
                'meliscms_tool_templates',
                'meliscms_tool_platform_ids'
            ),
            'melisprospects_tools_section' => array(
                'melistoolprospects_tool_prospects',
            ),
        ),
        'meliscore_center_dashboard' => array(
            'meliscore_dashboard_recent_activity',
            'meliscms_dashboard_pages_indicators',
            'melistoolprospects_dashboard_statistics',
            'melissb_dashboard_workflow',
            'meliscore_dashboard_calendar',
        ),
        'meliscore_dashboard_recent_activity' => array(
            'melispagehistoric_dashboard_recent_activity_pages',
            'meliscore_dashboard_recent_activity_users',
        ),
    ),
    'interface_disable' => array(

    )
);