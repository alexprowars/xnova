<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;
use App\Format;

class QueueNoResourcesMessage extends AbstractMessage
{
	protected string $type = 'QueueNoResources';

	public function getSubject(): ?string
	{
		return __('main.sys_buildlist');
	}

	public function render(): string
	{
		$message = __('buildings.queue_no_resources', [
			'building' => __('main.tech.' . $this->data['object']),
			'planet' => $this->data['planet']['name'],
			'coordinates' => Coordinates::fromArray($this->data['planet'])->getLink(),
		]) . '<br>' . __('buildings.queue_missing_resources') . '<br>';

		if (!empty($this->data['metal'])) {
			$message .= Format::number($this->data['metal']) . ' ' . __('main.res_plural.metal') . '<br>';
		}

		if (!empty($this->data['crystal'])) {
			$message .= Format::number($this->data['crystal']) . ' ' . __('main.res_plural.crystal') . '<br>';
		}

		if (!empty($this->data['deuterium'])) {
			$message .= Format::number($this->data['deuterium']) . ' ' . __('main.res_plural.deuterium') . '<br>';
		}

		if (!empty($this->data['energy'])) {
			$message .= Format::number($this->data['energy']) . ' ' . __('main.res_plural.energy') . '<br>';
		}

		return $message;
	}
}
