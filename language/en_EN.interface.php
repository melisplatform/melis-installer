<?php

return array(
    'MELIS Platform V2.0 Setup' => 'MELIS Platform V2.0 Setup',
    
    'tr_melis_installer_navigation_introduction' => 'Introduction',
    'tr_melis_installer_navigation_sysconf' => 'System Configuration',
    'tr_melis_installer_navgitation_vhost' => 'Vhost',
    'tr_melis_installer_navigation_fs' => 'File System Rights',
    'tr_melis_installer_navigation_environments' => 'Environments',
    'tr_melis_installer_navigation_dbcon' => 'Database Connection',
    'tr_melis_installer_navigation_modules' => 'Modules',
    'tr_melis_installer_navigation_platform' => 'Platform Initialization',
    'tr_melis_installer_navigation_final' => 'Creation & Result',
    
    
    'tr_melis_installer_layout_introduction_welcome' => 'Welcome to Melis Platform Setup Guide',
    
    'tr_melis_installer_layout_introduction_welcome_subtext' => 'This setup will guide you through the steps to install melis.',
    'tr_melis_installer_layout_introduction_welcome_sysconfig' => 'SYSTEM CONFIGURATION',
    'tr_melis_installer_layout_introduction_welcome_dbcon' => 'DATABASE CONNECTION',
    'tr_melis_installer_layout_introduction_welcome_platforminit' => 'MELIS PLATFORM INITIALIZATION',
    'tr_melis_installer_layout_introduction_welcome_start_button' => 'Let\'s Start',
    'tr_melis_installer_layout_introduction_welcome_view_online_doc' => 'View Online Documentation',
    'tr_melis_installer_layout_introduction_welcome_php_version_error' => 'Unable to proceed on installation, minimum PHP version required is <strong>%s</strong>, current version installed <strong>%s</strong>',
    
    'tr_melis_installer_layout_sys_req' => 'Step 1: System Requirements',
    'tr_melis_installer_layout_sys_req_subtext' => 'Let\'s check your PHP installed variables and properties.',
    'tr_melis_installer_layout_sys_req_check_php_ext' => 'Checking PHP Extensions',
    'tr_melis_installer_layout_sys_req_check_php_env' => 'Checking PHP Environment Variables',
    
    'tr_melis_installer_layout_vhost' => 'Step 1.1: Vhost Setup',
    'tr_melis_installer_layout_vhost_subtext' => 'Testing your virtual host setup.',

    'tr_melis_installer_layout_fsrights' => 'Step 1.2: File System Rights',
    'tr_melis_installer_layout_fsrights_subtext' => 'Let\'s check your directory rights',
    
    'tr_melis_installer_layout_env' => 'Step 1.3: Environments',
    'tr_melis_installer_layout_env_subtext' => 'Here you can add multiple environments, but you cannot delete the current one that has been setup on your virtual host.',
    'tr_melis_installer_layout_env_default_env' => 'Default Environment',
    'tr_melis_installer_Layout_env_env_name' => 'Environment Name',
    'tr_melis_installer_layout_env_back_office_domain' => 'Back Office Domain',
    'tr_melis_installer_layout_env_add_env' => 'Add Environment',
    'tr_melis_installer_layout_env_remove' => 'remove',
    
    'tr_melis_installer_layout_dbcon' => 'Step 2: Database Connection',
    'tr_melis_installer_layout_dbcon_subtext' => 'Let\'s setup your database, make sure to use the right information to connect to your MySql server.',
    'tr_melis_installer_layout_dbcon_db_details' => 'Database Details',
    'tr_melis_installer_layout_dbcon_form_host' => 'Host',
    'tr_melis_installer_layout_dbcon_form_db' => 'Database',
    'tr_melis_installer_layout_dbcon_form_user' => 'Username',
    'tr_melis_installer_layout_dbcon_form_pass' => 'Password',
    'tr_melis_installer_layout_dbcon_form_test' => 'Test Database Connection',
    'tr_melis_installer_layout_dbcon_promp_title' => 'Database Connection Test Result',
    'tr_melis_installer_layout_dbcon_promp_content' => 'Failed to test your database connection, please details below:',
    'tr_melis_installer_dbcon_form_host_fail' => 'Unable to connect to host',
    'tr_melis_installer_dbcon_form_db_empty' => 'Please enter the database name where you want to install melis',
    'tr_melis_installer_dbcon_form_db_fail' => 'Database connection test failed: the system was unable to reach the database you entered',
    'tr_melis_installer_dbcon_form_user_fail' => 'Please make sure your database username is correct',
    'tr_melis_installer_dbcon_form_pass_fail' => 'Please make sure your database password is correct', 
    'tr_melis_installer_dbcon_form_user_empty' => 'Please enter your MySql username',
    
    'tr_melis_installer_layout_modules' => 'Step 2.1: Install Modules',
    'tr_melis_installer_layout_modules_subtext' => 'Choose the additional modules you want to install and activate right now.',
    'tr_melis_installer_layout_module_available' => 'Available Modules',
    'tr_melis_installer_layout_module_select_all' => 'Select all',
    
    
    'tr_melis_installer_step_1_0_extension_not_loaded' => 'Extension "%s" is not loaded',
    'tr_melis_installer_step_1_0_php_variable_not_set' => 'PHP variable "%s" is not set',
    'tr_melis_installer_step_1_0_php_requied_variables_empty' => 'PHP required variables is not set',
    'tr_melis_installer_step_1_1_platform' => 'Environment Platform',
    'tr_melis_installer_step_1_1_no_paltform_declared' => 'No platform declared',
    'tr_melis_installer_step_1_1_alias_match_failed' => 'AliasMatch on "%s" is not working',
    'tr_melis_installer_step_1_1_alias_match_success' => 'Virtual host AliasMatch is working on "%s"',
    'tr_melis_installer_step_1_2_dir_not_writable' => '"%s" is not writable',
    'tr_melis_installer_step_1_2_dir_writable' => '"%s" is writable',
    
    
    
    'tr_melis_installer_platform_init' => 'Step 3: Melis Platform Initialization',
    'tr_melis_installer_platform_modal_title' => 'Melis Platform Initialization',
    'tr_melis_installer_patform_modal_content' => 'Please check details below: ',
    'tr_melis_installer_platform_init_subtext' => 'Let\'s create an admin account and your first simple site.',
    'tr_melis_installer_platform_init_create_user' => 'Create Admin User',
    'tr_melis_installer_platform_init_sel_language' => 'Website Language',
    'tr_melis_installer_platform_init_create_web' => 'Create a website',
    // NEW USER FORM
    'tr_melis_installer_new_user_login' => 'Login',
    'tr_melis_installer_new_user_email' => 'Email',
    'tr_melis_installer_new_user_password' => 'Password',
    'tr_Melis_installer_new_user_confirm_password' => 'Confirm Password',
    'tr_melis_installer_new_user_first_name' => 'First name',
    'tr_melis_installer_new_user_last_name' => 'Last name',
    
    'tr_melis_installer_new_user_login_empty' => 'Please enter your login',
    'tr_melis_installer_new_user_login_invalid' => 'Invalid login provided',
    'tr_melis_installer_new_user_login_max'   => 'Login value too long',
    'tr_melis_installer_new_user_email_empty' => 'Please enter your email',
    'tr_melis_installer_new_user_email_invalid' => 'Invalid email address',
    
    'tr_melis_installer_new_user_pass_empty' => 'Please enter your password',
    'tr_melis_installer_new_user_pass_max' => 'Password too long',
    'tr_melis_installer_new_user_pass_short' => 'Password too low, it should be more than 8 characters',
    'tr_melis_installer_new_user_pass_invalid' => 'Password should have at least 1 letter and 1 number',
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
    'tr_melis_installer_web_form_name' => 'Website Name',
    'tr_melis_installer_web_form_name_long' => 'Website name too long',
    'tr_melis_installer_web_form_name_empty' => 'Please enter the name of the website',
    
    'tr_melis_installer_web_form_module' => 'Module Name (environnement variable MELIS_MODULE)',
    'tr_melis_installer_web_form_module_label' => 'Module Name',
    'tr_melis_installer_web_form_module_long' => 'Website module name too long',
    'tr_melis_installer_web_form_module_empty' => 'Please enter the module name',
    'tr_melis_installer_web_form_module_exists' => 'Module "%s" already exists, please delete the existing module inside MelisSite module',
    
    'tr_melis_installer_failed_table_install' => 'Failed to install table "%s", please check the query and try again',
    
    'tr_melis_installer_creation_result' => 'Creation & Result',
    'tr_melis_installer_creation_result_subtext' => 'This will install all your provided information',
    
    'tr_melis_installer_common_next' => 'Next',
    'tr_melis_installer_common_finish' => 'Finish',
    
    'tr_melis_installer_common_finish_error' => 'There was a problem while doing the final step, if you want to redo everything, just refresh the page and try again',
    'tr_melis_installer_common_installing' => 'Installing...',
    
);