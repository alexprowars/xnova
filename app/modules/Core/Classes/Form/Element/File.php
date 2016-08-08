<?php

namespace Friday\Core\Form\Element;

use Phalcon\Di;
use Phalcon\Forms\Element\File as PhalconFile;

class File extends PhalconFile
{
	public function render ($attributes = null)
	{
		$value = $this->getValue();

		$result  = '<div class="form-group '.(isset($attributes['last']) && $attributes['last'] ? 'last' : '').'">
			<label for="exampleInputFile" class="col-md-3 control-label">'.$this->getLabel().'</label>
			<div class="col-md-9">';

		if ($value != '')
		{
			$result .= '<img src="'.$value.'" style="max-width: 100px;max-height: 100px;">
			<div class="mt-checkbox-inline">
				<label class="mt-checkbox mt-checkbox-outline">
					<input type="checkbox" name="'.$this->getName().'_delete" value="Y" title=""> Удалить фото
					<span></span>
				</label>
			</div><br>';
		}

		$result .= '<div class="fileinput fileinput-new" data-provides="fileinput">
			<div class="input-group input-large">
				<div class="form-control uneditable-input input-fixed input-medium" data-trigger="fileinput">
					<i class="fa fa-file fileinput-exists"></i>&nbsp;
					<span class="fileinput-filename"></span>
				</div>
				<span class="input-group-addon btn default btn-file">
					<span class="fileinput-new">Выберите файл</span>
					<span class="fileinput-exists">Изменить</span>
					<input type="file" name="'.$this->getName().'">
				</span>
				<a href="javascript:;" class="input-group-addon btn red fileinput-exists" data-dismiss="fileinput">Удалить</a>
			</div>
		</div>';

		$result .= '</div></div>';

		$assets = Di::getDefault()->getShared('assets');

		$assets->addCss('assets/admin/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css');
		$assets->addJs('assets/admin/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js');

		return $result;
	}
}