<template>
	<div class="block">
		<div class="title">{{ $t('pages.info.alliance.title') }}</div>
		<div class="content">
			<form method="post" action="" class="block-table" @submit.prevent="send">
				<div class="grid">
					<div class="th">{{ $t('pages.info.alliance.fleets_on_hold') }}</div>
				</div>
				<div class="grid">
					<div class="th">
						<select name="fleet" v-model="fleet">
							<option :value="0">-</option>
							<option v-for="fleet in data['fleets']" :value="fleet['id']">[{{ fleet['galaxy'] }}:{{ fleet['system'] }}:{{ fleet['planet'] }}] {{ fleet['name']}}</option>
						</select>
					</div>
				</div>
				<div v-if="fleet" class="grid">
					<div class="th">
						<button type="submit">{{ $t('pages.info.alliance.send') }} {{ data['cost'] }} {{ $t('pages.info.alliance.deuterium') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useForm } from '@inertiajs/vue3';

	const { t } = useI18n();

	const props = defineProps({
		data: Object
	});

	const fleet = ref(0);

	function send() {
		useForm({
			fleetId: fleet.value,
		})
		.post('/info/' + props.item + '/alliance', {
			onSuccess() {
				useSuccessNotification(t('pages.info.alliance.sent'));
			}
		});
	}
</script>