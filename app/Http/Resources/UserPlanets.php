<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Planet
 */
class UserPlanets extends JsonResource
{
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'image' => $this->image,
			'destroy' => $this->destruyed > 0,
		] + $this->getCoordinates()->toArray();
	}
}
