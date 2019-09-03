<?php

namespace Admin\Forms;

use Friday\Core\Form\Builder\Tab;
use Friday\Core\Form\Element\Checks;
use Friday\Core\Form\Element\Submit;
use Friday\Core\Form\Element\Text;
use Friday\Core\Form\Element\Password;
use Friday\Core\Form\Element\File;
use Friday\Core\Form\Element\Radio;
use Friday\Core\Form\Form;
use Friday\Core\Models\User;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;

class UserForm extends Form
{
	public function initialize (User $user)
	{
		$email = new Text('email', ['placeholder' => 'Email', 'icon' => 'envelope']);
		$email->setLabel('Email');
		$email->addValidators(
		[
			new PresenceOf(['message' => 'Введите Email']),
			new Email(['message' => 'Введите правильный Email'])
		]);

		$gender = new Radio('gender', ['M' => 'Мужской', 'F' => 'Женский']);
		$gender->setLabel('Пол');
		$gender->addValidator(new InclusionIn(['message' => 'Неверное значения пола', 'domain' => ['M', 'F']]));

		$password = new Password('password', ['icon' => 'lock', 'generator' => true, 'id' => 'password']);
		$password->setLabel('Пароль');

		if (!$user->id)
			$password->addValidator(new PresenceOf(['message' => 'Введите пароль']));

		$password_confirm = new Password('password_confirm', ['icon' => 'lock', 'id' => 'password_confirm']);
		$password_confirm->setLabel('Проверка пароля');
		$password_confirm->addValidator(new Confirmation(['message' => 'Пароли не совпадают', 'with' => 'password']));

		$name = new Text('name', ['placeholder' => 'Введите имя']);
		$name->setLabel('Имя');

		$last_name = new Text('last_name', ['placeholder' => 'Введите фамилию']);
		$last_name->setLabel('Фамилия');

		$second_name = new Text('second_name', ['placeholder' => 'Введите отчество']);
		$second_name->setLabel('Отчество');

		$photo = new File('photo');
		$photo->setLabel('Фотография');

		$groups = new Checks('groups_id', [
			'model' => ['name' => 'Friday\Core\Models\Group'],
			'tab' => 'groups'
		]);

		$groups->setLabel('Группы');

		$submit = new Submit('send');

		if ($user->id)
			$submit->setLabel('Сохранить');
		else
			$submit->setLabel('Добавить');

		$tabInfo = new Tab('Информация', 'info');
		$this->addTab($tabInfo);

		$tabInfo->add($email);
		$tabInfo->add($password);
		$tabInfo->add($password_confirm);
		$tabInfo->add($last_name);
		$tabInfo->add($name);
		$tabInfo->add($second_name);
		$tabInfo->add($gender);
		$tabInfo->add($photo);

		$this->add($groups);

		$this->addActionElement($submit);
	}
}