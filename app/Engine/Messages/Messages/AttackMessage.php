<?php

namespace App\Engine\Messages\Messages;

use App\Engine\Messages\MessageContract;
use App\Format;
use Illuminate\Support\Facades\Crypt;

class AttackMessage implements MessageContract
{
	public function format(array $message): string
	{
		$result  = '<div class="text-center">';
		$result .= '<a href="/rw/' . $message['report_id'] . '?signature=' . Crypt::encrypt($message['report_id']) . '" target="_blank">';
		$result .= '<span style="color:' . ($message['color'] ?? 'orange') . '">' . __('fleet_engine.sys_mess_attack_report') . ' [' . $message['galaxy'] . ":" . $message['system'] . ':' . $message['planet'] . ']</span></a>';
		$result .= '</div>';

		if (isset($message['lost'])) {
			$result .= '<div class="text-center mt-4">';
			$result .= '<div class="negative">' . __('fleet_engine.sys_perte_attaquant') . ': ' . Format::number($message['lost']['att']) . '</span><span class="positive">   ' . __('fleet_engine.sys_perte_defenseur') . ': ' . Format::number($message['lost']['def']) . '</div>';
			$result .= '<div>' . __('fleet_engine.sys_gain') . ' м: <span style="color:#adaead">' . Format::number($message['steal']['metal']) . '</span>, к: <span style="color:#ef51ef">' . Format::number($message['steal']['crystal']) . '</span>, д: <span style="color:#f77542">' . Format::number($message['steal']['deuterium']) . '</span></div>';
			$result .= '<div>' . __('fleet_engine.sys_debris') . ' м: <span style="color:#adaead">' . Format::number($message['debris']['metal']) . '</span>, к: <span style="color:#ef51ef">' . Format::number($message['debris']['crystal']) . '</span></div>';
			$result .= '</div>';
		}

		return $result;
	}
}
