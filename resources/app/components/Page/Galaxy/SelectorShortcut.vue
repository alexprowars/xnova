<template>
	<div>
		<select id="galaxy-shortcut" v-model="value">
			<option :value="null">{{ $t('pages.galaxy.selector.select') }}</option>
			<option v-for="item in items" :value="item">
				{{ item.name }} [{{ item.galaxy }}:{{ item.system }}:{{ item.planet }}]
			</option>
		</select>
	</div>
</template>

<script setup>
	defineProps({
		items: {
			type: Array,
			default: []
		},
		galaxy: {
			type: Number,
			default: 1
		},
		system: {
			type: Number,
			default: 1
		},
	});

	const value = defineModel();
</script>