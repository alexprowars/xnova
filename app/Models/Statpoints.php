<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_owner
 * @property string $username
 * @property int $race
 * @property int $id_ally
 * @property string $ally_name
 * @property int $stat_type
 * @property int $stat_code
 * @property int $tech_rank
 * @property int $tech_old_rank
 * @property int $tech_points
 * @property int $tech_count
 * @property int $build_rank
 * @property int $build_old_rank
 * @property int $build_points
 * @property int $build_count
 * @property int $defs_rank
 * @property int $defs_old_rank
 * @property int $defs_points
 * @property int $defs_count
 * @property int $fleet_rank
 * @property int $fleet_old_rank
 * @property int $fleet_points
 * @property int $fleet_count
 * @property int $total_rank
 * @property int $total_old_rank
 * @property int $total_points
 * @property int $total_count
 * @property int $stat_hide
 */
class Statpoints extends Model
{
	public $timestamps = false;
	public $table = 'statpoints';
}