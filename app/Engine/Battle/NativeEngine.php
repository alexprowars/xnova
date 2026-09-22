<?php

namespace App\Engine\Battle;

use FFI;
use RuntimeException;

class NativeEngine
{
	private FFI $ffi;

	public function __construct(string $library)
	{
		$this->ffi = FFI::cdef(
			'char* fight_battle_rounds(const char* input_json); void free_battle_result(char* ptr);',
			$library
		);
	}

	public function fight(array $input): array
	{
		$inputJson = json_encode($input, JSON_THROW_ON_ERROR);

		/** @var FFI\CData|null $outputPtr */
		/** @phpstan-ignore-next-line */
		$outputPtr = $this->ffi->fight_battle_rounds($inputJson);

		if ($outputPtr === null || FFI::isNull($outputPtr)) {
			throw new RuntimeException('The battle engine returned a null result.');
		}

		try {
			$output = FFI::string($outputPtr);
		} finally {
			// Rust owns this allocation; PHP must release it through the same allocator.
			/** @phpstan-ignore-next-line */
			$this->ffi->free_battle_result($outputPtr);
		}

		$result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($result) || !isset($result['rounds']) || !is_array($result['rounds'])) {
			throw new RuntimeException('Invalid battle engine result: ' . ($result['error'] ?? 'missing rounds'));
		}

		return $result;
	}
}