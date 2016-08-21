<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;

class Submit extends Element
{
	public function render ($attributes = null)
	{
		$result  = '<button type="submit" name="'.$this->getName().'" class="btn green">'.$this->getLabel().'</button>';

		return $result;
	}
}