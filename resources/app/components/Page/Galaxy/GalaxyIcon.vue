<template>
	<component :is="icons[type]" aria-hidden="true"/>
</template>

<script setup>
	import MessageIcon from '~/images/icons/message.svg?component';
	import FriendIcon from '~/images/icons/user-add.svg?component';
	import MissileIcon from '~/images/icons/missile.svg?component';
	import SpyIcon from '~/images/icons/radar.svg?component';
	import AttackIcon from '~/images/icons/attack.svg?component';
	import HoldIcon from '~/images/icons/hold.svg?component';
	import DeployIcon from '~/images/icons/deploy.svg?component';
	import TransportIcon from '~/images/icons/transport.svg?component';
	import PlayerIcon from '~/images/icons/player.svg?component';
	import BookmarkIcon from '~/images/icons/bookmark.svg?component';
	import ColonizeIcon from '~/images/icons/colonize.svg?component';
	import SystemIcon from '~/images/icons/system.svg?component';
	import LeftIcon from '~/images/icons/left.svg?component';
	import RightIcon from '~/images/icons/right.svg?component';

	defineProps({
		type: String,
	});

	const icons = {
		message: MessageIcon,
		friend: FriendIcon,
		missile: MissileIcon,
		spy: SpyIcon,
		attack: AttackIcon,
		hold: HoldIcon,
		deploy: DeployIcon,
		transport: TransportIcon,
		player: PlayerIcon,
		bookmark: BookmarkIcon,
		colonize: ColonizeIcon,
		system: SystemIcon,
		left: LeftIcon,
		right: RightIcon,
	};
</script>
