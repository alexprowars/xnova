<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;
use Phalcon\Forms\ElementInterface;
use Phalcon\Tag;

class Check extends Element implements ElementInterface
{
	public function render ($attributes = null)
	{
		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
		$result .= '<div class="col-md-9">';

		$result .= '<div class="mt-checkbox-inline">';
		$result .= '<label class="mt-checkbox mt-checkbox-outline">';
		$result .= '<input type="hidden" name="'.$this->getName().'" value="'.(!is_null($this->getAttribute('default')) ? $this->getAttribute('default') : 'N').'">';

		$result .= Tag::checkField($this->prepareAttributes($attributes, true));

		$result .= "<span></span></label></div>";

		$result .= '</div></div>';

		return $result;
	}
}