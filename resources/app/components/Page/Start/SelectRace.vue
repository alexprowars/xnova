<template>
	<div class="block start race">
		<div class="title">{{ $t('pages.start.race_selection') }}</div>
		<div class="content">
			<div class="block-table">
				<div class="grid grid-cols-2">
					<div v-for="(race_id, index) in Object.keys($tm('races'))" class="th">
						<div class="race-item" :class="{ selected: form.race === race_id }" @click="select(race_id)">
							<img :src="'/assets/images/skin/race' + race_id + '.gif'" alt=""><br>
							<h3>{{ $t('races.' + race_id) }}</h3>
							<span v-html="$t('info.' + (701 + index))"></span>
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="th">
						<button class="button" @click.prevent="save">{{ $t('pages.start.continue') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';

	const form = useForm({
		race: null,
	});

	function select(val) {
		form.race = val;
	}

	async function save() {
		form.post('/start/race', {
			preserveUrl: true,
		});
	}
</script>
