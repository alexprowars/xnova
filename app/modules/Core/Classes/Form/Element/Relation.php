<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;
use Phalcon\Tag;

class Relation extends Element
{
	public function render ($attributes = null)
	{
		$result  = '';

		$source = $this->getAttribute('source');

		$using = $this->getAttribute('using');

		if (is_null($using) || !is_array($using))
			$using = ["id", "title"];

		if (!is_null($source))
		{
			/**
			 * @var $source \Phalcon\Mvc\ModelInterface
			 */
			if (is_string($source))
				$source = $source::find(["columns" => $using]);
		}

		if (is_object($source))
		{
			$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
			$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
			$result .= '<div class="col-md-4">';

			$attrs = [
				$this->getName(),
				$source,
				"using"		=> $using,
				"useEmpty"	=> true,
				"emptyText"	=> "Выберите...",
				"value"		=> $this->getValue(),
				"class"		=> "bs-select form-control input-sm"
			];

			if (is_bool($this->getAttribute('disabled')) && $this->getAttribute('disabled'))
				$attrs['disabled'] = '';

			$result .= Tag::select($attrs);

			$result .= '</div></div>';
		}

		return $result;
	}
}