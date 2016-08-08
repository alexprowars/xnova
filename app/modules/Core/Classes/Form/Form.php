<?php

namespace Friday\Core\Form;

use Friday\Core\Form\Builder\Actions;
use Friday\Core\Form\Builder\Tab;
use Phalcon\Forms\Element;
use Phalcon\Forms\Form as PhalconForm;
use Phalcon\Validation\Validator\PresenceOf;

class Form extends PhalconForm
{
	/**
	 * @var Tab[]
	 */
	private $_tabs = [];

	/**
	 * @var Actions
	 */
	private $_actions = null;

	/**
	 * @var Builder
	 */
	private $_builder = null;
	private $_id = '';

	public function __construct($entity, $userOptions = null)
	{
		$this->setFormId(md5(time()).mt_rand(1000, 9999));

		parent::__construct($entity, $userOptions);
	}

	public function getTabElements ($tabId)
	{
		$result = [];

		foreach ($this->getElements() as $item)
		{
			if ($item->getAttribute('tab') == $tabId)
				$result[] = $item;
		}

		return $result;
	}

	public function addTab (Tab $tab)
	{
		$this->_tabs[$tab->getId()] = $tab;

		$tab->setForm($this);
	}

	public function getTab ($tabId)
	{
		if (isset($this->_tabs[$tabId]))
			return $this->_tabs[$tabId];

		return null;
	}

	public function addActionElement (Element $element)
	{
		$this->add($element);

		if (is_null($this->_actions))
		{
			$this->_actions = new Actions();
			$this->_actions->setForm($this);
		}

		$this->_actions->addElement($element->getName());
	}

	public function setFormId ($id)
	{
		$this->_id = $id;
	}

	public function getFormId ()
	{
		return 'form_'.$this->_id;
	}

	public function build ($options = [])
	{
		if (!is_null($this->_builder))
			$this->_builder->getData();

		$result = '<form action="'.$this->getAction().'" id="'.$this->getFormId().'" class="form" enctype="multipart/form-data" method="POST">';

		if (!empty($this->_tabs))
		{
			$result .= '<div class="tabbable-line boxless tabbable-reversed">';

			if (count($this->_tabs) > 1)
			{
				$result .= '<ul class="nav nav-tabs">';

				$i = 0;

				foreach ($this->_tabs as $tab)
				{
					$result .= $tab->renderTab(['index' => $i]);

					$i++;
				}

				$result .= '</ul>';
				$result .= '<div class="tab-content">';
			}

			$i = 0;

			foreach ($this->_tabs as $tab)
			{
				$result .= $tab->renderElements(['index' => $i]);

				$i++;
			}

			if (count($this->_tabs) > 1)
				$result .= '</div>';

			$result .= '</div>';
		}
		else
		{
			$result .= '<div class="form-horizontal form-row-seperated">';

			$elements = [];

			foreach ($this->getElements() as $element)
			{
				if (is_null($this->_actions) || (!is_null($this->_actions) && !in_array($element->getName(), $this->_actions->getElements())))
					$elements[] = $element->getName();
			}

			$cnt = count($elements);

			foreach ($elements as $i => $element)
			{
				$attrs = [];

				if ($i + 1 == $cnt)
					$attrs['last'] = true;

				$result.= $this->get($element)->render($attrs);
			}

			$result .= '</div>';
		}

		$result .= $this->renderActions();

		$result .= '</form>';

		$result .= $this->renderValidation();

		return $result;
	}

	public function renderActions ()
	{
		if (!is_null($this->_actions))
			return $this->_actions->render();

		return '';
	}

	public function addBuilder (Builder $builder)
	{
		$this->_builder = $builder;
		$this->_builder->setForm($this);
	}

	public function renderValidation ()
	{
		$assets = $this->getDI()->getShared('assets');

		$assets->addJs('assets/admin/global/plugins/jquery-validation/js/jquery.validate.min.js');
		$assets->addJs('assets/admin/global/plugins/jquery-validation/js/localization/messages_ru.min.js');

		$result = '<script type="text/javascript">$(document).ready(function(){';

		$messages = [];
		$rules = [];

		foreach ($this->getElements() as $element)
		{
			$validators = $element->getValidators();

			foreach ($validators as $validate)
			{
				if ($validate instanceof PresenceOf)
				{
					$messages[$element->getName()] = [
						'required' => $validate->getOption('message')
					];

					$rules[$element->getName()] = [
						'required' => true
					];
				}
			}
		}

		$result .= '$(\'#'.$this->getFormId().'\').validate({';
		$result .= 'errorElement: \'span\', errorClass: \'help-block help-block-error\', focusInvalid: false, ignore: "", messages: '.json_encode($messages).', rules: '.json_encode($rules).'';

		$result .= ",errorPlacement: function (error, element) {
			if (element.closest(\".input-group\").size() > 0) {
				error.insertAfter(element.closest(\".input-group\"));
			} else if (element.attr(\"data-error-container\")) { 
				error.appendTo(element.attr(\"data-error-container\"));
			} else if (element.parents('.radio-list').size() > 0) { 
				error.appendTo(element.parents('.radio-list').attr(\"data-error-container\"));
			} else if (element.parents('.radio-inline').size() > 0) { 
				error.appendTo(element.parents('.radio-inline').attr(\"data-error-container\"));
			} else if (element.parents('.checkbox-list').size() > 0) {
				error.appendTo(element.parents('.checkbox-list').attr(\"data-error-container\"));
			} else if (element.parents('.checkbox-inline').size() > 0) { 
				error.appendTo(element.parents('.checkbox-inline').attr(\"data-error-container\"));
			} else {
				error.insertAfter(element); // for other inputs, just perform default behavior
			}
		},
		highlight: function (element) {
			$(element).closest('.form-group').addClass('has-error');
		},
		unhighlight: function (element) { // revert the change done by hightlight
			$(element).closest('.form-group').removeClass('has-error').find('span.help-block-error').remove();
		},
		success: function (label) {
			label.closest('.form-group').removeClass('has-error').find('span.help-block-error').remove();
		},
		submitHandler: function (form) {
			form[0].submit();
		}";

		$result .= '});';
		$result .= '});</script>';

		return $result;
	}
}