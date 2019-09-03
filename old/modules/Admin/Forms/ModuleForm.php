<?php

namespace Admin\Forms;

use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Form;
use Phalcon\Validation\Validator\PresenceOf;

class ModuleForm extends Form
{
	public function initialize ()
	{
		$sort = new Numeric('sort');
		$sort->addValidator(new PresenceOf(['message' => 'Введите индекс сортировки']));

		$this->add($sort);
	}
}

?>