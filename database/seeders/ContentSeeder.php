<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContentSeeder extends Seeder
{
	public function run()
	{
		DB::transaction(function () {
			$pdo = DB::connection()->getPdo();

			foreach (File::lines(database_path('contents.sql')) as $statement) {
				if (!is_string($statement) || trim($statement) === '') {
					continue;
				}

				$pdo->exec($statement);
			}
		});
	}
}
