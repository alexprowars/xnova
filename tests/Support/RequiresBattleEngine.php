<?php

namespace Tests\Support;

use FFI;

trait RequiresBattleEngine
{
	protected function requireBattleEngine(): void
	{
		if (!extension_loaded('ffi')) {
			$this->markTestSkipped('The battle engine requires the FFI extension.');
		}

		try {
			FFI::cdef('char* fight_battle_rounds(const char* input_json);', base_path('storage/libbattle_engine_ffi.so'));
		} catch (FFI\Exception $exception) {
			$this->markTestSkipped('The native battle engine is unavailable: ' . $exception->getMessage());
		}
	}
}