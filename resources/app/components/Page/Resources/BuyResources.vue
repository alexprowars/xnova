<template>
	<div v-if="visible" class="block">
		<div class="title">{{ $t('pages.resources.resources_buy') }}</div>
		<div class="content resources-buy-content is-padded">
			<i18n-t keypath="pages.resources.resources_buy_info" tag="div" scope="global" class="resources-buy-description">
				<template #metal>
					<Colored :value="item.metal || 0"/>
				</template>
				<template #crystal>
					<Colored :value="item.crystal || 0"/>
				</template>
				<template #deuterium>
					<Colored :value="item.deuterium || 0"/>
				</template>
			</i18n-t>
			<button v-if="!item.time" type="button" @click="buyResources" class="button">
				{{ $t('pages.resources.resources_buy_button') }}
			</button>
			<div v-else class="resources-buy-timeout">
				<span>{{ $t('pages.resources.resources_buy_timeout') }}</span>
				<strong>{{ $formatTime(item.time) }}</strong>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import Colored from '~/components/Colored.vue';
	import { useI18n } from 'vue-i18n';
	import { useForm } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';

	defineProps({
		item: Object,
	});

	const { t } = useI18n();
	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);

	const visible = computed(() => {
		return planet.value.type === 1 && user.value.vacation === null;
	});

	function buyResources() {
		openConfirmModal(
			null,
			t('pages.resources.resources_buy_confirm'),
			[{
				title: t('pages.resources.resources_buy_confirm_no'),
			}, {
				title: t('pages.resources.resources_buy_confirm_yes'),
				async handler() {
					useForm().post('/resources/buy');
				}
			}]
		);
	}
</script>