<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;
use Phalcon\Tag;
use Phalcon\Validation\Validator\PresenceOf;

class Password extends Element
{
	public function render ($attributes = null)
	{
		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
		$result .= '<div class="col-md-4">';

		if (!isset($attributes['class']))
			$attributes['class'] = '';

		$attributes['class'] .= 'form-control';
		$attributes['value'] = '';

		$validators = $this->getValidators();

		foreach ($validators as $validator)
		{
			if ($validator instanceof PresenceOf)
				$attributes['required'] = '';
		}

		$options = $this->getAttributes();

		if (isset($options['generator']))
			$result .= '<div class="input-group">';

		if (isset($options['icon']))
		{
			$result .= '<div class="input-icon"><i class="fa fa-'.$options['icon'].'"></i>';
			$result .= Tag::passwordField($this->prepareAttributes($attributes, true));
			$result .= '</div>';
		}
		else
			$result .= Tag::passwordField($this->prepareAttributes($attributes, true));

		if (isset($options['generator']))
			$result .= '<span class="input-group-btn"><button id="genpassword" class="btn btn-success" type="button"><i class="fa fa-arrow-left fa-fw"></i> Создать</button></span></div>';

		$result .= '</div></div>';

		return $result;
	}
}