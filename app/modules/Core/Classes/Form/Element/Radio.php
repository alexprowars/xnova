<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;
use Phalcon\Forms\ElementInterface;
use Phalcon\Tag;

class Radio extends Element implements ElementInterface
{
	private $_optionsValues = [];

	public function __construct ($name, $options = null, $attributes = null)
	{
		$this->_optionsValues = $options;

		parent::__construct($name, $attributes);
	}

	public function render ($attributes = null)
	{
		$values = $this->getValue();

		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
		$result .= '<div class="col-md-9">';

		$result .= '<div class="mt-radio-inline">';

		$value = $this->getValue();

		foreach ($this->_optionsValues as $v => $n)
			$result .= '<label class="mt-radio mt-radio-outline"><input type="radio" name="'.$this->getName().'" value="'.$v.'" '.($value == $v ? 'checked' : '').'> '.$n.' <span></span></label>';

		$result .= '</div>';

		if (isset($model))
		{
			foreach ($model as $item)
			{
				$attrs = [$this->getName().'[]', 'value' => $item['id']];

				if (in_array($item['id'], $values))
					$attrs['checked'] = 'checked';

				$result .= Tag::checkField($attrs);
			}
		}

		$result .= '</div></div>';

		return $result;
	}
}