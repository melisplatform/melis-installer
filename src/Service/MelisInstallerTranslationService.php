<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Service;

use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\I18n\Translator\Translator;
/**
 * Translation Service for retrieveing all the translation messages 
 *
 */
class MelisInstallerTranslationService extends Translator implements ServiceLocatorAwareInterface, MelisInstallerTranslationServiceInterface 
{
    /**
     *
     * @var $serviceLocator ServiceLocatorInterface
     */
    public $serviceLocator;
    
    /**
     *
     * @var $fmContainer Container
     */
    protected $fmContainer;
    
    
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
        return $this;
    }
    
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    /**
     * Re-imports translation files from all the modules and re-writes it 
     * so it can be used in Javascript or any other scripts that would like
     * to use translation on their messages
     * @param String $locale
     * @param string $textDomain
     * @return Array
     */
    public function getTranslationMessages($locale, $textDomain = 'default')
    {
        // Get the translation service, so we would be able to fetch the current configs
        $translator = $this->getServiceLocator()->get('translator');
        $translation = $translator->getTranslator();
        
        // process to access the private properties of translation service
        $reflector = new \ReflectionObject($translation);
        $property = $reflector->getProperty('files');
        $property->setAccessible(true);
        $files = (array)$property->getValue($translation);
        

        // re-add translation file to a new Translation Class Object
        foreach($files['default']['*'] as $transKey => $transValues) 
        {
            $this->addTranslationFile('phparray', $transValues['filename'], 'default', $locale);
        }
        
        // Load Translation Messages
        if (!isset($this->messages[$textDomain][$locale])) {
            $this->loadMessages($textDomain, $locale);
        }
        
        // This is where the translated mesage are stored
        $translatedMessages =  (array)$this->messages[$textDomain][$locale];
        

        $messages = array();
        $key = '';
        foreach($translatedMessages as $translationKey => $translationValues) {
            $key =  str_replace("'", "\'", $translationKey);
            $messages[$key] = str_replace("'", "\'", $translationValues);
        }
        
        return $messages;
        
    }
    
    /**
     * Returns the date format depending on what locale
     * @param String $locale
     * @return string
     */
    public function getDateFormatByLocate($locale)
    {
        $dFormat = '';
        switch($locale) {
            case 'fr_FR':
                $dFormat = '%d/%m/%Y %H:%M:%S';
            break;
            case 'en_EN':
            default:
                $dFormat = '%m/%d/%Y %H:%M:%S';
            break;
        }
        
        return $dFormat;
    }
    
    /**
     * For JS usage
     * @param String $locale
     * @return string
     */
    public function getDateFormat($locale) 
    {
        $dFormat = '';
        switch($locale) {
            case 'fr_FR':
                $dFormat = 'DD/MM/YYYY';
                break;
            case 'en_EN':
            default:
                $dFormat = 'MM/DD/YYYY';
                break;
        }
        
        return $dFormat;
    }
    
    public function addTranslationFiles($locale)
    {
        $status = false;
        $defaultTransInterface = 'en_EN.interface.php';
        $defaultTransForms     = 'en_EN.forms.php';
        $transInterface = $locale.'.interface.php';
        $transForms     = $locale.'.forms.php';
        $excludeModules = array('.', '..', '.gitignore', 'MelisSites');
        $modules = array();
        $modDir  = scandir('module');
        
        foreach($modDir as $moduleName) {
        
            if(!in_array($moduleName, $excludeModules)) {
                $modules[] = $moduleName.'/language';
            }
        
        }
        
        foreach($modules as $translationPath) {
            $truePath = 'module/'.$translationPath;
            if(is_writable($truePath)) {
                if(file_exists($truePath.'/'.$defaultTransInterface)) {
                    copy($truePath.'/'.$defaultTransInterface, $truePath.'/'.$transInterface);
                    
                    // check if the path has forms translations
                    if(file_exists($truePath.'/'.$defaultTransForms)) {
                        copy($truePath.'/'.$defaultTransForms, $truePath.'/'.$transForms);
                    }
                    
                    // make sure that the created translation file exists
                    if(file_exists($truePath.'/'.$transInterface)) {
                        $status = true;
                    }
                }
            }
        }
        
        return $status;
    }
    
    public function getFilesByLocale($locale) 
    {
        $excludeModules = array('.', '..', '.gitignore', 'MelisSites');
        $modDir  = scandir('module');
        $modules = array();
        $translationFiles = array();
        foreach($modDir as $moduleName) {
            
            if(!in_array($moduleName, $excludeModules)) {
                $modules[] = $moduleName.'/language';
            }
            
        }
        
        foreach($modules as $translationPath) {
            
            $truePath = 'module/'.$translationPath;
            
            if(file_exists($truePath)) {
                $path = scandir($truePath);
                if(!empty($path)) {
                    foreach($path as $files) {
                        if(!in_array($files, $excludeModules)) {
                            
                            $fileNames = explode('.', $files);
                            if($fileNames[0] == $locale) {
                                $translationFiles[] = $truePath.'/'.$files;
                            }
                            
                        }
                    }
                }
            }
        }
        
        return $translationFiles;
    }
    
    public function getTranslationsLocale() 
    {
        $path = 'module/MelisCore/language/';
        $dir  = scandir($path);
        $files = array();
        foreach($dir as $file) {
             if(is_file($path.$file)) {
                 $files[] = $file;
             }
        }
        
        $locales = array();
        foreach($files as $file) {
            $locale = explode('.',$file);
            $locales[] = $locale[0];
        }
        
        // re-add locales to get the unique locales and fix proper array indexing
        $uniqueLocales = array_unique($locales);
        $newUniqueLocales = array();
        foreach($uniqueLocales as $locale) {
            $newUniqueLocales[] = $locale;
        }

        
        return $newUniqueLocales;
    }

}