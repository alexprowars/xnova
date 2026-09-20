<template>
	<component :is="icons[code]"/>
</template>

<script setup>
	import MetalIcon from '~/images/icons/resources/metal.svg?component';
	import CrystalIcon from '~/images/icons/resources/crystal.svg?component';
	import DeuteriumIcon from '~/images/icons/resources/deuterium.svg?component';
	import EnergyIcon from '~/images/icons/resources/energy.svg?component';
	import CreditsIcon from '~/images/icons/resources/credits.svg?component';

	defineProps({
		code: {
			type: String,
			required: true,
		},
	});

	const icons = {
		metal: MetalIcon,
		crystal: CrystalIcon,
		deuterium: DeuteriumIcon,
		energy: EnergyIcon,
		credits: CreditsIcon,
	};
</script>