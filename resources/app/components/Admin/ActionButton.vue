<template>
	<Popper :content="label" popper-class="admin-popper" :delay-duration="0">
		<component
			:is="href ? Link : 'button'"
			v-bind="$attrs"
			:href="href"
			:type="href ? undefined : 'button'"
			:aria-label="label"
			class="admin-button"
			:class="{ 'admin-icon-button': compact }"
		>
			<component :is="icons[icon]" class="admin-action-icon" aria-hidden="true" />
			<span v-if="!compact">{{ label }}</span>
		</component>
	</Popper>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import Popper from '~/components/Popper.vue';
	import EditIcon from '~/images/icons/edit.svg?component';
	import DeleteIcon from '~/images/icons/trash.svg?component';
	import LockIcon from '~/images/icons/lock.svg?component';
	import CheckIcon from '~/images/icons/check.svg?component';
	import ReturnIcon from '~/images/icons/reply.svg?component';
	import SendIcon from '~/images/icons/send.svg?component';
	import HoldIcon from '~/images/icons/hold.svg?component';
	import FlagIcon from '~/images/icons/flag.svg?component';
	import RankIcon from '~/images/icons/trophy.svg?component';
	import CloseIcon from '~/images/icons/close.svg?component';

	defineOptions({ inheritAttrs: false });
	defineProps({
		icon: String,
		label: String,
		href: String,
		compact: { type: Boolean, default: true },
	});

	const icons = {
		edit: EditIcon,
		delete: DeleteIcon,
		ban: LockIcon,
		unban: CheckIcon,
		vacation: HoldIcon,
		accelerate: SendIcon,
		return: ReturnIcon,
		complete: CheckIcon,
		leader: FlagIcon,
		rank: RankIcon,
		close: CloseIcon,
	};
</script>
