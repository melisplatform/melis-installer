<?php

return array(
    'MELIS Platform V2.0 Setup' => 'MELIS Platform V2.0 Setup',
    
    'tr_melis_installer_navigation_introduction' => 'Introduction',
    'tr_melis_installer_navigation_sysconf' => 'System configuration',
    'tr_melis_installer_navgitation_vhost' => 'Vhost',
    'tr_melis_installer_navigation_fs' => 'File system rights',
    'tr_melis_installer_navigation_environments' => 'Environments',
    'tr_melis_installer_navigation_dbcon' => 'Database connection',
    'tr_melis_installer_navigation_platform' => 'Platform initialization',
    'tr_melis_installer_navigation_web_config' => 'Site configuration',
    'tr_melis_installer_navigation_melis_config' => 'Admin account configuration',
    'tr_melis_installer_navigation_modules' => 'Modules',
    'tr_melis_installer_navigation_final' => 'Installation',
    
    'tr_melis_installer_common_close' => 'Close',
    
    'tr_melis_installer_layout_introduction_welcome' => 'Welcome to Melis Platform setup guide',
    
    'tr_melis_installer_layout_introduction_welcome_subtext' => 'This setup will guide you through the steps to install melis.',
    'tr_melis_installer_layout_introduction_welcome_sysconfig' => 'SYSTEM CONFIGURATION',
    'tr_melis_installer_layout_introduction_welcome_dbcon' => 'DATABASE CONNECTION',
    'tr_melis_installer_layout_introduction_welcome_platforminit' => 'MELIS PLATFORM INITIALIZATION',
    'tr_melis_installer_layout_introduction_welcome_start_button' => 'Let\'s start',
    'tr_melis_installer_layout_introduction_welcome_view_online_doc' => 'View online documentation',
    'tr_melis_installer_layout_introduction_welcome_php_version_error' => 'Unable to proceed on installation, minimum PHP version required is <strong>%s</strong>, current version installed <strong>%s</strong>',
    
    'tr_melis_installer_layout_sys_req' => 'Step 1: System requirements',
    'tr_melis_installer_layout_sys_req_subtext' => 'Let\'s check your PHP installed variables and properties.',
    'tr_melis_installer_layout_sys_req_php_version_recommended' => 'Recommended 5.5 to 7.1',
    'tr_melis_installer_layout_sys_req_check_php_ext' => 'Checking required PHP extensions',
    'tr_melis_installer_layout_sys_req_check_php_env' => 'Checking PHP environment variables',
    
    'tr_melis_installer_layout_vhost' => 'Step 1.1: Vhost setup',
    'tr_melis_installer_layout_vhost_subtext' => 'Testing your virtual host setup.',

    'tr_melis_installer_layout_fsrights' => 'Step 1.2: File system rights',
    'tr_melis_installer_layout_fsrights_subtext' => 'Checking the directory rights.',
    
    'tr_melis_installer_layout_env' => 'Step 1.3: Environments',
    'tr_melis_installer_layout_env_subtext' => 'On top of your current environment add here other environments if you wish so.',
    'tr_melis_installer_layout_env_default_env' => 'Default environment',
    'tr_melis_installer_Layout_env_env_name' => 'Environment name',
    'tr_melis_installer_layout_env_back_office_domain' => 'Back office domain',
    'tr_melis_installer_layout_env_add_env' => 'Add environment',
    'tr_melis_installer_layout_env_remove' => 'Remove',
    'tr_melis_installer_layout_env_advance_parameters' => 'Advance parameters',
    'tr_melis_installer_layout_env_send_email' => 'Email sending',
    'tr_melis_installer_layout_env_send_email_info' => 'If your platform cannot send email set this button on &quot;disabled&quot;',
    'tr_melis_installer_layout_env_send_email_enabled' => 'Enabled',
    'tr_melis_installer_layout_env_send_email_disabled' => 'Disabled',
    'tr_melis_installer_layout_env_error_reporting' => 'Error reporting',
    'tr_melis_installer_layout_env_error_reporting_info' => 'Select E_ALL to display the errors or disable with the other option',
    'tr_melis_installer_layout_env_error_reporting_all_except_deprecated' => 'Report all except user-generated warning messages (E_ALL & ~E_USER_DEPRECATED)',
    'tr_melis_installer_layout_env_error_reporting_all' => 'Report all errors (E_ALL)',
    'tr_melis_installer_layout_env_error_reporting_off' => 'Disable the display of errors ',

    
    'tr_melis_installer_layout_dbcon' => 'Step 2: Database connection',
    'tr_melis_installer_layout_dbcon_subtext' => 'Configuration of the database access information.',
    'tr_melis_installer_layout_dbcon_db_details' => 'Database details',
    'tr_melis_installer_layout_dbcon_form_host' => 'Host',
    'tr_melis_installer_layout_dbcon_form_host_info' => 'ex: localhost',
    'tr_melis_installer_layout_dbcon_form_db' => 'Database',
    'tr_melis_installer_layout_dbcon_form_db_info' => 'ex: melis',
    'tr_melis_installer_layout_dbcon_form_user' => 'Username',
    'tr_melis_installer_layout_dbcon_form_user_info' => 'ex: root',
    'tr_melis_installer_layout_dbcon_form_pass' => 'Password',
    'tr_melis_installer_layout_dbcon_form_pass_info' => 'ex: root',
    'tr_melis_installer_layout_dbcon_form_test' => 'Test database connection',
    'tr_melis_installer_layout_dbcon_form_testing' => 'Testing database connection . . .',
    'tr_melis_installer_layout_dbcon_promp_title' => 'Database connection test result',
    'tr_melis_installer_layout_dbcon_promp_content' => 'Failed to test your database connection, please details below:',
    'tr_melis_installer_layout_dbcon_collation_name' => 'Collation name',
    'tr_melis_installer_layout_dbcon_collation_name_invalid' => 'Please make sure your database collation is "utf8_general_ci"',
    'tr_melis_installer_dbcon_form_host_fail' => 'Unable to connect to host',
    'tr_melis_installer_dbcon_form_db_empty' => 'Please enter the database name where you want to install melis',
    'tr_melis_installer_dbcon_form_db_fail' => 'Database connection test failed: the system was unable to reach the database you entered',
    'tr_melis_installer_dbcon_form_user_fail' => 'Please make sure your database username is correct',
    'tr_melis_installer_dbcon_form_pass_fail' => 'Please make sure your database password is correct', 
    'tr_melis_installer_dbcon_form_user_empty' => 'Please type in the username of your database (ex: root)',
    
    'tr_melis_installer_step_1_0_extension_not_loaded' => 'Extension "%s" is not loaded',
    'tr_melis_installer_step_1_0_php_variable_not_set' => 'PHP variable "%s" is not set',
    'tr_melis_installer_step_1_0_php_requied_variables_empty' => 'PHP required variables is not set',
    'tr_melis_installer_step_1_1_platform' => 'Environment platform',
    'tr_melis_installer_step_1_1_module' => 'Site module',
    'tr_melis_installer_step_1_1_no_paltform_declared' => 'You must declare in you vhost an environment variable MELIS_PLATFORM',
    'tr_melis_installer_step_1_1_no_module_declared' => 'You must declare in you vhost an environment variable MELIS_MODULE corresponding to the name of your site module',
    'tr_melis_installer_step_1_1_example_vhost_config' => 'Example of vhost configuration :<br> <br>SetEnv MELIS_PLATFORM "development" <br>SetEnv MELIS_MODULE "MySiteTest"',
    'tr_melis_installer_step_1_1_alias_match_failed' => 'AliasMatch on "%s" is not working',
    'tr_melis_installer_step_1_1_alias_match_success' => 'Virtual host AliasMatch is working on "%s"',
    'tr_melis_installer_step_1_2_dir_not_writable' => '"%s" is not writable',
    'tr_melis_installer_step_1_2_dir_writable' => '"%s" is writable',
    
    // STEP 3: Platform Initialization
    'tr_melis_installer_platform_modal_title' => 'Melis Platform initialization',
    'tr_melis_installer_platform_modal_content' => 'Please check details below: ',
    
    // WEBSITE CONFIGURATION
    'tr_melis_installer_web_config' => 'Step 3.1: Website configuration',
    'tr_melis_installer_web_config_subtext' => 'Creation of the first site.',
    'tr_melis_installer_web_config_option' => 'Website option',
    'tr_melis_installer_web_config_option_none' => 'None',
    'tr_melis_installer_web_config_option_new_site' => 'Create new website',
    'tr_melis_installer_web_config_option_use' => 'Use',
    'tr_melis_installer_web_config_option_use_empty' => 'Please select website option',
    'tr_melis_installer_web_config_create_web' => 'Create a website',
    'tr_melis_installer_web_config_create_web_sel_language' => 'Website language',
    'tr_melis_installer_web_config_empty_vhost_module_name' => 'Module name (MELIS_MODULE) must be set on server vhost before proceeding the setup',
    'tr_melis_installer_web_config_invalid_vhost_module_name' => 'Module name (MELIS_MODULE) in the server vhost is invalid, alphanumeric and underscore are the only valid characters allowed for naming the module name',

    'tr_melis_installer_option_none'         => '<strong>No site</strong> - This option will not create anything (no module, no folder, no page) and you will have to build your site from scratch. <br>Recommended for advanced users or for those having special requirements.',
    'tr_melis_installer_option_new_website'  => '<strong>Create new website</strong> - This option will create a site base including a module with its configuration, its Module.php, a layout, a controller, a first action/view and a first page in the site tree view. <br>Recommanded for those already familiar with the Melis Platform site structure.',
    'tr_melis_installer_option_use_demo_cms' => '<strong>Use the demo site</strong> - This option will import the Melis demo CMS site to be used as a tutorial on how to build a website in Melis Platform. <br>Recommanded for those discovering Melis Platform.',


    // MELIS CONFIGURATION
    'tr_melis_installer_melis_config' => 'Step 3.2 Admin account configuration',
    'tr_melis_installer_melis_config_subtext' => 'Administrator account creation.',
    'tr_melis_installer_melis_config_create_admin' => 'Create admin user',
    
    // MELIS MODULES
    'tr_melis_installer_melis_modules' => 'Step 3.3: Modules',
    'tr_melis_installer_melis_modules_subtext' => 'Selection of the additional modules to install and enable. <br>If you have chosen to activate the demo site some modules cannot be disabled as they are necessary to the proper functioning of the demo site.',
    'tr_melis_installer_melis_modules_available' => 'Available modules',
    'tr_melis_installer_melis_modules_select_all' => 'Select all',
    
    'tr_melis_installer_melis_modules_MelisCalendar' => 'Create and manage events',
    'tr_melis_installer_melis_modules_MelisCmsNews' => 'Create and manage news for your sites',
    'tr_melis_installer_melis_modules_MelisCmsPageHistoric' => 'Visualize the historic of the modifications of your pages',
    'tr_melis_installer_melis_modules_MelisCmsProspects' => 'Manage your contact forms and their associated prospects',
    'tr_melis_installer_melis_modules_MelisDesign' => 'Make use of predefined designs for development purposes',
    'tr_melis_installer_melis_modules_MelisCmsSlider' => 'Create and manage sliders for your sites',
    'tr_melis_installer_melis_modules_MelisCommerce' => 'Create and manage the commerce brick of Melis Platform',
    'tr_melis_installer_melis_modules_MelisSmallBusiness' => 'Add a workflow of page validation, a page versioning and an advanced media library',
    'tr_melis_installer_melis_modules_MelisZendServer' => 'Manage page cache via zendserver',
    'tr_melis_installer_melis_modules_MelisCmsPageAnalytics' => 'Manage the analytics of your sites',
    'tr_melis_installer_melis_modules_MelisMessenger' => 'Exchange messages with other users',
    'tr_melis_installer_melis_modules_MelisMarketPlace' => 'Manage the modules of the platform',
    
    
    // NEW USER FORM
    'tr_melis_installer_new_user_login' => 'Login',
    'tr_melis_installer_new_user_login_info' => 'Username to connect to Melis Platform',
    'tr_melis_installer_new_user_email' => 'Email',
    'tr_melis_installer_new_user_email_info' => 'Email address of the user',
    'tr_melis_installer_new_user_password' => 'Password',
    'tr_melis_installer_new_user_password_info' => 'User password (must be of 8 characters minimum and contain at least a letter and a number)',
    'tr_Melis_installer_new_user_confirm_password' => 'Confirm password',
    'tr_Melis_installer_new_user_confirm_password_info' => 'Password confirmation of the user',
    'tr_melis_installer_new_user_first_name' => 'First name',
    'tr_melis_installer_new_user_first_name_info' => 'First name of the user',
    'tr_melis_installer_new_user_last_name' => 'Last name',
    'tr_melis_installer_new_user_last_name_info' => 'Last name of the user',
    
    'tr_melis_installer_new_user_login_empty' => 'Please enter your login',
    'tr_melis_installer_new_user_login_invalid' => 'Invalid login provided',
    'tr_melis_installer_new_user_login_max'   => 'Login value too long',
    'tr_melis_installer_new_user_email_empty' => 'Please enter your email',
    'tr_melis_installer_new_user_email_invalid' => 'Invalid email address',
    
    'tr_melis_installer_new_user_pass_empty' => 'Please enter your password',
    'tr_melis_installer_new_user_pass_max' => 'Password too long',
    'tr_melis_installer_new_user_pass_short' => 'Password too low, it should be more than 8 characters',
    'tr_melis_installer_new_user_pass_invalid' => 'Password should have 8 characters with at least 1 letter and 1 number',
    'tr_melis_installer_new_user_pass_no_match' => 'Password does not match',
    
    'tr_melis_installer_new_user_first_name_empty' => 'Please enter your first name',
    'tr_melis_installer_new_user_first_name_long' => 'First name too long',
    'tr_melis_installer_new_user_first_name_invalid' => 'Invalid first name provided',
    
    'tr_melis_installer_new_user_last_name_empty' => 'Please enter your last name',
    'tr_melis_installer_new_user_last_name_long' => 'Last name too long',
    'tr_melis_installer_new_user_last_name_invalid' => 'Invalid last name provided',
    
    // Website Language Form
    'tr_melis_installer_web_form_lang' => 'Language',
    
    // Website Form
    'tr_melis_installer_web_form_name' => 'Website name',
    'tr_melis_installer_web_form_name_long' => 'Website name too long',
    'tr_melis_installer_web_form_name_empty' => 'Please enter the name of the website',
    'tr_melis_installer_web_form_name_invalid' => 'Invalid site name, alphanumeric and underscore are the only valid characters allowed',
    
    'tr_melis_installer_web_form_module' => 'Module name (environnement variable MELIS_MODULE)',
    'tr_melis_installer_web_form_module_label' => 'Module name',
    'tr_melis_installer_web_form_module_long' => 'Website module name too long',
    'tr_melis_installer_web_form_module_empty' => 'Please enter the module name',
    'tr_melis_installer_web_form_module_exists' => 'Module "%s" already exists, please delete the existing module inside MelisSite module',
    
    'tr_melis_installer_failed_table_install' => 'Failed to install table "%s", please check the query and try again',
    
    'tr_melis_installer_creation_result' => 'Installation',
    'tr_melis_installer_creation_result_subtext' => 'Validate and start the installation of Melis Platform with the information provided.',
    
    'tr_melis_installer_common_next' => 'Next',
    'tr_melis_installer_common_finish' => 'Install',
    'tr_melis_installer_common_choose' => 'Choose',
    'tr_melis_installer_common_status' => 'Status',
    
    'tr_melis_installer_common_finish_error' => 'There was a problem while doing the final step, if you want to redo everything, just refresh the page and try again',
    'tr_melis_installer_common_installing' => 'Installing . . .',
    'tr_melis_installer_common_finalizing' => 'Finalizing setup . . .',
    
);