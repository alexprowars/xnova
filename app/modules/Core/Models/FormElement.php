<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static FormElement[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static FormElement findFirst(mixed $parameters = null)
 */
class FormElement extends Model
{
	public $id;
	public $form_id;
	public $name;
	public $type;
	public $title;
	public $tab;

	public function getSource()
	{
		return DB_PREFIX."forms_elements";
	}
}