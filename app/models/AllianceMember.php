<?php
namespace App\Models;

use Phalcon\Mvc\Model;

class AllianceMember extends Model
{
	public $a_id;
	public $u_id;
	public $rank;
	public $time;

	public function getSource()
	{
		return DB_PREFIX."alliance_members";
	}

	public function onConstruct()
	{
	 	$this->hasOne("a_id", "App\Models\Alliance", "id", Array('alias' => 'alliance'));
	}
}

?>