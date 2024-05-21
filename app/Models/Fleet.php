<?php

namespace App\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Entity\Coordinates;

class Fleet extends Model
{
	public $timestamps = false;
	protected $guarded = [];

	public $username = '';

	public static function boot()
	{
		parent::boot();

		self::creating(function (self $model) {
			$model->_beforeSave($model);
		});

		self::updating(function (self $model) {
			$model->_beforeSave($model);
		});
	}

	private function _beforeSave($model)
	{
		if (is_array($model->fleet_array)) {
			$model->fleet_array = json_encode($model->fleet_array);
		}
	}

	public function splitStartPosition()
	{
		return $this->start_galaxy . ':' . $this->start_system . ':' . $this->start_planet;
	}

	public function splitTargetPosition()
	{
		return $this->end_galaxy . ':' . $this->end_system . ':' . $this->end_planet;
	}

	public function getStartAdressLink($FleetType = '')
	{
		$uri = URL::route('galaxy', [
			'galaxy' => $this->start_galaxy,
			'system' => $this->start_system,
		], false);

		$uri = str_replace('/api', '', $uri);

		return '<a href="' . $uri . '" ' . $FleetType . '>[' . $this->splitStartPosition() . ']</a>';
	}

	public function getTargetAdressLink($FleetType = '')
	{
		$uri = URL::route('galaxy', [
			'galaxy' => $this->end_galaxy,
			'system' => $this->end_system,
		], false);

		$uri = str_replace('/api', '', $uri);

		return '<a href="' . $uri . '" ' . $FleetType . '>[' . $this->splitTargetPosition() . ']</a>';
	}

	public function getTotalShips()
	{
		$result = 0;

		$data = $this->getShips();

		foreach ($data as $type) {
			$result += $type['count'];
		}

		return $result;
	}

	public function getShips($fleets = false)
	{
		if (!$fleets) {
			$fleets = $this->fleet_array;
		}

		$result = [];

		if (!is_array($fleets)) {
			$fleets = json_decode($fleets, true);
		}

		if (!is_array($fleets)) {
			return [];
		}

		foreach ($fleets as $fleet) {
			if (!isset($fleet['id'])) {
				continue;
			}

			$fleetId = (int) $fleet['id'];

			$result[$fleetId] = [
				'id' => $fleetId,
				'count' => isset($fleet['count']) ? (int) $fleet['count'] : 0
			];

			if (isset($fleet['target'])) {
				$result[$fleetId]['target'] = (int) $fleet['target'];
			}
		}

		return $result;
	}

	public function beforeSave()
	{
		if (is_array($this->fleet_array)) {
			$this->fleet_array = json_encode(array_values($this->fleet_array));
		}
	}

	public function canBack()
	{
		return ($this->mess == 0 || ($this->mess == 3 && $this->mission != 15) && $this->mission != 20 && $this->target_owner != 1);
	}

	public function getOriginCoordinates(): Coordinates
	{
		return new Coordinates($this->start_galaxy, $this->start_system, $this->start_planet, $this->start_type);
	}

	public function getDestinationCoordinates(): Coordinates
	{
		return new Coordinates($this->end_galaxy, $this->end_system, $this->end_planet, $this->end_type);
	}
}
