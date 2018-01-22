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
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'sdom_scheme',
                                'type' => 'text',
                                'options' => array(
                                    'label' => 'tr_melis_installer_sdom_scheme',
                                    'tooltip' => 'tr_melis_installer_sdom_scheme_info',
                                ),
                                'attributes' => array(
                                    'id' => 'sdom_scheme',
                                    'value' => 'http',
                                    'placeholder' => 'http',
                                    'class' => 'form-control',
                                ),
                            ),
                        ),
                        array(
                            'spec' => array(
                                'name' => 'sdom_domain',
                                'type' => 'text',
                                'options' => array(
                                    'label' => 'tr_melis_installer_sdom_domain',
                                    'tooltip' => 'tr_melis_installer_sdom_domain_info',
                                ),
                                'attributes' => array(
                                    'id' => 'sdom_domain',
                                    'value' => 'sample.com',
                                    'placeholder' => 'sample.com',
                                    'class' => 'form-control',
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
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_scheme_install_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'sdom_domain' => array(
                            'name'     => 'sdom_domain',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_domain_install_sdom_domain_empty',
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