<?php
return array(
    'plugins' => array(
        'melis_demo_cms_setup' => array(
            'forms' => array(
                'melis_installer_demo_cms' => array(
                    'attributes' => array(
                        'name' => 'form_melis_installer_demo_cms',
                        'id'   => 'id_form_melis_installer_demo_cms',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Laminas\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'sdom_scheme',
                                'type' => 'Laminas\Form\Element\Select',
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
                                    'label' => 'tr_meliscms_tool_site_site_label',
                                    'tooltip' => 'tr_meliscms_tool_site_site_label tooltip',
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
                                            \Laminas\Validator\InArray::NOT_IN_ARRAY => 'tr_melis_installer_tool_site_scheme_invalid_selection',
                                        ),
                                    )
                                ),
                                array(
                                    'name'    => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_tool_site_scheme_error_empty',
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
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melis_installer_tool_site_domain_error_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_domain_error_empty',
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
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_meliscms_tool_site_site_label_error_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_tool_site_site_label_error_empty',
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
                ), // end melis_installer_platform_id
            ),
        ),
    ),
);