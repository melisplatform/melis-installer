<?php

return array(
    'MELIS Platform V2.0 Setup' => 'Installation MELIS Platform V2.0',
    
    'tr_melis_installer_navigation_introduction' => 'Introduction',
    'tr_melis_installer_navigation_sysconf' => 'Configuration système',
    'tr_melis_installer_navgitation_vhost' => 'Vhost',
    'tr_melis_installer_navigation_fs' => 'Droits des fichiers système',
    'tr_melis_installer_navigation_environments' => 'Environnements',
    'tr_melis_installer_navigation_dbcon' => 'Connection à la base de données',
    'tr_melis_installer_navigation_platform' => 'Initialisation de la plateforme',
    'tr_melis_installer_navigation_web_config' => 'Configuration du site',
    'tr_melis_installer_navigation_melis_config' => 'Configuration du compte administateur',
    'tr_melis_installer_navigation_modules' => 'Modules',
    'tr_melis_installer_navigation_final' => 'Installation',
    
    'tr_melis_installer_common_close' => 'Fermer',
    
    'tr_melis_installer_layout_introduction_welcome' => 'Bienvenue dans le guide d\'installation de Melis Platform',
    
    'tr_melis_installer_layout_introduction_welcome_subtext' => 'Cet installeur va vous guider à travers les étapes d\'installation de Melis',
    'tr_melis_installer_layout_introduction_welcome_sysconfig' => 'CONFIGURATION SYSTEME',
    'tr_melis_installer_layout_introduction_welcome_dbcon' => 'CONNECTION A LA BASE DE DONNEES',
    'tr_melis_installer_layout_introduction_welcome_platforminit' => 'INITIALISATION DE MELIS PLATFORM',
    'tr_melis_installer_layout_introduction_welcome_start_button' => 'Commençons',
    'tr_melis_installer_layout_introduction_welcome_view_online_doc' => 'Voir la documentation en ligne',
    'tr_melis_installer_layout_introduction_welcome_php_version_error' => 'Impossible de procéder à l\'installation, la version PHP minimum requise est <strong>%s</strong>, version actualle installée <strong>%s</strong>',
    
    'tr_melis_installer_layout_sys_req' => 'Etape 1 : Configuration requise',
    'tr_melis_installer_layout_sys_req_subtext' => 'Regardons vos propriétés et variables PHP installées',
    'tr_melis_installer_layout_sys_req_php_version_recommended' => 'Recommandé 5.5 à 7.1',
    'tr_melis_installer_layout_sys_req_check_php_ext' => 'Vérification des extensions PHP requises',
    'tr_melis_installer_layout_sys_req_check_php_env' => 'Verification des variables de l\'Environnement PHP',
    
    'tr_melis_installer_layout_vhost' => 'Etape 1.1 : Configuration vhost',
    'tr_melis_installer_layout_vhost_subtext' => 'Analyse de la configuration de votre hôte virtuel',

    'tr_melis_installer_layout_fsrights' => 'Etape 1.2 : Droits des fichiers systèmes',
    'tr_melis_installer_layout_fsrights_subtext' => 'Vérification des droits d\'écriture des répertoires.',
    
    'tr_melis_installer_layout_env' => 'Etape 1.3 : Environnements',
    'tr_melis_installer_layout_env_subtext' => 'En plus de votre environnement actuel ajoutez ici d\'autres environnements si vous le souhaitez.',
    'tr_melis_installer_layout_env_default_env' => 'Environnement par défaut',
    'tr_melis_installer_Layout_env_env_name' => 'Nom de l\'environnement',
    'tr_melis_installer_layout_env_back_office_domain' => 'Domaine du back office',
    'tr_melis_installer_layout_env_add_env' => 'Ajouter un environnement',
    'tr_melis_installer_layout_env_remove' => 'Supprimer',
    'tr_melis_installer_layout_env_advance_parameters' => 'Paramètres avancés',
    'tr_melis_installer_layout_env_send_email' => 'Envoi d\'email',
    'tr_melis_installer_layout_env_send_email_info' => 'Si votre plateforme ne peut pas envoyer d\'email mettez ce bouton sur &quot;désactivé&quot;',
    'tr_melis_installer_layout_env_send_email_enabled' => 'Activé',
    'tr_melis_installer_layout_env_send_email_disabled' => 'Désactivé',
    'tr_melis_installer_layout_env_error_reporting' => 'Affichage des erreurs (error_reporting)',
    'tr_melis_installer_layout_env_error_reporting_info' => 'Choisissez E_ALL pour afficher les erreurs ou désactivez avec l\'autre option',
    'tr_melis_installer_layout_env_error_reporting_all_except_deprecated' => 'Report all except user-generated warning messages (E_ALL & ~E_USER_DEPRECATED)',
    'tr_melis_installer_layout_env_error_reporting_all' => 'Afficher toutes les erreurs (E_ALL)',
    'tr_melis_installer_layout_env_error_reporting_off' => 'Désactiver l\'affichage des erreurs',
    
    'tr_melis_installer_layout_dbcon' => 'Etape 2 : Connection à la base de bonnées',
    'tr_melis_installer_layout_dbcon_subtext' => 'Configuration des informations d\'accès à la base de données.',
    'tr_melis_installer_layout_dbcon_db_details' => 'Details de la base de données',
    'tr_melis_installer_layout_dbcon_form_host' => 'Hôte',
    'tr_melis_installer_layout_dbcon_form_host_info' => 'ex: localhost',
    'tr_melis_installer_layout_dbcon_form_db' => 'Base de données',
    'tr_melis_installer_layout_dbcon_form_db_info' => 'ex: melis',
    'tr_melis_installer_layout_dbcon_form_user' => 'Nom d\'utilisateur',
    'tr_melis_installer_layout_dbcon_form_user_info' => 'ex: root',
    'tr_melis_installer_layout_dbcon_form_pass' => 'Mot de passe',
    'tr_melis_installer_layout_dbcon_form_pass_info' => 'ex: root',
    'tr_melis_installer_layout_dbcon_form_test' => 'Test de la connection à la base de données',
    'tr_melis_installer_layout_dbcon_form_testing' => 'Test de la connection à la base de donnée . . .',
    'tr_melis_installer_layout_dbcon_promp_title' => 'Résultat du test de la connection à la base de données',
    'tr_melis_installer_layout_dbcon_promp_content' => 'Echec du test de la connection à votre base de données', // need to see with Mickael
    'tr_melis_installer_layout_dbcon_collation_name' => 'Nom de la collation',
    'tr_melis_installer_layout_dbcon_collation_name_invalid' => 'Veuillez vous assurer que la collation de votre base de donnée est "utf8_general_ci"',
    'tr_melis_installer_dbcon_form_host_fail' => 'Impossible de se connecter à l\'hôte',
    'tr_melis_installer_dbcon_form_db_empty' => 'Veuillez saisir le nom de la base de données où vous souhaitez installer Melis',
    'tr_melis_installer_dbcon_form_db_fail' => 'Erreur de connexion à la base de données : le système n\'a pas pu joindre la base que vous avez renseigné',
    'tr_melis_installer_dbcon_form_user_fail' => 'Veuillez vous assurer que votre nom d\'utilisateur de la base de données est correct',
    'tr_melis_installer_dbcon_form_pass_fail' => 'Veuillez vous assurer que votre mot de passe de la base de données est correct',
    'tr_melis_installer_dbcon_form_user_empty' => 'Veuillez saisir le nom d\'utilisateur de votre base de données (ex : root)',
    
    'tr_melis_installer_layout_modules' => 'Etape 2.1 : Installer les modules',
    'tr_melis_installer_layout_modules_subtext' => 'Choisissez les modules additionnels que vous voulez installer et activer tout de suite.',
    'tr_melis_installer_layout_module_available' => 'Modules disponibles',
    'tr_melis_installer_layout_module_select_all' => 'Sélectionner tout',
    
    
    'tr_melis_installer_step_1_0_extension_not_loaded' => 'L\'extension "%s" n\'est pas chargée',
    'tr_melis_installer_step_1_0_php_variable_not_set' => 'La variable PHP "%s" n\'est pas définie',
    'tr_melis_installer_step_1_0_php_requied_variables_empty' => 'La variable PHP requise n\'est pas définie',
    'tr_melis_installer_step_1_1_platform' => 'Plateforme de l\'environnement',
    'tr_melis_installer_step_1_1_module' => 'Module du site',
    'tr_melis_installer_step_1_1_no_paltform_declared' => 'Vous devez déclarer dans votre vhost une variable d\'environnement MELIS_PLATFORM',
    'tr_melis_installer_step_1_1_no_module_declared' => 'Vous devez déclarer dans votre vhost une variable d\'environnement MELIS_MODULE correspondant au nom de votre module site',
    'tr_melis_installer_step_1_1_example_vhost_config' => 'Exemple de configuration vhost : <br> <br>SetEnv MELIS_PLATFORM "development" <br>SetEnv MELIS_MODULE "MySiteTest"',
    'tr_melis_installer_step_1_1_alias_match_failed' => 'AliasMatch sur "%s" ne fonctionne pas',
    'tr_melis_installer_step_1_1_alias_match_success' => 'L\'hôte virtuel AliasMatch fonctionne sur "%s"',
    'tr_melis_installer_step_1_2_dir_not_writable' => '"%s" n\'est pas inscriptible',
    'tr_melis_installer_step_1_2_dir_writable' => '"%s" est inscriptible',
    
    // WEBSITE CONFIGURATION
    'tr_melis_installer_web_config' => 'Etape 3.1: Configuration du site',
    'tr_melis_installer_web_config_subtext' => 'Création du premier site.',
    'tr_melis_installer_web_config_option' => 'Option du site',
    'tr_melis_installer_web_config_option_none' => 'Aucune',
    'tr_melis_installer_web_config_option_new_site' => 'Créer un nouveau site web',
    'tr_melis_installer_web_config_option_use' => 'Utiliser',
    'tr_melis_installer_web_config_option_use_empty' => 'Veuillez sélectionner une option de site',
    'tr_melis_installer_web_config_create_web' => 'Créer un site',
    'tr_melis_installer_web_config_create_web_sel_language' => 'Langage du site',
    'tr_melis_installer_web_config_empty_vhost_module_name' => 'Le nom du module (MELIS_MODULE) doit être défini avant de procéder à l\'installation',
    'tr_melis_installer_web_config_invalid_vhost_module_name' => 'Le nom du module (MELIS_MODULE) dans le vhost du server est invalide, seuls les caractères alphanumériques and l\'underscore sont autorisés dans le nom du module',

    'tr_melis_installer_option_none'         => '<strong>Aucun site</strong> - Cette option ne créera rien (aucun module, aucun dossier, aucune page) et vous devrez construire votre site complètement. <br>Recommandé aux initiés ou pour ceux ayant des besoins spéciaux.',
    'tr_melis_installer_option_new_website'  => '<strong>Créer un nouveau site web</strong> - Cette option créera une base de site incluant un module avec sa configuration, son Module.php, un layout, un controller, une première action/vue et une première page dans l\'arborescence du site. <br>Recommandé pour ceux connaissant déjà la structure d\'un site Melis Platform.',
    'tr_melis_installer_option_use_demo_cms' => '<strong>Utiliser le site démo</strong> - Cette option importera le site de test Melis Demo CMS, afin de pouvoir l\'utiliser en tant que tutoriel sur comment construire un site avec Melis Platform. <br>Recommandé pour ceux découvrant Melis Platform',
    
    // STEP 3: Platform Initialization
    'tr_melis_installer_platform_modal_title' => 'Melis Platform initialisation',
    'tr_melis_installer_platform_modal_content' => 'Une erreur est survenue :',
    
    // MELIS CONFIGURATION
    'tr_melis_installer_melis_config' => 'Etape 3.2 Configuration du compte administateur',
    'tr_melis_installer_melis_config_subtext' => 'Création d\'un compte administateur.',
    'tr_melis_installer_melis_config_create_admin' => 'Créer un utilisateur administrateur',
    
    // MELIS MODULES
    'tr_melis_installer_melis_modules' => 'Etape 3.3: Modules',
    'tr_melis_installer_melis_modules_subtext' => 'Choix des modules additionnels à installer et activer. <br>Si vous avez choisi d\'installer le site démo certains modules ne peuvent pas être désactivés car ils sont nécessaires au bon fonctionnement du site démo.',
    'tr_melis_installer_melis_modules_available' => 'Modules disponibles',
    'tr_melis_installer_melis_modules_select_all' => 'Tout sélectionner',
    
    'tr_melis_installer_melis_modules_MelisCalendar' => 'Créez et gérez des évènements',
    'tr_melis_installer_melis_modules_MelisCmsNews' => 'Créez et gérez des actualités pour vos sites',
    'tr_melis_installer_melis_modules_MelisCmsPageHistoric' => 'Visualisez l\'historique des modifications de vos pages ',
    'tr_melis_installer_melis_modules_MelisCmsProspects' => 'Gérez vos formulaires de contact et les prospects associés',
    'tr_melis_installer_melis_modules_MelisDesign' => 'Exploitez des designs prédéfinis à des fins de développement',
    'tr_melis_installer_melis_modules_MelisCmsSlider' => 'Créez et gérez des sliders pour vos sites',
    'tr_melis_installer_melis_modules_MelisCommerce' => 'Créez et gérez la brique commerce de Melis Platform',
    'tr_melis_installer_melis_modules_MelisSmallBusiness' => 'Ajoutez un workflow de validation des pages, un versioning de pages et une media library avancée',
    'tr_melis_installer_melis_modules_MelisZendServer' => 'Gérez du cache de page via zendserver',
    'tr_melis_installer_melis_modules_MelisCmsPageAnalytics' => 'Gérez l\'analytics de vos sites',
    'tr_melis_installer_melis_modules_MelisMessenger' => 'Echangez des messages avec les autres utilisateurs',
    'tr_melis_installer_melis_modules_MelisMarketPlace' => 'Gérez les modules de la plateforme',
    
    // NEW USER FORM
    'tr_melis_installer_new_user_login' => 'Login', 
    'tr_melis_installer_new_user_login_info' => 'Nom d&#39;utilisateur pour se connecter à Melis Platform', 
    'tr_melis_installer_new_user_email' => 'Email',
    'tr_melis_installer_new_user_email_info' => 'Adresse email de l&#39;utilisateur',
    'tr_melis_installer_new_user_password' => 'Mot de passe',
    'tr_melis_installer_new_user_password_info' => 'Mot de passe utilisateur (doit être de 8 caractères minimum et contenir au moins une lettre et un chiffre)',
    'tr_Melis_installer_new_user_confirm_password' => 'Mot de passe (2)',
    'tr_Melis_installer_new_user_confirm_password_info' => 'Confirmation du mot de passe de l&#39;utilisateur',
    'tr_melis_installer_new_user_first_name' => 'Prénom',
    'tr_melis_installer_new_user_first_name_info' => 'Prénom de l&#39;utilisateur',
    'tr_melis_installer_new_user_last_name' => 'Nom',
    'tr_melis_installer_new_user_last_name_info' => 'Nom de famille de l&#39;utilisateur',
    
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
    'tr_melis_installer_web_form_name' => 'Nom du site Internet',
    'tr_melis_installer_web_form_name_long' => 'Nom du Site Internet trop long',
    'tr_melis_installer_web_form_name_empty' => 'Veuillez saisir le nom du site Internet',
    'tr_melis_installer_web_form_name_invalid' => 'Nom de site invalide, alphanumerique et underscore sont les seul caractères autorisés',
    
    'tr_melis_installer_web_form_module' => 'Nom du module (variable d\'environnement MELIS_MODULE)',
    'tr_melis_installer_web_form_module_label' => 'Nom du module',
    'tr_melis_installer_web_form_module_long' => 'Nom du module du site internet trop long',
    'tr_melis_installer_web_form_module_empty' => 'Veuillez saisir le nom du module',
    'tr_melis_installer_web_form_module_exists' => 'Le module "%s" existe déjà, veuillez supprimer le module existant dans le module MelisSite',
    
    'tr_melis_installer_failed_table_install' => 'Echec de l\'installation de la table "%s", veuillez vérifier la requête et réessayez',
    
    'tr_melis_installer_creation_result' => 'Installation',
    'tr_melis_installer_creation_result_subtext' => 'Valider et commencer l\'installation de Melis Platform avec les informations fournies.',
    
    'tr_melis_installer_common_next' => 'Suivant',
    'tr_melis_installer_common_finish' => 'Installer',
    'tr_melis_installer_common_choose' => 'Choisir',
    'tr_melis_installer_common_status' => 'Statut',
    
    'tr_melis_installer_common_finish_error' =>  'Il y a eu un problème lors de l\'étape finale, si vous souhaitez recommencer, rafraichissez la page et réessayez',
    'tr_melis_installer_common_installing' => 'Installation . . .',
    'tr_melis_installer_common_finalizing' => 'Finalisation du setup . . .',

    'melis_installer_common_selection' => 'Sélection',
    'melis_installer_common_download' => 'Téléchargement',
    'melis_installer_common_configuration' => 'Configuration',
    'melis_installer_common_confirmation' => 'Confirmation',

    'melis_installer_module_active' => 'Activated %s',

    'melis_installer_configuration_subtitle' => 'Please fill the following configuration of the modules',
    'melis_installer_language_header_fr' => 'Français',
    'melis_installer_language_header_en' => 'English',
    'melis_installer_selection_heading'  => 'Modules à installer',
    'melis_installer_site_to_install_heading' => 'Site à installer (optionnel)',
    'melis_installer_site_to_install_desc' => 'Vous pouvez choisir d\'installer un site lors de l\'installation du module MelisCms',


    'melis_installer_download_module_title' => 'Modules / Téléchargement',
    'melis_installer_download_module_subtitle' => 'Téléchargement des modules, ceci peut prendre un peu de temps',
    'melis_installer_common_downloading' => 'Téléchargement ...',
    
    'melis_installer_common_checking' => 'Checking...',


);