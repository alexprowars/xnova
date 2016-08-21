<?php

namespace Friday\Core\Form\Element;

use Phalcon\Forms\Element;
use Phalcon\Forms\ElementInterface;
use Phalcon\Tag;

class Checks extends Element implements ElementInterface
{
	public function render ($attributes = null)
	{
		$values = $this->getValue();

		$options = $this->getAttributes();

		if (isset($options['model']) && isset($options['model']['name']))
		{
			$model = new $options['model']['name'];

			if (isset($options['model']['filter']))
				$model = $model::find($options['model']['filter'])->toArray();
			else
				$model = $model::find()->toArray();
		}

		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
		$result .= '<div class="col-md-9">';

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