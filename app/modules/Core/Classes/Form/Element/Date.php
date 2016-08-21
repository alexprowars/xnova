<?php

namespace Friday\Core\Form\Element;

use Phalcon\Di;
use Phalcon\Forms\Element\Date as PhalconDate;

class Date extends PhalconDate
{
	public function render ($attributes = null)
	{
		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">';
		$result .= '<label class="col-md-3 control-label">'.$this->getLabel().'</label>';
		$result .= '<div class="col-md-4">';

		$result .= '<div class="input-group date" data-date-format="dd.mm.yyyy" id="'.$this->getName().'-picker">
			<input type="text" class="form-control" placeholder="Выберите дату рождения" name="birthday" value="'.$this->getValue().'" title="">
			<span class="input-group-btn">
				<button class="btn default" type="button">
					<i class="fa fa-calendar"></i>
				</button>
			</span>
		</div>';

		$result .= '</div></div>';

		$result .= '<script type="text/javascript">
			$(document).ready(function() {
				$(\'#'.$this->getName().'-picker\').datepicker({
					autoclose: true,
					format: "dd.mm.yyyy",
					startDate: "01.01.1940",
					endDate: "0d",
					language: "ru",
					forceParse: false
				});
		
				$(\'#'.$this->getName().'-picker input\').inputmask("d.m.y", {
					autoUnmask: true
				});
			});
		</script>';

		$assets = Di::getDefault()->getShared('assets');

		$assets->addCss('assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css');
		$assets->addJs('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
		$assets->addJs('assets/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.ru.min.js');

		return $result;
	}
}