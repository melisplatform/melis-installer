<?php
return array(
    'plugins' => array(
        'melis_installer' => array(
            'forms' => array(
                'melis_installer_user_data' => array(
                    'attributes' => array(
                        'name' => 'frmuserdata',
                        'id'   => 'idfrmuserdata',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'login',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_new_user_login'  
                                ),
                                'attributes' => array(
                                    'id' => 'login',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_new_user_login',
                                )
                            )  
                        ),
                        array(
                            'spec' => array(
                                'name' => 'email',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_new_user_email'
                                ),
                                'attributes' => array(
                                    'id' => 'email',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_new_user_email',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'email',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_new_user_email'
                                ),
                                'attributes' => array(
                                    'id' => 'email',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_new_user_email',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'password',
                                'type' => 'Password',
                                'options' => array(
                                    'label' => 'tr_melis_installer_new_user_password',
                                ),
                                'attributes' => array(
                                    'id' => 'password',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_new_user_password',
                                    'class' => 'form-control',
                                ),
                            ),
                        ),
                        array(
                            'spec' => array(
                                'name' => 'confirmPassword',
                                'type' => 'Password',
                                'options' => array(
                                    'label' => 'tr_Melis_installer_new_user_confirm_password',
                                ),
                                'attributes' => array(
                                    'id' => 'confirmPassword',
                                    'value' => '',
                                    'placeholder' => 'tr_Melis_installer_new_user_confirm_password',
                                    'class' => 'form-control',
                                ),
                            ),
                        ),
                        array(
                            'spec' => array(
                                'name' => 'firstname',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_new_user_first_name'
                                ),
                                'attributes' => array(
                                    'id' => 'firstname',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_new_user_first_name',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'lastname',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_new_user_last_name'
                                ),
                                'attributes' => array(
                                    'id' => 'lastname',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_new_user_last_name',
                                )
                            )
                        ),
                    ), // end elements
                    'input_filter' => array(
                        'login' => array(
                            'name'     => 'login',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_new_user_login_max',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_new_user_login_empty',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'regex', false,
                                    'options' => array(
                                        'pattern' => '/^[A-Za-z][A-Za-z0-9]*$/',
                                        'messages' => array(\Zend\Validator\Regex::NOT_MATCH => 'tr_melis_installer_new_user_login_invalid'),
                                        'encoding' => 'UTF-8',
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'email' => array(
                            'name'     => 'email',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'EmailAddress',
                                    'options' => array(
                                        'domain'   => 'true',
                                        'hostname' => 'true',
                                        'mx'       => 'true',
                                        'deep'     => 'true',
                                        'message'  => 'tr_melis_installer_new_user_email_invalid',
                                    )
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_new_user_email_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'password' => array(
                            'name'     => 'password',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => '\MelisInstaller\Validator\MelisPasswordValidator',
                                    'options' => array(
                                        'min' => 8,
                                        'messages' => array(
                                            \MelisInstaller\Validator\MelisPasswordValidator::TOO_SHORT => 'tr_melis_installer_new_user_pass_short',
                                            \MelisInstaller\Validator\MelisPasswordValidator::NO_DIGIT => 'tr_melis_installer_new_user_pass_invalid',
                                            \MelisInstaller\Validator\MelisPasswordValidator::NO_LOWER => 'tr_melis_installer_new_user_pass_invalid',
                                        ),
                                    ),
                                ),
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_new_user_pass_max',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_new_user_pass_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'confirmPassword' => array(
                            'name'     => 'confirmPassword',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => '\MelisInstaller\Validator\MelisPasswordValidator',
                                    'options' => array(
                                        'min' => 8,
                                        'messages' => array(
                                            \MelisInstaller\Validator\MelisPasswordValidator::TOO_SHORT => 'tr_melis_installer_new_user_pass_short',
                                            \MelisInstaller\Validator\MelisPasswordValidator::NO_DIGIT => 'tr_melis_installer_new_user_pass_invalid',
                                            \MelisInstaller\Validator\MelisPasswordValidator::NO_LOWER => 'tr_melis_installer_new_user_pass_invalid',
                                        ),
                                    ),
                                ),
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_new_user_pass_max',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_new_user_pass_empty',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'Identical',
                                    'options' => array(
                                        'token' => 'password', 
                                        'messages' => array(
                                            \Zend\Validator\Identical::NOT_SAME => 'tr_melis_installer_new_user_pass_no_match',  
                                        ),
                                    ),
                                )
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'firstname' => array(
                            'name'     => 'firstname',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        //'min'      => 1,
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_new_user_first_name_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_new_user_first_name_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'lastname' => array(
                            'name'     => 'lastname',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        //'min'      => 1,
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_new_user_last_name_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_new_user_last_name_empty',
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
                ), // end melis_installer_user_data
                'melis_installer_web_lang' => array(
                    'attributes' => array(
                        'name' => 'frmweblang',
                        'id'   => 'idfrmweblang',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'language',
                                'type' => 'MelisInstallerLanguageSelect',
                                'options' => array(
                                    'label' => 'tr_melis_installer_web_form_lang',
                                ),
                                'attributes' => array(
                                    'id' => 'language',
                                ),
                            ),
                        ),
                    )
                ), // end melis_installer_web_lang
                'melis_installer_webconfig_option' => array(
                    'attributes' => array(
                        'name' => 'webOptionForm',
                        'id'   => 'webOptionForm',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'weboption',
                                'type' => 'MelisInstallerWebOptionSelect',
                                'options' => array(
                                    'label' => 'tr_melis_installer_web_config_option',
                                    'empty_option' => 'tr_melis_installer_common_choose',
                                    'disable_inarray_validator' => true,
                                ),
                                'attributes' => array(
                                    'id' => 'weboption',
                                ),
                            ),
                        ),
                    ),
                    'input_filter' => array(
                        'weboption' => array(
                            'name'     => 'weboption',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_web_config_option_use_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        )
                    )
                ),
                'melis_installer_webform' => array(
                    'attributes' => array(
                        'name' => 'frmwebform',
                        'id'   => 'idfrmwebform',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'website_name',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_web_form_name'
                                ),
                                'attributes' => array(
                                    'id' => 'website_name',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_web_form_name',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'website_module',
                                'type' => 'MelisText',
                                'options' => array(
                                    'label' => 'tr_melis_installer_web_form_module_label' 
                                ),
                                'attributes' => array(
                                    'id' => 'website_module',
                                    'value' => '',
                                    'placeholder' => 'tr_melis_installer_web_form_module',
                                    'readonly' => 'readonly'
                                )
                            )
                        ),
                    ),
                    'input_filter' => array(
                        'website_name' => array(
                            'name'     => 'website_name',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_web_form_name_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_web_form_name_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'website_module' => array(
                            'name'     => 'website_module',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        'max'      => 255,
                                        'messages' => array(
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_web_form_module_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_web_form_module_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);