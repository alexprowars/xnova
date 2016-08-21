<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;

class Cancel extends Element
{
	public function render ($attributes = null)
	{
		return '<a href="'.$this->getAttribute('url').'" class="btn default">'.$this->getLabel().'</a>';
	}
}