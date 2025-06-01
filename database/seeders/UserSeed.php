<?php

namespace Database\Seeders;

use App\Facades\Galaxy;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeed extends Seeder
{
	public function run()
	{
		if (!User::find(1)) {
			$user = User::creation([
				'username' => 'admin',
				'email'    => 'admin@admin.com',
				'password' => 'password',
			]);

			$user->assignRole('admin');

			$user->setTech('spy', 10);
			$user->setTech('computer', 10);
			$user->setTech('military', 10);
			$user->setTech('shield', 10);
			$user->setTech('defence', 10);
			$user->setTech('hyperspace', 10);
			$user->setTech('combustion', 10);
			$user->setTech('impulse_motor', 10);
			$user->setTech('hyperspace_motor', 10);
			$user->setTech('laser', 10);
			$user->setTech('ionic', 10);

			$planet = Galaxy::createPlanetByUser($user);
			$planet->updateAmount('metal_mine', 10);
			$planet->updateAmount('crystal_mine', 10);
			$planet->updateAmount('deuterium_mine', 10);
			$planet->updateAmount('solar_plant', 15);
			$planet->updateAmount('fusion_plant', 5);
			$planet->updateAmount('robot_factory', 8);
			$planet->updateAmount('nano_factory', 2);
			$planet->updateAmount('hangar', 10);
			$planet->updateAmount('laboratory', 10);
			$planet->updateAmount('metal_store', 5);
			$planet->updateAmount('crystal_store', 5);
			$planet->updateAmount('deuterium_store', 5);
			$planet->updateAmount('missile_facility', 2);

			$planet->updateAmount('small_ship_cargo', 50);
			$planet->updateAmount('big_ship_cargo', 10);
			$planet->updateAmount('light_hunter', 50);
			$planet->updateAmount('heavy_hunter', 20);
			$planet->updateAmount('crusher', 10);
			$planet->updateAmount('battle_ship', 10);
			$planet->updateAmount('colonizer', 5);
			$planet->updateAmount('recycler', 5);
			$planet->updateAmount('spy_sonde', 5);
			$planet->updateAmount('bomber_ship', 5);
			$planet->updateAmount('solar_satelit', 10);
			$planet->updateAmount('dearth_star', 10);
			$planet->updateAmount('misil_launcher', 20);
			$planet->updateAmount('small_laser', 20);
			$planet->updateAmount('big_laser', 20);
			$planet->updateAmount('gauss_canyon', 20);
			$planet->updateAmount('small_protection_shield', 1);
			$planet->updateAmount('big_protection_shield', 1);
			$planet->updateAmount('interceptor_misil', 10);
			$planet->updateAmount('interplanetary_misil', 10);
		}
	}
}
