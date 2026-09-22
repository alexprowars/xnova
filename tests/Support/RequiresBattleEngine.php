<?php

namespace Tests\Support;

use App\Engine\Battle\NativeEngine;
use FFI;

trait RequiresBattleEngine
{
	protected function requireBattleEngine(): void
	{
		if (!extension_loaded('ffi')) {
			$this->markTestSkipped('The battle engine requires the FFI extension.');
		}

		try {
			new NativeEngine(config('game.combat.library'));
		} catch (FFI\Exception $exception) {
			$this->markTestSkipped('The native battle engine is unavailable: ' . $exception->getMessage());
		}
	}
}