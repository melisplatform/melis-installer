<?php

namespace MelisDemoCms;

use MelisMarketPlace\Support\MelisMarketPlace as MarketPlace;
use MelisMarketPlace\Support\MelisMarketPlaceCmsTables as Melis;
use MelisMarketPlace\Support\MelisMarketPlaceSiteInstall as Site;

return [
    'plugins' => [
        __NAMESPACE__ => [
            Site::DOWNLOAD => [
                Site::CONFIG => [
                    'id' => 'id_' . __NAMESPACE__,
                    Melis::CMS_TOTAL_PAGE => 10,
                ],
                MarketPlace::FORM => [
                    'melis_demo_cms_setup' => [
                        'forms' => [
                            'melis_demo_cms_setup_download_form' => [
                                'attributes' => [
                                    'name' => 'melis_demo_cms_setup_download_form',
                                    'id' => 'melis_demo_cms_setup_download_form',
                                    'method' => 'POST',
                                    'action' => '',
                                ],
                                'hydrator' => \Zend\Stdlib\Hydrator\ArraySerializable::class,
                                'elements' => [
                                    [
                                        'spec' => [
                                            'name' => 'scheme',
                                            'type' => \Zend\Form\Element\Select::class,
                                            'options' => [
                                                'label' => 'tr_site_demo_cms_tool_site_scheme',
                                                'tooltip' => 'tr_site_demo_cms_tool_site_scheme tooltip',
                                                'value_options' => [
                                                    'http' => 'http://',
                                                    'https' => 'https://',
                                                ],
                                            ],
                                            'attributes' => [
                                                'id' => 'scheme',
                                                'value' => '',
                                                'required' => 'required',
                                                'text-required' => '*',
                                                'class' => 'form-control',

                                            ],
                                        ],
                                    ],
                                    [
                                        'spec' => [
                                            'name' => 'domain',
                                            'type' => \Zend\Form\Element\Text::class,
                                            'options' => [
                                                'label' => 'tr_site_demo_cms_tool_site_domain',
                                                'tooltip' => 'tr_site_demo_cms_tool_site_domain tooltip',
                                            ],
                                            'attributes' => [
                                                'id' => 'domain',
                                                'value' => '',
                                                'required' => 'required',
                                                'placeholder' => 'www.example.com',
                                                'class' => 'form-control',
                                                'text-required' => '*',
                                            ],
                                        ],
                                    ],
                                    [
                                        'spec' => [
                                            'name' => 'name',
                                            'type' => \Zend\Form\Element\Text::class,
                                            'options' => [
                                                'label' => 'tr_site_demo_cms_name',
                                                'tooltip' => 'tr_site_demo_cms_name_tooltip',
                                            ],
                                            'attributes' => [
                                                'id' => 'name',
                                                'value' => '',
                                                'required' => 'required',
                                                'placeholder' => 'My Site Name',
                                                'class' => 'form-control',
                                                'text-required' => '*',
                                            ],
                                        ],
                                    ],
                                ], // end elements
                                'input_filter' => [
                                    'scheme' => [
                                        'name' => 'scheme',
                                        'required' => true,
                                        'validators' => [
                                            [
                                                'name' => 'InArray',
                                                'options' => [
                                                    'haystack' => ['http', 'https'],
                                                    'messages' => [
                                                        \Zend\Validator\InArray::NOT_IN_ARRAY => 'tr_site_demo_cms_tool_site_scheme_invalid_selection',
                                                    ],
                                                ],
                                            ],
                                            [
                                                'name' => 'NotEmpty',
                                                'options' => [
                                                    'messages' => [
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_scheme_error_empty',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'filters' => [
                                        ],
                                    ],
                                    'domain' => [
                                        'name' => 'domain',
                                        'required' => true,
                                        'validators' => [
                                            [
                                                'name' => 'StringLength',
                                                'options' => [
                                                    'encoding' => 'UTF-8',
                                                    'max' => 50,
                                                    'messages' => [
                                                        \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_tool_site_domain_error_long',
                                                    ],
                                                ],
                                            ],
                                            [
                                                'name' => 'NotEmpty',
                                                'options' => [
                                                    'messages' => [
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_domain_error_empty',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'filters' => [
                                            ['name' => 'StripTags'],
                                            ['name' => 'StringTrim'],
                                        ],
                                    ],
                                    'name' => [
                                        'name' => 'name',
                                        'required' => true,
                                        'validators' => [
                                            [
                                                'name' => 'StringLength',
                                                'options' => [
                                                    'encoding' => 'UTF-8',
                                                    'max' => 50,
                                                    'messages' => [
                                                        \Zend\Validator\StringLength::TOO_LONG => 'tr_site_demo_cms_tool_site_name_error_long',
                                                    ],
                                                ],
                                            ],
                                            [
                                                'name' => 'NotEmpty',
                                                'options' => [
                                                    'messages' => [
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_name_error_empty',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'filters' => [
                                            ['name' => 'StripTags'],
                                            ['name' => 'StringTrim'],
                                        ],
                                    ],
                                ], // end input_filter
                            ],
                        ],
                    ],
                ],
                Site::Data => []
            ],
        ],
    ],

];
