<?php

namespace MelisDemoCms;

use MelisMarketPlace\Support\MelisMarketPlace as MarketPlace;
use MelisMarketPlace\Support\MelisMarketPlaceCmsTables as Melis;
use MelisMarketPlace\Support\MelisMarketPlaceSiteInstall as Site;

return [
    'plugins' => [
        'MelisDemoCms' => [
            'setup' => [
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
                                    'elements'  => array(
                                        array(
                                            'spec' => array(
                                                'name' => 'sdom_scheme',
                                                'type' => 'Zend\Form\Element\Select',
                                                'options' => array(
                                                    'label' => 'tr_melis_installer_tool_site_scheme',
                                                    'tooltip' => 'tr_melis_installer_tool_site_scheme tooltip',
                                                    'value_options' => array(
                                                        'http' => 'http://',
                                                        'https' => 'https://',
                                                    ),
                                                ),
                                                'attributes' => array(
                                                    'id' => 'id_sdom_scheme',
                                                    'value' => '',
                                                    'required' => 'required',
                                                    'text-required' => '*',
                                                    'class' => 'form-control',
                                                ),
                                            ),
                                        ),
                                        array(
                                            'spec' => array(
                                                'name' => 'sdom_domain',
                                                'type' => 'text',
                                                'options' => array(
                                                    'label' => 'tr_melis_installer_tool_site_domain',
                                                    'tooltip' => 'tr_melis_installer_tool_site_domain tooltip',
                                                ),
                                                'attributes' => array(
                                                    'id' => 'id_sdom_domain',
                                                    'value' => '',
                                                    'required' => 'required',
                                                    'placeholder' => 'www.sample.com',
                                                    'class' => 'form-control',
                                                    'text-required' => '*',
                                                ),
                                            ),
                                        ),
                                        array(
                                            'spec' => array(
                                                'name' => 'site_label',
                                                'type' => 'text',
                                                'options' => array(
                                                    'label' => 'tr_melis_installer_tool_site_site_label',
                                                    'tooltip' => 'tr_melis_installer_tool_site_site_label tooltip',
                                                ),
                                                'attributes' => array(
                                                    'id' => 'site_label',
                                                    'value' => '',
                                                    'required' => 'required',
                                                    'class' => 'form-control',
                                                    'text-required' => '*',
                                                ),
                                            ),
                                        ),
                                    ), // end elements
                                    'input_filter' => array(
                                        'sdom_scheme' => array(
                                            'name'     => 'sdom_scheme',
                                            'required' => true,
                                            'validators' => array(
                                                array(
                                                    'name'    => 'InArray',
                                                    'options' => array(
                                                        'haystack' => array('http', 'https'),
                                                        'messages' => array(
                                                            \Zend\Validator\InArray::NOT_IN_ARRAY => 'tr_melis_installer_tool_site_scheme_invalid_selection',
                                                        ),
                                                    )
                                                ),
                                                array(
                                                    'name'    => 'NotEmpty',
                                                    'options' => array(
                                                        'messages' => array(
                                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_tool_site_scheme_error_empty',
                                                        ),
                                                    ),
                                                ),
                                            ),
                                            'filters'  => array(
                                            ),
                                        ),
                                        'sdom_domain' => array(
                                            'name'     => 'sdom_domain',
                                            'required' => true,
                                            'validators' => array(
                                                array(
                                                    'name'    => 'StringLength',
                                                    'options' => array(
                                                        'encoding' => 'UTF-8',
                                                        'max'      => 50,
                                                        'messages' => array(
                                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_tool_site_domain_error_long',
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'name' => 'NotEmpty',
                                                    'options' => array(
                                                        'messages' => array(
                                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_domain_error_empty',
                                                        ),
                                                    ),
                                                ),
                                            ),
                                            'filters'  => array(
                                                array('name' => 'StripTags'),
                                                array('name' => 'StringTrim'),
                                            ),
                                        ),
                                        'site_label' => array(
                                            'name'     => 'site_label',
                                            'required' => true,
                                            'validators' => array(
                                                array(
                                                    'name'    => 'StringLength',
                                                    'options' => array(
                                                        'encoding' => 'UTF-8',
                                                        'max'      => 100,
                                                        'messages' => array(
                                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_tool_site_site_label_error_long',
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'name' => 'NotEmpty',
                                                    'options' => array(
                                                        'messages' => array(
                                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_site_label_error_empty',
                                                        ),
                                                    ),
                                                ),
                                            ),
                                            'filters'  => array(
                                                array('name' => 'StripTags'),
                                                array('name' => 'StringTrim'),
                                            ),
                                        ),
                                    ), // end input_filter
                                ],
                            ],
                        ],
                    ],
                    Site::DATA => []
                ],
            ],
        ],
    ],

];