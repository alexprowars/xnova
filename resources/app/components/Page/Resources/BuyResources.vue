<template>
	<div v-if="visible" class="block">
		<div class="title">
			{{ $t('pages.resources.resources_buy') }}
		</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid">
					<div class="th middle flex flex-col gap-2">
						<i18n-t keypath="pages.resources.resources_buy_info" tag="div" scope="global">
							<template v-slot:metal>
								<Colored :value="item['metal'] || 0"/>
							</template><template v-slot:crystal>
								<Colored :value="item['crystal'] || 0"/>
							</template><template v-slot:deuterium>
								<Colored :value="item['deuterium'] || 0"/>
							</template>
						</i18n-t>
						<span v-if="!item['time']">
							<a @click.prevent="buyResources" class="button">{{ $t('pages.resources.resources_buy_button') }}</a>
						</span>
						<span v-else>
							{{ $t('pages.resources.resources_buy_timeout') }}
							<br>
							{{ $formatTime(item['time']) }}
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import Colored from '~/components/Colored.vue';
	import { useI18n } from 'vue-i18n';
	import { useForm, usePage } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';

	defineProps({
		item: Object,
	});

	const { t } = useI18n();
	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);

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