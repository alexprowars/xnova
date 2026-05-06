<template>
	<form class="block-table text-center mb-1.5" action="" method="post" @submit.prevent="send">
		<div class="grid">
			<div class="c">
				{{ $t('pages.missile_attack.title', [page['galaxy'], page['system'], planet]) }}
			</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">
				{{ $t('pages.missile_attack.rockets', [currentPlanet['units']['interplanetary_misil']]) }}:
				<input type="number" style="width:25%" min="1" :max="currentPlanet['units']['interplanetary_misil']" v-model.number="count">
			</div>
			<div class="th">
				{{ $t('pages.missile_attack.target') }}:
				<select name="target" v-model="target">
					<option value="all">{{ $t('pages.missile_attack.target_all') }}</option>
					<option value="401">{{ $t('tech.401') }}</option>
					<option value="402">{{ $t('tech.402') }}</option>
					<option value="403">{{ $t('tech.403') }}</option>
					<option value="404">{{ $t('tech.404') }}</option>
					<option value="405">{{ $t('tech.405') }}</option>
					<option value="406">{{ $t('tech.406') }}</option>
					<option value="407">{{ $t('tech.407') }}</option>
					<option value="408">{{ $t('tech.408') }}</option>
				</select>
			</div>
		</div>
		<div class="grid">
			<div class="c">
				<button type="submit">{{ $t('pages.missile_attack.submit') }}</button>
				<button @click.prevent="$emit('close')">{{ $t('pages.missile_attack.cancel') }}</button>
			</div>
		</div>
	</form>
</template>

<script setup>
	import { computed, ref } from 'vue';
	import { router, usePage } from '@inertiajs/vue3';
	import { useErrorNotification } from '../../../composables/useToast.js';
	import { useApiPost } from '../../../composables/useApi.js';

	const props = defineProps({
		page: {
			type: Object
		},
		planet: {
			type: Number
		}
	});

	const page = usePage();
	const currentPlanet = computed(() => page.props.planet);

	const target = ref('all');
	const count = ref(currentPlanet.value['units']['interplanetary_misil'] || 0);

	async function send() {
		try {
			await useApiPost('/rocket', {
				galaxy: props.page['galaxy'],
				system: props.page['system'],
				planet: props.planet,
				count: count.value,
				target: target.value,
			});

			router.reload();
		} catch (e) {
			useErrorNotification(e.message);
		}
	}
</script>