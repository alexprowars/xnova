<template>
	<Head :title="$t('pages.overview.rename.page_title')"/>
	<div class="page-overview-rename">
		<Link href="/overview" class="planet-settings-back">
			<svg
				viewBox="0 0 24 24"
				fill="none"
				stroke="currentColor"
				stroke-width="1.6"
				stroke-linecap="round"
				stroke-linejoin="round"
				aria-hidden="true"
			>
				<path d="m10 5-7 7 7 7M3 12h18"/>
			</svg>
			{{ $t('pages.overview.rename.back') }}
		</Link>
		<header class="planet-settings-heading">
			<img :src="'/assets/images/planeten/' + planet.image + '.jpg'" alt="" width="64" height="64">
			<div>
				<h1>{{ $t('pages.overview.rename.block_title') }}</h1>
				<div class="planet-settings-identity">
					<strong>{{ planet.name }}</strong>
					<span>[{{ planet.coordinates.galaxy }}:{{ planet.coordinates.system }}:{{ planet.coordinates.planet }}]</span>
				</div>
			</div>
		</header>
		<section class="planet-settings-panel">
			<h2>{{ $t('pages.overview.rename.change_name_heading') }}</h2>
			<form class="planet-name-form" @submit.prevent="changeName">
				<div class="planet-name-field">
					<label for="planet-name">{{ $t('pages.overview.rename.new_name') }}</label>
					<input
						id="planet-name"
						name="name"
						type="text"
						:placeholder="planet.name"
						v-model="nameForm.name"
						minlength="2"
						maxlength="19"
						required
						:class="{ 'is-invalid': nameForm.errors.name }"
						aria-describedby="planet-name-hint"
					>
					<span id="planet-name-hint" class="planet-settings-hint">{{ $t('pages.overview.rename.name_hint') }}</span>
				</div>
				<button type="submit" class="button" :disabled="!nameForm.name.trim() || nameForm.processing">
					{{ $t('pages.overview.rename.change_name_submit') }}
				</button>
				<div v-if="Object.keys(nameForm.errors).length" class="planet-settings-errors" role="alert">
					<span v-for="(error, key) in nameForm.errors" :key="key">{{ error }}</span>
				</div>
			</form>
		</section>
		<section v-if="type" class="planet-settings-panel">
			<h2>{{ $t('pages.overview.rename.background_title') }}</h2>
			<form @submit.prevent="changeImage">
				<fieldset class="planet-image-grid">
					<legend class="sr-only">{{ $t('pages.overview.rename.background_title') }}</legend>
					<label
						v-for="i in planetImages[type]"
						:key="imageName(i)"
						class="planet-image-option"
						:class="{ 'is-selected': imageForm.image === i }"
					>
						<input
							type="radio"
							name="planet-image"
							v-model="imageForm.image"
							:value="i"
							:aria-label="$t('pages.overview.rename.image_variant', { number: i })"
							:aria-describedby="imageName(i) === planet.image ? 'planet-image-current' : undefined"
						>
						<img :src="'/assets/images/planeten/' + imageName(i) + '.jpg'" alt="" width="100" height="100" loading="lazy">
						<span class="planet-image-caption">
							<span>{{ $t('pages.overview.rename.image_variant', { number: i }) }}</span>
							<svg
								v-if="imageForm.image === i"
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
								aria-hidden="true"
							>
								<path d="m5 12 4 4L19 6"/>
							</svg>
						</span>
						<span v-if="imageName(i) === planet.image" id="planet-image-current" class="planet-image-current">
							{{ $t('pages.overview.rename.current_image') }}
						</span>
					</label>
				</fieldset>
				<div v-if="Object.keys(imageForm.errors).length" class="planet-settings-errors planet-image-errors" role="alert">
					<span v-for="(error, key) in imageForm.errors" :key="key">{{ error }}</span>
				</div>
				<div class="planet-image-actions">
					<span class="planet-settings-hint">{{ $t('pages.overview.rename.image_hint') }}</span>
					<button
						type="submit"
						class="button"
						:disabled="!imageForm.image || imageName(imageForm.image) === planet.image || imageForm.processing"
					>
						{{ $t('pages.overview.rename.change_image_one_credit') }}
					</button>
				</div>
			</form>
		</section>
		<section class="planet-abandon">
			<div>
				<h2>{{ $t('pages.overview.rename.abandon_colony') }}</h2>
				<span class="planet-settings-hint">{{ $t('pages.overview.rename.abandon_hint') }}</span>
			</div>
			<button type="button" class="button is-danger" :disabled="deleteForm.processing" @click="deletePlanet">
				<svg
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="1.6"
					stroke-linecap="round"
					stroke-linejoin="round"
					aria-hidden="true"
				>
					<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>
				</svg>
				{{ $t('pages.overview.rename.abandon_colony') }}
			</button>
			<div v-if="Object.keys(deleteForm.errors).length" class="planet-settings-errors" role="alert">
				<span v-for="(error, key) in deleteForm.errors" :key="key">{{ error }}</span>
			</div>
		</section>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
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

	const state = useState();
	const planet = computed(() => state.planet);

	const nameForm = useForm({ name: '' });
	const imageForm = useForm({ image: 0 });
	const deleteForm = useForm({});

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

	function imageName(index) {
		return type.value + 'planet' + String(index).padStart(2, '0');
	}

	function changeName() {
		if (!nameForm.name.trim() || nameForm.processing) return;

		nameForm.post('/planet/rename', {
			preserveUrl: true,
			onSuccess() {
				useSuccessNotification(t('pages.overview.rename.toast_renamed'));
			}
		});
	}

	function changeImage() {
		if (!imageForm.image || imageName(imageForm.image) === planet.value.image || imageForm.processing) return;

		imageForm.post('/planet/image', {
			preserveUrl: true,
			onSuccess() {
				useSuccessNotification(t('pages.overview.rename.toast_image_changed'));
			}
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
				class: 'is-danger',
				handler() {
					deleteForm.delete('/planet/delete', {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							useSuccessNotification(t('pages.overview.rename.toast_colony_removed'));
						}
					});
				}
			}]
		);
	}
</script>