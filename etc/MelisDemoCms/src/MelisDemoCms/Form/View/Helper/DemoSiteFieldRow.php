<?php
namespace MelisDemoCms\Form\View\Helper;
 
use Laminas\Form\View\Helper\FormRow;
use Laminas\Form\ElementInterface;
 
class DemoSiteFieldRow extends FormRow
{
    const MELIS_SELECT_FACTORY        = 'select';
 
	public function render(ElementInterface $element, $labelPosition = null)
	{
	    $formElement = '';
	    $element->setLabelOption('class', 'control-label');
	    if($element->getAttribute('type') == self::MELIS_SELECT_FACTORY)
	    {
	        // render to bootstrap select element
    		$element->setAttribute('class', '');
    		$formElement .= '<div class="input-box mb-20">'. parent::render($element, $labelPosition).'</div>';
	    }
	    else 
	    {
	        // render to bootstrap select element
	        $element->setAttribute('class', 'info');
	        $formElement .= '<div class="input-box mb-20">'. parent::render($element, $labelPosition).'</div>';
	    }
	    
		return $formElement;
	}
	
	/**
	 * Returns the class attribute of the element
	 * @param ElementInterface $element
	 * @return String
	 */
	protected function getClass(ElementInterface $element)
	{
	    return $element->getAttribute('class');
	}
}