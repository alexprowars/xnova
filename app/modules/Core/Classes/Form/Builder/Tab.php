<?php

namespace Friday\Core\Form\Builder;

use Friday\Core\Form\Form;
use Phalcon\Di;
use Phalcon\Forms\Element as PhalconElement;

class Tab
{
	private $_title = '';
	private $_id = '';
	/**
	 * @var PhalconElement[]
	 */
	private $_elements = [];

	/**
	 * @var Form
	 */
	private $_form = null;
	private $_partial = '';

	public function __construct($title = '', $id)
	{
		$this->_title = $title;
		$this->_id = $id;
	}

	public function getTitle ()
	{
		return $this->_title;
	}

	public function setTitle ($title)
	{
		$this->_title = $title;
	}

	public function getId ()
	{
		return $this->_id;
	}

	public function setId ($id)
	{
		$this->_id = $id;
	}

	public function getForm ()
	{
		return $this->_form;
	}

	public function setForm (Form $form)
	{
		$this->_form = $form;
	}

	public function setPartial ($path = '')
	{
		$this->_partial = $path;
	}

	public function add ($element)
	{
		if (!is_object($element))
			throw new \Exception('Element not object');

		if (!($element instanceof PhalconElement))
			throw new \Exception('Element not instanceof PhalconElement');

		if (!is_null($this->_form))
			$this->_form->add($element);

		$this->_elements[] = $element->getName();
	}

	public function renderTab ($options = [])
	{
		$result = '<li '.(isset($options['index']) && $options['index'] == 0 ? 'class="active"' : '').'><a href="#tab_'.$this->_id.'" data-toggle="tab">'.$this->_title.'</a></li>';

		return $result;
	}

	public function renderElements ($options = [])
	{
		$result = '<div class="tab-pane form-horizontal form-row-seperated '.(isset($options['index']) && $options['index'] == 0 ? 'active' : '').'" id="tab_'.$this->_id.'">';

		$cnt = count($this->_elements);

		if ($cnt > 0)
		{
			foreach ($this->_elements as $i => $element)
			{
				$attrs = [];

				if ($i + 1 == $cnt)
					$attrs['last'] = true;

				$result.= $this->_form->get($element)->render($attrs);
			}
		}

		if ($this->_partial != '')
			$result .= Di::getDefault()->getShared('view')->getPartial($this->_partial);

		$result .= '</div>';

		return $result;
	}
}