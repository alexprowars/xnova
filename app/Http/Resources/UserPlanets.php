<?php

namespace Xnova\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Xnova\Planet
 */
class UserPlanets extends JsonResource
{
	public function toArray($request)
	{
		return [
			'id' => (int) $this->id,
			'name' => $this->name,
			'image' => $this->image,
			'g' => (int) $this->galaxy,
			's' => (int) $this->system,
			'p' => (int) $this->planet,
			't' => (int) $this->planet_type,
			'destroy' => $this->destruyed > 0,
		];
	}
}
