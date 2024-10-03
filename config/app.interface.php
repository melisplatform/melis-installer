<?php
return [
    'plugins' => [
        'meliscore' => [
            'ressources' => [
                'css' => [
                    '/MelisInstaller/setup/css/OpenSans.css',
                    '/MelisInstaller/setup/css/Roboto.css',
                    '/MelisInstaller/setup/css/Monseratt.css',
                    '/MelisInstaller/setup/css/fontawesome/f-assets/f-css/font-awesome.min.css',
                    '/MelisInstaller/setup/css/module.admin.page.index.min.css',
                    '/MelisInstaller/setup/css/bootstrap.min.css',
                    '/MelisInstaller/setup/css/owl-carousel/owl.carousel.min.css',
                    '/MelisInstaller/setup/css/owl-carousel/owl.theme.default.min.css',
                    '/MelisInstaller/setup/css/bootstrap-switch.css',
                    '/MelisInstaller/setup/core/css/styles.css',
                    '/MelisInstaller/setup/css/setup.css',
                ],
                'js' => [
                    '/melis/MelisInstaller/Translation/getTranslation',
                    '/MelisInstaller/setup/js/jquery.min.js',
                    '/MelisInstaller/setup/js/jquery-migrate.min.js',
                    '/MelisInstaller/setup/js/jquery.validate.js',
                    '/MelisInstaller/setup/js/popper.min.js',
                    '/MelisInstaller/setup/js/bootstrap.min.js',
                    '/MelisInstaller/setup/js/owl.carousel.min.js',
                    '/MelisInstaller/setup/js/bootstrap-switch.js',
                    '/MelisInstaller/setup/js/bootstrap-switch.init.js',
                    '/MelisInstaller/setup/js/setup.js',
                    '/MelisInstaller/setup/core/js/melisHelper.js',
                    '/MelisInstaller/setup/core/js/melisCoreTool.js',
                ],
                /**
                 * the "build" configuration compiles all assets into one file to make
                 * lesser requests
                 */
                'build' => [
                    // set to "true" if you want to use the build assets
                    'use_build_assets' => true,

                    // path to where the build CSS and JS are located
                    'css_build_path' => 'public/assets/css/',
                    'js_build_path' => 'public/assets/js/',

                    // lists of assets that will be loaded in the layout
                    'css' => [
                        '/MelisInstaller/bundle/css/bundle.css',

                    ],
                    'js' => [
                        '/melis/MelisInstaller/Translation/getTranslation',
                        '/MelisInstaller/bundle/js/bundle.js',
                    ],
                ],
            ],
        ],
        'melis_installer' => [
            'conf' => [

            ],
            'datas' => [
                /**
                 * Melis Installer Step 3 : Wesite Configuration Option default options
                 * NOTE* : if key "None" and "NewSite" modify,
                 * the intaller contoller, js, factory etc. should also modify
                 */
                'default_website_config_options' => [
                    'None' => 'tr_melis_installer_web_config_option_none',
                    'NewSite' => 'tr_melis_installer_web_config_option_new_site',
                ],
                'marketplace_url' => 'http://marketplace.melisplatform.com',
                'module_exceptions' => [
                    // modules that will not be displayed when selecting a module to be installed
                    'MelisCore',
                    'MelisInstaller',
                    'MelisAssetManager',
                    'MelisDbDeploy',
                    'MelisComposerDeploy',
                    'MelisMarketPlace',
                    'MelisPlatformFrameworks',
                    'MelisPlatformFrameworkSymfonyDemoTool',
                    'MelisPlatformFrameworkLaravelDemoTool',
                    'MelisPlatformFrameworkLumenDemoTool',
                    'MelisPlatformFrameworkSilexDemoTool',
                    'MelisPlatformFrameworkSilex',
                    'MelisPlatformFrameworkLaravel',
                    'MelisPlatformFrameworkSymfony',
                    'MelisPlatformFrameworkLumen',
                    'MelisToolCreatorLaravel',
                    'MelisPlatformFrameworkLaravelToolCreator',
                    'MelisToolCreatorSymfony',
                    'MelisPlatformFrameworkSymfonyToolCreator',
                    'MelisToolCreatorLumen',
                    'MelisPlatformFrameworkLumenToolCreator',
                    'MelisToolCreatorSilex',
                    'MelisPlatformFrameworkSilexToolCreator',
                ],
                'module_auto_install' => [
                    // this configuration consists of the required modules that is needed to run Melis Platform
                ],
                'module_default' => [
                    'MelisAssetManager',
                    'MelisDbDeploy',
                    'MelisComposerDeploy',
                    'MelisCore',
                    'MelisMarketPlace',
                ],
                'default' => [
                    'errors' => array(
                        'error_reporting' => E_ALL & ~E_USER_DEPRECATED,
                        'display_errors' => 1,
                    ),
                ],
            ],
            'ressources' => [

            ],
        ],
    ],
];
