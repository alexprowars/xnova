<template>
	<div v-if="planet['units']['interceptor_misil'] || planet['units']['interplanetary_misil']" class="block">
		<div class="title">{{ $t('pages.info.missile.title') }}</div>
		<div class="content">
			<form method="post" action="" class="block-table" @submit.prevent="send">
				<div v-if="planet['units']['interceptor_misil']" class="grid grid-cols-2">
					<div class="th">{{ $t('tech.502') }}: {{ planet['units']['interceptor_misil'] }}</div>
					<div class="th"><Number v-model="interceptor"/></div>
				</div>
				<div v-if="planet['units']['interplanetary_misil']" class="grid grid-cols-2">
					<div class="th">{{ $t('tech.502') }}: {{ planet['units']['interplanetary_misil'] }}</div>
					<div class="th"><Number v-model="interplanetary"/></div>
				</div>
				<div v-if="interceptor > 0 || interplanetary > 0" class="grid">
					<div class="th">
						<button type="submit" class="button">{{ $t('pages.info.missile.destroy') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed, ref } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { useForm } from '@inertiajs/vue3';
	import Number from '~/components/Number.vue';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const { t } = useI18n();

	const props = defineProps({
		item: {
			type: Number,
		},
	});

	const state = useState();
	const planet = computed(() => state.planet);
	const interceptor = ref(0);
	const interplanetary = ref(0);

	function send() {
		useForm({
			interceptor: interceptor.value,
			interplanetary: interplanetary.value,
		})
		.post('/info/' + props.item + '/missiles', {
			onSuccess() {
				useSuccessNotification(t('pages.info.missile.destroyed'));

				interceptor.value = 0;
				interplanetary.value = 0;
			}
		});
	}
</script>