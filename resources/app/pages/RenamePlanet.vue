<template>
	<Head :title="$t('pages.overview.rename.page_title')"/>
	<div class="page-overview-rename">
		<div class="block">
			<div class="title">{{ $t('pages.overview.rename.block_title') }}</div>
			<div class="content">
				<div class="block-table middle">
					<div class="grid grid-cols-3">
						<div class="th hidden sm:flex middle">{{ planet['coordinates']['galaxy'] }}:{{ planet['coordinates']['system'] }}:{{ planet['coordinates']['planet'] }}</div>
						<div class="th middle">{{ planet['name'] }}</div>
						<div class="th middle">
							<button type="button" class="button" @click.prevent="deletePlanet">{{ $t('pages.overview.rename.abandon_colony') }}</button>
						</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="th hidden sm:flex middle">{{ $t('pages.overview.rename.change_name_heading') }}</div>
						<div class="th middle"><input type="text" :placeholder="planet['name']" v-model="name" maxlength="20"></div>
						<div class="th middle"><button v-if="name" @click.prevent="changeName">{{ $t('pages.overview.rename.change_name_submit') }}</button></div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="type" class="block page-overview-planet-image">
			<div class="title">{{ $t('pages.overview.rename.background_title') }}</div>
			<div class="content p-2">
				<div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
					<div v-for="i in planetImages[type]">
						<input type="radio" v-model="image" :value="i" :id="'image_'+i">
						<label :for="'image_'+i">
							<img :src="'/assets/images/planeten/' + type + 'planet'+(i < 10 ? '0' : '')+i+'.jpg'" align="absmiddle" width="100%" alt="">
						</label>
					</div>
				</div>
				<div v-if="image > 0" class="grid">
					<div class="th text-center">
						<button @click.prevent="changeImage">{{ $t('pages.overview.rename.change_image_one_credit') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { computed, ref } from 'vue';
	import { Head, router, usePage } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useApiSubmit } from '~/composables/useApi.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { openConfirmModal } from '~/composables/useModals.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const { t } = useI18n();

	const page = usePage();
	const planet = computed(() => page.props.planet);

	const name = ref('');
	const image = ref(0);

	const planetImages = {
		trocken: 20,
		wuesten: 4,
		dschjungel: 19,
		normaltemp: 15,
		gas: 16,
		wasser: 18,
		eis: 20,
	};

	const type = computed(() => {
		for (let type in planetImages) {
			if (planet.value.image.includes(type)) {
				return type;
			}
		}

		return null;
	});

	function changeName() {
		useApiSubmit('/planet/rename', {
			name: name.value
		}, () => {
			useSuccessNotification(t('pages.overview.rename.toast_renamed'));

			router.visit('/overview');
		});
	}

	function changeImage() {
		useApiSubmit('/planet/image', {
			image: image.value
		}, () => {
			useSuccessNotification(t('pages.overview.rename.toast_image_changed'));

			router.visit('/overview');
		});
	}

	function deletePlanet() {
		openConfirmModal(
			null,
			t('pages.overview.rename.confirm_abandon'),
			[{
				title: t('pages.overview.rename.modal_close'),
			}, {
				title: t('pages.overview.rename.modal_confirm_delete'),
				handler() {
					useApiSubmit('/planet/delete', {
						_method: 'DELETE',
					}, async () => {
						useSuccessNotification(t('pages.overview.rename.toast_colony_removed'));

						router.reload();
					});
				}
			}]
		);
	}
</script>