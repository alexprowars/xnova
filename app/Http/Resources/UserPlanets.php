<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Planet
 */
class UserPlanets extends JsonResource
{
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'image' => $this->image,
			'destroy' => !empty($this->destruyed),
		] + $this->getCoordinates()->toArray();
	}
}
