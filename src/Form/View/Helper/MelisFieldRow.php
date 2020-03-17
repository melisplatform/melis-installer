<?php
namespace MelisInstaller\Form\View\Helper;
 
use Laminas\Form\View\Helper\FormRow;
use Laminas\Form\ElementInterface;
 
class MelisFieldRow extends FormRow
{
    const MELIS_TOGGLE_BUTTON_FACTORY = 'switch';
    const MELIS_SELECT_FACTORY        = 'select';
    const MELIS_MULTI_VAL_INPUT       = 'melis-multi-val-input';
 
	public function render(ElementInterface $element, $labelPosition = null)
	{
	    $formElement = '';
	    
	    if(!empty($element->getOption('tooltip')))
	    {
	        $element->setLabelOptions(array('disable_html_escape' => true));
	        $label = $element->getLabel().'<i class="fa fa-info-circle fa-lg pull-right" data-toggle="tooltip" data-placement="left" title="" data-original-title="'.$element->getOption('tooltip').'"></i>';
	        $element->setLabel($label);
	    }
	    
	    if($this->getClass($element) == self::MELIS_TOGGLE_BUTTON_FACTORY)
	    {
	        // recreate checkbox to render into a toggle button
	        $markup = '<div class="make-switch" data-on="1" data-off="0"><input type="%s" class="switch" name="%s" id="%s" value="%s" onchange="%s" %s></div>';
	        $attrib = $element->getAttributes();
	        $value  = $element->getValue();
	        $isChecked = !empty($value) ? 'checked' : '';
	        $toggleButton = sprintf($markup, $attrib['type'], $attrib['name'], $attrib['id'], $value, $attrib['onchange'], $isChecked);
	        
	        // disect label and element so it would not be included in the switch feature
	        $formElement .= '<div class="form-group"><label for="'.$attrib['name'].'">'.$element->getLabel().'</label> '.$toggleButton.'</div>';
	    }
	    elseif($element->getAttribute('type') == self::MELIS_SELECT_FACTORY)
	    {
	        // render to bootstrap select element
    		$element->setAttribute('class', 'form-control');
    		$element->setLabelOption('class','col-sm-2 control-label');
    		$formElement .= '<div class="form-group">'. parent::render($element, $labelPosition).'</div>';
	    }
	    elseif($element->getAttribute('class') == self::MELIS_MULTI_VAL_INPUT)
	    {
	        $dataTags = $element->getAttribute('data-tags');
	        
	        if(is_string($dataTags)) {
	            
	            $label = '<label for="tags">' . $element->getAttribute('data-label-text') . '</label>';
	            $getTags = explode(',', $dataTags);
	            $ulStart = '<ul class="multi-value-input clearfix">';
	            $ulEnd   = '</ul>';
	            $liSpan  = '<li><span>%s</span><a class="remove-tag fa fa-times"></a></li>';
	            $liInput = '<li class="tag-creator">' . parent::render($element, $labelPosition) . '</li>';
                $tagItems= '';
	            
	            $multiValElement = $label . $ulStart.'';
	            if(!empty($dataTags)) 
    	            foreach($getTags as $tagValues) 
    	                $tagItems .= sprintf($liSpan, $tagValues);
 
	            $multiValElement .=  $tagItems . $liInput. $ulEnd;
	            
	            $formElement .= '<div class="form-group">' . $multiValElement . '</div>';
	        }

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