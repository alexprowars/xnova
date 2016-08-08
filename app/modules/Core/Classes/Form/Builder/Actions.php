<?php

namespace Friday\Core\Form\Builder;

use Friday\Core\Form\Form;

class Actions
{
	private $_elements = [];
	/**
	 * @var Form
	 */
	private $_form = null;

	public function addElement ($element)
	{
		$this->_elements[] = $element;
	}

	public function getElements ()
	{
		return $this->_elements;
	}

	public function getForm ()
	{
		return $this->_form;
	}

	public function setForm (Form $form)
	{
		$this->_form = $form;
	}

	public function render ($options = [])
	{
		$result = '<div class="form-actions '.(isset($options['direction']) ? $options['directions'] : '').'"><div class="row"><div class="col-md-offset-3 col-md-4">';

		foreach ($this->_elements as $element)
			$result .= $this->_form->get($element)->render().' ';

		$result .= '</div></div></div>';

		return $result;
	}
}