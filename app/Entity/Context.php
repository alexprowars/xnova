<?php

namespace Xnova\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet;
use Xnova\User;

class Context
{
	private $user;
	private $planet;

	public function __construct(?User $user, ?Planet $planet = null)
	{
		$this->user = $user;
		$this->planet = $planet;
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getPlanet(): Planet
	{
		if (!$this->planet && $this->user) {
			$this->planet = $this->user->getCurrentPlanet(true);
		}

		if (!$this->planet) {
			throw new Exception('planet not found in context');
		}

		return $this->planet;
	}
}
