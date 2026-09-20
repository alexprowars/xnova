<template>
	<UiPanel class="game-panel">
		<header class="start-heading">
			<span class="game-eyebrow">01 / 02</span>
			<h1>{{ $t('pages.start.main_info') }}</h1>
		</header>
		<form class="game-form" @submit.prevent="save">
			<label>
				{{ $t('pages.start.game_nickname') }}
				<input :class="{error: v$.name.$error}" name="name" maxlength="30" type="text" v-model="form.name">
			</label>
			<div v-if="v$.name.$error" class="game-errors">
				{{ $t('pages.auth.check_fields') }}
			</div>
			<h2 class="start-section-title">{{ $t('pages.start.game_avatar') }}</h2>
			<UiTabs :items="avatarTabs" :label="$t('pages.start.game_avatar')">
				<template #panel="{ item: { id: sex } }">
					<div class="start-avatar-grid">
						<label v-for="i in 8" :key="i" :class="{ 'is-selected': form.avatar === sex + '_' + i }">
							<input type="radio" :value="sex + '_' + i" v-model="form.avatar" :aria-label="$t(sex === 1 ? 'pages.start.male' : 'pages.start.female') + ' ' + i">
							<img :src="'/assets/images/faces/' + sex + '/' + i + 's.png'" alt="">
							<span class="start-choice-mark" aria-hidden="true">✓</span>
						</label>
					</div>
				</template>
			</UiTabs>
			<div v-for="(error, key) in form.errors" :key="key" class="game-errors">{{ error }}</div>
			<div class="game-actions">
				<UiButton type="submit" :disabled="!form.name || !form.avatar || form.processing">{{ $t('pages.start.continue') }} →</UiButton>
			</div>
		</form>
	</UiPanel>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';
	import { UiButton, UiPanel, UiTabs } from '~/components/UI';
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { useForm } from '@inertiajs/vue3';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';

	const { t } = useI18n();
	const avatarTabs = computed(() => [
		{ id: 1, label: t('pages.start.male') },
		{ id: 2, label: t('pages.start.female') },
	]);
	const state = useState();
	const user = computed(() => state.user);

	const form = useForm({
		name: user.value['name'],
		avatar: null,
	});

	const validations = {
		name: {
			required,
		}
	};

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function save() {
		if (form.processing) return;

		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/start', {
			preserveUrl: true,
		});
	}
</script>