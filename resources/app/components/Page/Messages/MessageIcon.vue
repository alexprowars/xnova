<template>
	<component :is="icons[name]" stroke-width="1.6" aria-hidden="true"/>
</template>

<script setup>
	import MailIcon from '~/images/icons/message.svg?component';
	import ReplyIcon from '~/images/icons/reply.svg?component';
	import QuoteIcon from '~/images/icons/editor/quote.svg?component';
	import FlagIcon from '~/images/icons/flag.svg?component';
	import TrashIcon from '~/images/icons/trash.svg?component';
	import SendIcon from '~/images/icons/send.svg?component';
	import UserIcon from '~/images/icons/player.svg?component';
	import BackIcon from '~/images/icons/arrow-left.svg?component';

	defineProps({
		name: String,
	});

	const icons = {
		mail: MailIcon,
		reply: ReplyIcon,
		quote: QuoteIcon,
		flag: FlagIcon,
		trash: TrashIcon,
		send: SendIcon,
		user: UserIcon,
		back: BackIcon,
	};
</script>
