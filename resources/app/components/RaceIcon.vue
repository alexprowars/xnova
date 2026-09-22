<template>
	<component v-if="icons[code]" :is="icons[code]"/>
</template>

<script setup>
	import ConfederationIcon from '~/images/icons/races/confederation.svg?component';
	import BionicsIcon from '~/images/icons/races/bionics.svg?component';
	import CylonsIcon from '~/images/icons/races/cylons.svg?component';
	import AncientsIcon from '~/images/icons/races/ancients.svg?component';

	defineProps({
		code: {
			type: [Number, String],
			required: true,
		},
	});

	const icons = {
		1: ConfederationIcon,
		2: BionicsIcon,
		3: CylonsIcon,
		4: AncientsIcon,
	};
</script>