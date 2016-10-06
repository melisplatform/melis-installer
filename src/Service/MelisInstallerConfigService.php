<?php

namespace MelisInstaller\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

class MelisInstallerConfigService implements MelisCoreConfigServiceInterface, ServiceLocatorAwareInterface
{
	public $serviceLocator;
	public $appConfig;
	
	public function setServiceLocator(ServiceLocatorInterface $sl)
	{
		$this->serviceLocator = $sl;
		return $this;
	}
	
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}
	
	private function getItemRec($pathTab, $position, $configTab)
	{
		if (!empty($pathTab[$position]))
		{
			foreach($configTab as $keyConfig => $valueConfig)
			{
				if ($pathTab[$position] == $keyConfig)
					return $this->getItemRec($pathTab, $position + 1, $configTab[$keyConfig]);
			}
			
			return array();
		}
		else
		{
			if ($position == 0)
				return $this->getItemRec($pathTab, $position + 1, $configTab);
			else
				return $configTab;
		}
			
	}
	
	public function getItem($pathString = '', $prefix = '')
	{
		$config = $this->serviceLocator->get('config');
		if (!empty($config['plugins']))
			$this->appConfig = $config['plugins'];
		else
			$this->appConfig = array();
		$melisKeys = $this->getMelisKeys($this->appConfig);
		
		if ($pathString == '' || $pathString == '/')
			return $this->appConfig;
		
		if (!empty($melisKeys[$pathString]))
			$pathString = $melisKeys[$pathString];
		
		$pathTab = explode('/', $pathString);
		
		$items = $this->getItemRec($pathTab, 0, $this->appConfig);
		$items = $this->addItemsLinkedByType($items);
		$items = $this->translateAppConfig($items);
		
		if ($prefix != '')
			$items = $this->prefixIdsKeysRec($items, $prefix);
		
		return $items;
	}
	
	public function getMelisKeys($array = array(), $fullPath = '')
	{
		$final = array();
		
		if (empty($array))
		{
			$config = $this->getServiceLocator()->get('config');
			if (!empty($config['plugins']))
				$array = $config['plugins'];
			else
				$array = array();
		}
		
		foreach($array as $keyConfig => $valueConfig)
		{
			$fullPathTmp = $fullPath . '/' . $keyConfig;
			if (!empty($valueConfig['conf']) && !empty($valueConfig['conf']['melisKey']))
			{
				$final[$valueConfig['conf']['melisKey']] = $fullPathTmp;
			}
			if (!empty($valueConfig['interface']))
			{
				$subarray = $this->getMelisKeys($valueConfig['interface'], $fullPathTmp . '/interface');
				$final = array_merge($final, $subarray);
			}
		}
		
		return $final;
	}
	
	private function addItemsLinkedByType($array)
	{
		if (!empty($array['conf']['type']))
		{
			$type = $array['conf']['type'];
			$pathTab = explode('/', $type);
			$items = $this->getItemRec($pathTab, 0, $this->appConfig);
			
			$array = ArrayUtils::merge($items, $array);
			
		}
		
		foreach($array as $key => $value)
		{
			if (is_array($value) && $key != 'conf')
			{
				$children = $this->addItemsLinkedByType($value);
				$array[$key] = $children;
			}
			else
				$final[$key] = $value;
		}
		
		return $array;
	}

	public function prefixIdsKeysRec($array, $prefix)
	{
		if (empty($prefix))
			return $array;
		
		if (!empty($array['id']))
			$array['id'] = $prefix . $array['id'];
		
		$final = array();
		foreach($array as $key => $value)
		{
			if (is_array($value))
			{
				$children = $this->prefixIdsKeysRec($value, $prefix);
				$final[$key] = $children;
			}
			else
				$final[$key] = $value;
		}
		
		return $final;
	}
	

	public function translateAppConfig($array)
	{
		$translator = $this->serviceLocator->get('translator');
	
		$final = array();
		foreach($array as $key => $value)
		{
			if (is_array($value))
			{
				$children = $this->translateAppConfig($value);
				$final[$key] = $children;
			}
			else
			{
				if (substr($value, 0, 3) == 'tr_')
					$value = $translator->translate($value);
				$final[$key] = $value;
			}
		}
	
		return $final;
	}
	
	public function getJsCallbacksDatas($array, $final = array(), $datas = array())
	{
		if (!empty($array['interface']))
		{
			foreach ($array['interface'] as $keyInterface => $valueInterface)
			{
				list($final, $datas) = $this->getJsCallbacksDatas($valueInterface, $final, $datas);
			}
		}

		if (!empty($array['forward']) && !empty($array['forward']['jscallback']))
		{
			array_push($final, $array['forward']['jscallback']);
		}
		if (!empty($array['forward']) && !empty($array['forward']['jsdatas']))
		{
			foreach ($array['forward']['jsdatas'] as $keyJsDatas => $jsDatas)
				$datas[$keyJsDatas] = $jsDatas;
		}
		
		return array($final, $datas);
	}
	
	/**
	 * Disable/Enable a field from the the config form array
	 * 
	 * @param array $array
	 * @param string $fieldName
	 * @param boolean $isDisabled
	 * @return array
	 */
	public function setFormFieldDisabled($array, $fieldName, $isDisabled = false)
	{
		if (!empty($array['elements']))
		{
			foreach ($array['elements'] as $keyElement => $element)
			{
				if (!empty($element['spec']) && !empty($element['spec']['name']) && 
					$element['spec']['name'] == $fieldName)
				{
					if (empty($element['spec']['attributes']))
						$array['elements'][$keyElement]['spec']['attributes'] = array();
					$array['elements'][$keyElement]['spec']['attributes']['disabled'] = $isDisabled;
				}
			}
		}
		
		return $array;
	}
	
	/**
	 * Set a required field in input filters from the the config form array
	 *
	 * @param array $array
	 * @param string $fieldName
	 * @param boolean $isRequired
	 * @return array
	 */
	public function setFormFieldRequired($array, $fieldName, $isRequired = false)
	{
		if (!empty($array['input_filter']))
		{
			foreach ($array['input_filter'] as $keyElement => $element)
			{
				if ($keyElement == $fieldName)
				{
					$array['input_filter'][$keyElement]['required'] = $isRequired;
				}
			}
		}
		
		return $array;
	}
	
	public function getOrderInterfaceConfig($keyInterface)
	{
		$config = $this->getServiceLocator()->get('config');
		if (!empty($config['interface_ordering']))
			$array = $config['interface_ordering'];
		else
			$array = array();
		
		if (!empty($array[$keyInterface]))
			return $array[$keyInterface];
		else
			return array();
	}
	
	public function isInterfaceDisabled($keyInterface)
	{
		$config = $this->getServiceLocator()->get('config');
		if (!empty($config['interface_disable']))
			$array = $config['interface_disable'];
		else
			$array = array();
		
		if (array_search($keyInterface, $array) !== false)
			return true;
		else
			return false;
	}
	
	public function getItemPerPlatform($pathString = '', $prefix = '')
	{
		$config = $this->getItem($pathString, $prefix);
		
		$final = array();
		if (!empty($config['default']))
			$final = $config['default'];
		
		$melisPlatform = getenv('MELIS_PLATFORM');
		if (!empty($config[$melisPlatform]))
			$final = ArrayUtils::merge($final, $config[$melisPlatform]);
		
		return $final;
	}
}