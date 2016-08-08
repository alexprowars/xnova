<?php

namespace Friday\Core\Form;

use Friday\Core\Models\Form as FormModel;
use Friday\Core\Models\FormElement;

class Builder
{
	/**
	 * @var FormModel|null
	 */
	private $_builder = null;

	/**
	 * @var Form
	 */
	private $_form = null;

	public function __construct($formId)
	{
		$this->_builder = FormModel::findFirst($formId);

		if (!$this->_builder)
			throw new \Exception('Form not found');
	}

	public function setForm (Form $form)
	{
		$this->_form = $form;
	}

	public function getForm ()
	{
		return $this->_form;
	}

	public function getData ()
	{
		$elements = FormElement::find(["conditions" => "form_id = ?0", "bind" => [$this->_builder->id]]);

		foreach ($elements as $element)
		{
			$item = new $element->type($element->name);
			$item->setLabel($element->title);

			if ($element->tab != '')
			{
				$tab = $this->_form->getTab($element->tab);

				if (!is_null($tab))
					$tab->add($item);
			}
			else
				$this->_form->add($item);
		}
	}
}