<template>
	<div class="block-table w-full mx-auto max-w-50 c">
		<select v-model="value" style="width:100%">
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