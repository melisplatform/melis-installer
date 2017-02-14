<?php
namespace MelisDemoCms\Form\View\Helper;
 
use Zend\Form\View\Helper\FormRow;
use Zend\Form\ElementInterface;
 
class MelisFieldRow extends FormRow
{
    const MELIS_SELECT_FACTORY        = 'select';
 
	public function render(ElementInterface $element, $labelPosition = null)
	{
	    $formElement = '';
	    if($element->getAttribute('type') == self::MELIS_SELECT_FACTORY)
	    {
	        // render to bootstrap select element
    		$element->setAttribute('class', 'form-control');
    		$element->setLabelOption('class','col-sm-2 control-label');
    		$formElement .= '<div class="form-group">'. parent::render($element, $labelPosition).'</div>';
	    }
	    else 
	    {
	        $formElement .= '<div class="form-group">'. parent::render($element, $labelPosition).'</div>';
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