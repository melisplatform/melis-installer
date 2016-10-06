<?php
	
namespace MelisInstaller\Service;

interface MelisCoreConfigServiceInterface 
{
	public function getItem($pathString = '');
	
	public function prefixIdsKeysRec($array, $prefix);
}