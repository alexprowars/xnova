<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Responce extends JsonResource
{
	public function toArray($request)
	{
		return [
			'data' => $this->resource,
			'error' => null,
			'messages' => [],
		];
	}
}
