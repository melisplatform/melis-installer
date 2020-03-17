<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)b
 *
 */

namespace MelisInstaller\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Session\Container;
use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;

class TranslationController  extends AbstractActionController
{

    public function getTranslationAction()
    {
        $translator = $this->getServiceLocator()->get('translator');
        $translations = (array) $translator->getAllMessages();
        $jsTranslations = null;

        $container  = new Container('meliscore');
        $locale     = 'en_EN';
        if(isset($container['melis-lang-locale'])) {
            $locale = $container['melis-lang-locale'];
        }

        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'text/javascript; charset=utf-8');
        foreach($translations as $transKey => $transValue)
        {
            $transKey        = str_replace("'", '', $transKey);
            $transValue      = str_replace("'", "\'", $transValue);
            $jsTranslations .= "translators['".$transKey."'] = '" . $transValue . "';". PHP_EOL;
        }

        $scriptContent = '';
        $scriptContent .= 'var melisLangId = "' . $locale . '";' . PHP_EOL;
        $scriptContent .= 'var translators = new Object();'. PHP_EOL;
        $scriptContent .= $jsTranslations;
        $response->setContent($scriptContent);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->content = $response->getContent();
        return $view;

    }

}