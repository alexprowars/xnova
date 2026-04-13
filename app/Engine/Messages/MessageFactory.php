<?php

namespace App\Engine\Messages;

class MessageFactory
{
	protected static array $messages = [
		'AcsRequest' => Types\AcsRequestMessage::class,
		'AcsFleetArrived' => Types\AcsFleetArrivedMessage::class,
		'AllianceMemberAccept' => Types\AllianceMemberAcceptMessage::class,
		'AllianceMemberReject' => Types\AllianceMemberRejectMessage::class,
		'FriendsRequest' => Types\FriendsRequestMessage::class,
		'MissionAttack' => Types\MissionAttackMessage::class,
		'MissionColonizationError' => Types\MissionColonizationErrorMessage::class,
		'MissionColonization' => Types\MissionColonizationMessage::class,
		'MissionColonizationMaxReached' => Types\MissionColonizationMaxReachedMessage::class,
		'MissionColonizationExist' => Types\MissionColonizationExistMessage::class,
		'MissionCreateBaseError' => Types\MissionCreateBaseErrorMessage::class,
		'MissionCreateBase' => Types\MissionCreateBaseMessage::class,
		'MissionCreateBaseMaxReached' => Types\MissionCreateBaseMaxReachedMessage::class,
		'MissionCreateBaseExist' => Types\MissionCreateBaseExistMessage::class,
		'MissionDestruction' => Types\MissionDestructionMessage::class,
		'MissionDestructionFailure' => Types\MissionDestructionFailureMessage::class,
		'MissionEspionage' => Types\MissionEspionageMessage::class,
		'MissionEspionageNotify' => Types\MissionEspionageNotifyMessage::class,
		'MissionExpeditionAttack' => Types\MissionExpeditionAttackMessage::class,
		'MissionExpeditionFoundShips' => Types\MissionExpeditionFoundShipsMessage::class,
		'MissionExpeditionFailed' => Types\MissionExpeditionFailedMessage::class,
		'MissionExpeditionLossFleet' => Types\MissionExpeditionLossFleetMessage::class,
		'MissionExpeditionDelay' => Types\MissionExpeditionDelayMessage::class,
		'MissionExpeditionGainCredits' => Types\MissionExpeditionGainCreditsMessage::class,
		'MissionExpeditionGainResources' => Types\MissionExpeditionGainResourcesMessage::class,
		'MissionExpeditionBattle' => Types\MissionExpeditionBattleMessage::class,
		'MissionMissileAttack' => Types\MissionMissileAttackMessage::class,
		'MissionRecycling' => Types\MissionRecyclingMessage::class,
		'MissionStay' => Types\MissionStayMessage::class,
		'MissionStayReturn' => Types\MissionStayReturnMessage::class,
		'MissionTransportArrived' => Types\MissionTransportArrivedMessage::class,
		'MissionTransportReceived' => Types\MissionTransportReceivedMessage::class,
		'NewLevel' => Types\NewLevelMessage::class,
		'QueueDestroyNotExist' => Types\QueueDestroyNotExistMessage::class,
		'QueueNoResources' => Types\QueueNoResourcesMessage::class,
		'SupportAnswer' => Types\SupportAnswerMessage::class,
		'Text' => Types\TextMessage::class,
	];

	public static function get(array $messages): ?AbstractMessage
	{
		if (empty($messages['type']) || !isset(self::$messages[$messages['type']])) {
			return null;
		}

		/** @var ?AbstractMessage $formatter */
		$formatter = self::$messages[$messages['type']];

		return new $formatter($messages['data'] ?? []);
	}
}
