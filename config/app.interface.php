<?php 
return array(
    'plugins' => array(
        'melis_installer' => array(
            'conf' => array(
                
            ),
            'datas' => array(
                /**
                 * Melis Installer Step 3 : Wesite Configuration Option default options
                 * NOTE* : if key "None" and "NewSite" modify, 
                 * the intaller contoller, js, factory etc. should also modify
                 */
                'default_website_config_options' => array(
                    'None' => 'tr_melis_installer_web_config_option_none',
                    'NewSite' => 'tr_melis_installer_web_config_option_new_site',
                ),
                'module_exceptions' => array(
                    // modules that will not be displayed when selecting a module to be installed
                    'MelisCore',
                    'MelisFront',
                    'MelisEngine',
                    'MelisInstaller',
                    'MelisAssetManager',
                    'MelisDbDeploy',
                    'MelisComposerDeploy',
                    'MelisMarketPlace',
                ),
                'module_auto_install' => array(
                    // this configuration consists of the required modules that is need to run Melis Platform
                    'MelisMarketPlace' => 'melisplatform/melis-marketplace',
                    // added temporarily
                    'MelisEngine' => 'melisplatform/melis-engine',
                    'MelisFront' => 'melisplatform/melis-front'
                ),
                'module_default' => array(
                    'MelisAssetManager',
                    'MelisDbDeploy',
                    'MelisComposerDeploy',
                    'MelisCore',
                )
            ),
            'ressources' => array(
                'js' => array(
                    '/melis/MelisInstaller/Translation/getTranslation',
                    '/MelisInstaller/setup/js/jquery.min.js',
                    '/MelisInstaller/setup/js/jquery.validate.js',
                    '/MelisInstaller/setup/js/bootstrap.min.js',
                    '/MelisInstaller/setup/js/owl.carousel.min.js',
                    '/MelisInstaller/setup/js/bootstrap-switch.js',
                    '/MelisInstaller/setup/js/bootstrap-switch.init.js',
                    '/MelisInstaller/setup/js/setup.js',
                    '/MelisInstaller/setup/core/js/melisHelper.js',
                    '/MelisInstaller/setup/core/js/melisCoreTool.js',
                ),
                'css' => array(
                    '/MelisInstaller/setup/css/fontawesome/f-assets/f-css/font-awesome.min.css',
                    '/MelisInstaller/setup/css/module.admin.page.index.min.css',
                    '/MelisInstaller/setup/css/bootstrap.min.css',
                    '/MelisInstaller/setup/css/owl-carousel/owl.carousel.min.css',
                    '/MelisInstaller/setup/css/owl-carousel/owl.theme.default.min.css',
                    '/MelisInstaller/setup/css/bootstrap-switch.css',
                    '/MelisInstaller/setup/core/css/styles.css',
                    '/MelisInstaller/setup/css/setup.css',
                ),
            ),
        ),
    ),
);