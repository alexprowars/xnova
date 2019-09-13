<?php

namespace Xnova\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet;
use Xnova\User;

class Context
{
	private $_user;
	private $_planet;

	public function __construct (?User $user, ?Planet $planet = null)
	{
		$this->_user = $user;
		$this->_planet = $planet;
	}

	public function getUser (): User
	{
		return $this->_user;
	}

	public function getPlanet (): Planet
	{
		if (!$this->_planet && $this->_user)
			$this->_planet = $this->_user->getCurrentPlanet(true);

		if (!$this->_planet)
			throw new Exception('planet not found in context');

		return $this->_planet;
	}
}