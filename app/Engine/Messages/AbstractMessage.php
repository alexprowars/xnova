<?php

namespace App\Engine\Messages;

abstract class AbstractMessage implements MessageContract
{
	protected string $type;

	public function __construct(protected array $data = [])
	{
	}

	public function getSubject(): ?string
	{
		return null;
	}

	public function toArray(): array
	{
		return [
			'type' => $this->type,
			'data' => $this->data,
		];
	}
}
