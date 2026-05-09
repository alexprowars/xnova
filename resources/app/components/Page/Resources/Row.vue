<template>
	<tr>
		<td class="th text-left" nowrap>
			<InfoPopup :id="item['id']" :title="$t('tech.' + item['id'])">
				{{ $t('tech.' + item['id']) }}
			</InfoPopup>
		</td>
		<td class="th text-center">
			<Colored :value="item['level']"/>
		</td>
		<td class="th text-center">
			{{ item['bonus'] }}%
		</td>
		<td class="th text-center" v-for="res in resources">
			<Colored :value="item['resources'][res]"/>
		</td>
		<td class="th text-center">
			<Colored :value="item['resources']['energy']"/>
		</td>
		<td class="th text-center">
			<select v-if="!isVacation" v-model="item['factor']">
				<option v-for="j in [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]" :value="j">{{ j * 10 }}%</option>
			</select>
			<span v-else>{{ item['factor'] * 10 }}%</span>
		</td>
	</tr>
</template>

<script setup>
	import InfoPopup from '~/components/Page/Info/Popup.vue';
	import Colored from '~/components/Colored.vue';
	import { usePage } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineProps({
		item: {
			type: Object
		},
		resources: {
			type: Array
		}
	});

	const page = usePage();
	const isVacation = computed(() => page.props.user?.vacation !== null);
</script>