<?php

namespace Admin\Forms;

use Friday\Core\Form\Element\Checks;
use Friday\Core\Form\Element\Submit;
use Friday\Core\Form\Element\Text;
use Friday\Core\Form\Form;
use Friday\Core\Models\Group;
use Phalcon\Validation\Validator\PresenceOf;

class GroupForm extends Form
{
	public function initialize (Group $group)
	{
		$title = new Text('title', ['placeholder' => 'Введите название группы']);
		$title->addValidator(new PresenceOf(['message' => 'Введите название группы']));
		$title->setLabel('Название');

		$roles = new Checks('roles');

		$submit = new Submit('send');

		if ($group->id)
			$submit->setLabel('Сохранить');
		else
			$submit->setLabel('Добавить');

		$this->add($title);
		$this->add($roles);

		$this->addActionElement($submit);
	}
}