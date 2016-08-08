<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element\Numeric as PhalconNumeric;
use Phalcon\Validation\Validator\PresenceOf;

class Numeric extends PhalconNumeric
{
	public function render ($attributes = null)
	{
		$attributes['class'] = 'form-control';

		$validators = $this->getValidators();

		foreach ($validators as $validator)
		{
			if ($validator instanceof PresenceOf)
				$attributes['required'] = '';
		}

		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel();

		if (isset($attributes['required']))
			$result .= '<span class="required"> *</span>';

		$result .= '</label>';
		$result .= '<div class="col-md-4">';

		$options = $this->getAttributes();

		if (isset($options['icon']))
		{
			$result .= '<div class="input-icon"><i class="fa fa-'.$options['icon'].'"></i>';
			$result .= parent::render($attributes);
			$result .= '</div>';
		}
		else
		{
			$result .= parent::render($attributes);
		}

		$result .= '</div></div>';

		return $result;
	}
}