<?php

namespace App\Engine\Enums;

enum AllianceAccess: string
{
	case CAN_WATCH_MEMBERLIST_STATUS = 'onlinestatus';
	case CAN_WATCH_MEMBERLIST = 'memberlist';
	case CHAT_ACCESS = 'chat';
	case CAN_KICK = 'kick';
	case CAN_EDIT_RIGHTS = 'rights';
	case CAN_DELETE_ALLIANCE = 'delete';
	case CAN_ACCEPT = 'accept';
	case ADMIN_ACCESS = 'admin';
	case DIPLOMACY_ACCESS = 'diplomacy';
	case PLANET_ACCESS = 'planet';
	case REQUEST_ACCESS = 'request';
}
