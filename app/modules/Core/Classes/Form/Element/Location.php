<?php

namespace Friday\Core\Form\Element;

use Phalcon\Di;
use Phalcon\Forms\Element\Numeric as PhalconNumeric;

class Location extends PhalconNumeric
{
	public function render ($attributes = null)
	{
		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
		$result .= '<div class="col-md-4">';

		$result .= '<div class="input-group">
			<span class="input-group-addon">
				<i class="fa fa-globe"></i>
			</span>';

		$value = $this->getValue();

		if ($value > 0)
		{
			$location = \Friday\Core\Models\Location::findFirst($value);

			$result .= '<input type="hidden" id="'.$this->getName().'-data" name="'.$this->getName().'_id" value="'.$location->id.'">';
			$result .= '<input type="text" id="'.$this->getName().'-value" class="form-control" value="'.$location->getTitle().'" title="">';
		}
		else
		{
			$result .= '<input type="hidden" id="'.$this->getName().'-data" name="'.$this->getName().'_id" value="">';
			$result .= '<input type="text" id="'.$this->getName().'-value" class="form-control" value="" title="">';
		}

		$result .= '</div>';

		if (isset($location))
			$result .= '<a href="https://yandex.ru/maps/?text='.$location->getFullChain().'" target="_blank">'.$location->getFullChain().' на карте</a>';

		$url = Di::getDefault()->getShared('url');

		$result .= '
			<script type="text/javascript">
				$(document).ready(function()
				{
					var '.$this->getName().'Finder = new Bloodhound({
						datumTokenizer: function (d){return d.id},
						queryTokenizer: Bloodhound.tokenizers.whitespace,
						remote: {url: \''.$url->get('locations/find/').'?q=%QUERY\', wildcard: \'%QUERY\'}
					});
			
					'.$this->getName().'Finder.initialize();
			
					$(\'#'.$this->getName().'-value\').typeahead({
						minLength: 3,
						classNames: { hint: \'\', input: \'\' }
					},
					{
						name: \'location\', displayKey: \'value\', source: '.$this->getName().'Finder.ttAdapter()
					})
					.bind(\'typeahead:select\', function(ev, suggestion)
					{
						$(\'#'.$this->getName().'-data\').val(suggestion.id);
					});
			});
		</script>';

		$result .= '</div></div>';

		$assets = Di::getDefault()->getShared('assets');

		$assets->addCss('assets/global/plugins/typeahead/typeahead.css');
		$assets->addJs('assets/global/plugins/typeahead/handlebars.min.js');
		$assets->addJs('assets/global/plugins/typeahead/typeahead.bundle.min.js');

		return $result;
	}
}