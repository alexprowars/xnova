<template>
	<UiPanel class="game-panel">
		<header class="start-heading">
			<span class="game-eyebrow">03 / 03</span>
			<h2>{{ $t('pages.start.game_avatar') }}</h2>
		</header>
		<form class="game-form" @submit.prevent="finish">
			<UiTabs v-model="activeTab" :items="avatarTabs" :label="$t('pages.start.game_avatar')">
				<template #panel="{ item: { id: sex } }">
					<div class="start-avatar-grid">
						<label v-for="i in 8" :key="i" :class="{ 'is-selected': form.avatar === sex + '_' + i }">
							<input type="radio" :value="sex + '_' + i" v-model="form.avatar" :aria-label="$t(sex === 1 ? 'pages.start.male' : 'pages.start.female') + ' ' + i" @change="form.clearErrors('avatar')">
							<img :src="'/assets/images/faces/' + sex + '/' + i + 's.png'" alt="">
							<span class="start-choice-mark" aria-hidden="true">✓</span>
						</label>
					</div>
				</template>
			</UiTabs>
			<div v-if="form.errors.avatar" class="game-errors">{{ form.errors.avatar }}</div>
			<div class="game-actions">
				<UiButton variant="secondary" :disabled="form.processing" @click="emit('back')">← {{ $t('pages.start.back') }}</UiButton>
				<UiButton type="submit" :disabled="!form.avatar || form.processing">{{ $t('pages.start.finish') }} →</UiButton>
			</div>
		</form>
	</UiPanel>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';
	import { UiButton, UiPanel, UiTabs } from '~/components/UI';
	import { computed, ref } from 'vue';

	const { t } = useI18n();
	const avatarTabs = computed(() => [
		{ id: 1, label: t('pages.start.male') },
		{ id: 2, label: t('pages.start.female') },
	]);

	const props = defineProps({
		form: { type: Object, required: true },
	});
	const emit = defineEmits(['back', 'finish']);
	const activeTab = ref(props.form.avatar?.startsWith('2_') ? 2 : 1);

	function finish() {
		if (props.form.processing || !props.form.avatar) return;

		emit('finish');
	}
</script>