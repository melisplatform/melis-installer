<?php

return array(
'MELIS Platform V2.0 Setup' => 'Installation MELIS Platform V2.0',
    
    'tr_melis_installer_navigation_introduction' => 'Introduction',
    'tr_melis_installer_navigation_sysconf' => 'Configuration Système',
    'tr_melis_installer_navgitation_vhost' => 'Vhost',
    'tr_melis_installer_navigation_fs' => 'Droits des Fichiers Système',
    'tr_melis_installer_navigation_environments' => 'Environnements',
    'tr_melis_installer_navigation_dbcon' => 'Connection à la Base de Données',
    'tr_melis_installer_navigation_platform' => 'Initialisation de la Plateforme',
    'tr_melis_installer_navigation_web_config' => 'Configuration Internet',
    'tr_melis_installer_navigation_melis_config' => 'Configuration Melis',
    'tr_melis_installer_navigation_modules' => 'Modules',
    'tr_melis_installer_navigation_final' => 'Création et Résultat',
    
    
    'tr_melis_installer_layout_introduction_welcome' => 'Bienvenue dans le Guide d\'Installation de Melis Platform',
    
    'tr_melis_installer_layout_introduction_welcome_subtext' => 'Cet installeur va vous guider à travers les étapes d\'installation de Melis',
    'tr_melis_installer_layout_introduction_welcome_sysconfig' => 'CONFIGURATION SYSTEME',
    'tr_melis_installer_layout_introduction_welcome_dbcon' => 'CONNECTION A LA BASE DE DONNEES',
    'tr_melis_installer_layout_introduction_welcome_platforminit' => 'INITIALISATION DE MELIS PLATFORM',
    'tr_melis_installer_layout_introduction_welcome_start_button' => 'Commençons',
    'tr_melis_installer_layout_introduction_welcome_view_online_doc' => 'Voir la Documentation en Ligne',
    'tr_melis_installer_layout_introduction_welcome_php_version_error' => 'Impossible de procéder à l\'installation, la version PHP minimum requise est <strong>%s</strong>, version actualle installée <strong>%s</strong>',
    
    'tr_melis_installer_layout_sys_req' => 'Etape 1 : Configuration Requise',
    'tr_melis_installer_layout_sys_req_subtext' => 'Regardons vos propriétés et variables PHP installées',
    'tr_melis_installer_layout_sys_req_check_php_ext' => 'Vérification des Extensions PHP',
    'tr_melis_installer_layout_sys_req_check_php_env' => 'Verification des Variables de l\'Environnement PHP',
    
    'tr_melis_installer_layout_vhost' => 'Etape 1.1 : Configuration Vhost',
    'tr_melis_installer_layout_vhost_subtext' => 'Analyse de la configuration de votre hôte virtuel',

    'tr_melis_installer_layout_fsrights' => 'Etape 1.2 : Droits des Fichiers Systèmes',
    'tr_melis_installer_layout_fsrights_subtext' => 'Regardons vos droits de directory', // check with Sylvain
    
    'tr_melis_installer_layout_env' => 'Etape 1.3 : Environnements',
    'tr_melis_installer_layout_env_subtext' => 'Vous pouvez rajouter ici plusieurs environnements, mais vous ne pouvez pas supprimer l\'environnement actuel ayant été configuré sur votre hôte virtuel',
    'tr_melis_installer_layout_env_default_env' => 'Environnement par défaut',
    'tr_melis_installer_Layout_env_env_name' => 'Nom de l\'Environnement',
    'tr_melis_installer_layout_env_back_office_domain' => 'Domaine du Back Office',
    'tr_melis_installer_layout_env_add_env' => 'Ajouter un Environnement',
    'tr_melis_installer_layout_env_remove' => 'Supprimer',
    
    'tr_melis_installer_layout_dbcon' => 'Etape 2 : Connection à la Base de Données',
    'tr_melis_installer_layout_dbcon_subtext' => 'Configurons votre base de données, assurez-vous d\'utiliser les bonnes information pour se connecter à votre serveur MySql',
    'tr_melis_installer_layout_dbcon_db_details' => 'Details de la Base de Données',
    'tr_melis_installer_layout_dbcon_form_host' => 'Hôte',
    'tr_melis_installer_layout_dbcon_form_db' => 'Base de Données',
    'tr_melis_installer_layout_dbcon_form_user' => 'Nom d\'Utilisateur',
    'tr_melis_installer_layout_dbcon_form_pass' => 'Mot de Passe',
    'tr_melis_installer_layout_dbcon_form_test' => 'Test de la Connection à la Base de Données',
    'tr_melis_installer_layout_dbcon_promp_title' => 'Résultat du Test de la Connection à la Base de Données',
    'tr_melis_installer_layout_dbcon_promp_content' => 'Echec du test de la connection à votre base de données', // need to see with Mickael
    'tr_melis_installer_dbcon_form_host_fail' => 'Impossible de se connecter à l\'hôte',
    'tr_melis_installer_dbcon_form_db_empty' => 'Veuillez saisir le nom de la base de données où vous souhaitez installer Melis',
    'tr_melis_installer_dbcon_form_db_fail' => 'Erreur de connexion à la base de données : le système n\'a pas pu joindre la base que vous avez renseigné',
    'tr_melis_installer_dbcon_form_user_fail' => 'Veuillez vous assurer que votre nom d\'utilisateur de la base de données est correct',
    'tr_melis_installer_dbcon_form_pass_fail' => 'Veuillez vous assurer que votre mot de passe de la base de données est correct',
    
    'tr_melis_installer_layout_modules' => 'Etape 2.1 : Installer les Modules',
    'tr_melis_installer_layout_modules_subtext' => 'Choisissez les modules additionnels que vous voulez installer et activer tout de suite.',
    'tr_melis_installer_layout_module_available' => 'Modules Disponibles',
    'tr_melis_installer_layout_module_select_all' => 'Sélectionner tout',
    
    
    'tr_melis_installer_step_1_0_extension_not_loaded' => 'L\'extension "%s" n\'est pas chargée',
    'tr_melis_installer_step_1_0_php_variable_not_set' => 'La variable PHP "%s" n\'est pas définie',
    'tr_melis_installer_step_1_0_php_requied_variables_empty' => 'La variable PHP requise n\'est pas définie',
    'tr_melis_installer_step_1_1_platform' => 'Plateforme de l\'Environnement',
    'tr_melis_installer_step_1_1_no_paltform_declared' => 'Pas de plateforme déclarée',
    'tr_melis_installer_step_1_1_alias_match_failed' => 'AliasMatch sur "%s" ne fonctionne pas',
    'tr_melis_installer_step_1_1_alias_match_success' => 'L\'hôte virtuel AliasMatch fonctionne sur "%s"',
    'tr_melis_installer_step_1_2_dir_not_writable' => '"%s" n\'est pas inscriptible',
    'tr_melis_installer_step_1_2_dir_writable' => '"%s" est inscriptible',
    
    // WEBSITE CONFIGURATION
    'tr_melis_installer_web_config' => 'Etape 3.1: Configuration du site',
    'tr_melis_installer_web_config_subtext' => 'Créons votre premier site ou vous pouvez utiliser le site de démo.',
    'tr_melis_installer_web_config_option' => 'Option du site',
    'tr_melis_installer_web_config_option_none' => 'Qucune',
    'tr_melis_installer_web_config_option_new_site' => 'Créer un nouveau site web',
    'tr_melis_installer_web_config_option_use' => 'Utiliser',
    'tr_melis_installer_web_config_option_use_empty' => 'Veuillez sélectionner une option de site',
    'tr_melis_installer_web_config_create_web' => 'Créer un site',
    'tr_melis_installer_web_config_create_web_sel_language' => 'Langage du site',
    'tr_melis_installer_web_config_empty_vhost_module_name' => 'Le nom du module (MELIS_MODULE) doit être défini avant de procéder à l\'installation',
    'tr_melis_installer_web_config_invalid_vhost_module_name' => 'Le nom du module (MELIS_MODULE) dans le vhost du server est invalide, seuls les caractères alphanumériques and l\'underscore sont autorisés dans le nom du module',
    
    // STEP 3: Platform Initialization
    'tr_melis_installer_platform_modal_title' => 'Melis Platform Initialization',
    'tr_melis_installer_platform_modal_content' => 'Please check details below: ',
    
    // STEP 3: Platform Initialization
    'tr_melis_installer_platform_modal_title' => 'Melis Platform Initialization',
    'tr_melis_installer_platform_modal_content' => 'Please check details below: ',
    
    // MELIS CONFIGURATION
    'tr_melis_installer_melis_config' => 'Etape 3.2 Configuration de Melis',
    'tr_melis_installer_melis_config_subtext' => 'Créons un compte administrateur.',
    'tr_melis_installer_melis_config_create_admin' => 'Créer un utilisateur administrateur',
    
    // MELIS MODULES
    'tr_melis_installer_melis_modules' => 'Etape 3.3: Modules',
    'tr_melis_installer_melis_modules_subtext' => 'Choisissez les modules additionnels que vous souhaitez installer et activer.',
    'tr_melis_installer_melis_modules_available' => 'Modules disponibles',
    'tr_melis_installer_melis_modules_select_all' => 'Tout sélectionner',
    
    // NEW USER FORM
    'tr_melis_installer_new_user_login' => 'Login', 
    'tr_melis_installer_new_user_email' => 'Email',
    'tr_melis_installer_new_user_password' => 'Mot de Passe',
    'tr_Melis_installer_new_user_confirm_password' => 'Confirmer le Mot de Passe',
    'tr_melis_installer_new_user_first_name' => 'Prénom',
    'tr_melis_installer_new_user_last_name' => 'Nom',
    
    'tr_melis_installer_new_user_login_empty' => 'Veuillez saisir votre login',
    'tr_melis_installer_new_user_login_invalid' => 'Login saisi invalide',
    'tr_melis_installer_new_user_login_max' => 'Valeur du login trop longue',
    'tr_melis_installer_new_user_email_empty' => 'Veuillez saisir votre email',
    'tr_melis_installer_new_user_email_invalid' => 'Adresse email invalide',
    
    'tr_melis_installer_new_user_pass_empty' => 'Veuillez saisir votre mot de passe',
    'tr_melis_installer_new_user_pass_max' => 'Mot de passe trop long',
    'tr_melis_installer_new_user_pass_short' => 'Mot de passe trop faible, il doit être de plus de 8 caractères',
    'tr_melis_installer_new_user_pass_invalid' => 'Le mot de passe doit comprendre au moins 1 lettre et 1 chiffre',
    'tr_melis_installer_new_user_pass_no_match' => 'Le mot de passe ne correspond pas',
    
    'tr_melis_installer_new_user_first_name_empty' => 'Veuillez saisir votre prénom',
    'tr_melis_installer_new_user_first_name_long' => 'Prénom trop long',
    'tr_melis_installer_new_user_first_name_invalid' => 'Prénom donné invalidé',
    
    'tr_melis_installer_new_user_last_name_empty' => 'Veuillez saisir votre nom',
    'tr_melis_installer_new_user_last_name_long' => 'Nom trop long',
    'tr_melis_installer_new_user_last_name_invalid' => 'Nom donné invalidé',
    
    // Website Language Form
    'tr_melis_installer_web_form_lang' => 'Langage',
    
    // Website Form
    'tr_melis_installer_web_form_name' => 'Nom du Site Internet',
    'tr_melis_installer_web_form_name_long' => 'Nom du Site Internet trop long',
    'tr_melis_installer_web_form_name_empty' => 'Veuillez saisir le nom du site internet',
    
    'tr_melis_installer_web_form_module' => 'Nom du Module (variable d\'environnement MELIS_MODULE)',
    'tr_melis_installer_web_form_module_label' => 'Nom du Module',
    'tr_melis_installer_web_form_module_long' => 'Nom du module du site internet trop long',
    'tr_melis_installer_web_form_module_empty' => 'Veuillez saisir le nom du module',
    'tr_melis_installer_web_form_module_exists' => 'Le module "%s" existe déjà, veuillez supprimer le module existant dans le module MelisSite',
    
    'tr_melis_installer_failed_table_install' => 'Echec de l\'installation de la table "%s", veuillez vérifier la requête et réessayez',
    
    'tr_melis_installer_creation_result' => 'Création & Résultat',
    'tr_melis_installer_creation_result_subtext' => 'Ceci installera toutes les informations fournies',
    
    'tr_melis_installer_common_next' => 'Suivant',
    'tr_melis_installer_common_finish' => 'Terminer',
    'tr_melis_installer_common_choose' => 'Choisir',
    
    'tr_melis_installer_common_finish_error' =>  'Il y a eu un problème lors de l\'étape finale, si vous souhaitez recommencer, rafraichissez la page et réessayez',
    'tr_melis_installer_common_installing' => 'Installation...',
    
);