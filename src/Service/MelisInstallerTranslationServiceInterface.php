<?php

namespace MelisInstaller\Service;

interface MelisInstallerTranslationServiceInterface
{
    public function getTranslationMessages($locale, $textDomain = 'default');
}