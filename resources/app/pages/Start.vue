<template>
	<Head :title="$t('pages.start.title')"/>
	<div class="game-page page-start">
		<header class="start-welcome">
			<span class="start-welcome-brand">XNOVA</span>
			<h1>{{ $t('pages.start.welcome_title') }}</h1>
			<p>{{ $t('pages.start.welcome_description') }}</p>
		</header>
		<Profile v-if="step === 1" :form="form" @next="step = 2"/>
		<SelectRace v-else-if="step === 2" :form="form" @back="step = 1" @next="step = 3"/>
		<SelectAvatar v-else-if="step === 3" :form="form" @back="step = 2" @finish="save"/>
	</div>
</template>

<script setup>
	import Profile from '~/components/Page/Start/Profile.vue';
	import SelectRace from '~/components/Page/Start/SelectRace.vue';
	import SelectAvatar from '~/components/Page/Start/SelectAvatar.vue';
	import useState from '~/composables/useState.js';
	import { Head, useForm } from '@inertiajs/vue3';
	import { nextTick, onMounted, ref } from 'vue';
	import { visitModal } from '@inertiaui/modal-vue';

	defineOptions({
		layout: {
			view: {
				header: false,
				menu: false,
				resources: false,
				planets: false,
			}
		}
	});

	const state = useState();
	const step = ref(1);
	const form = useForm({
		name: state.user.name,
		locale: state.user.locale || state.locale,
		race: null,
		avatar: null,
	});

	onMounted(() => {
		nextTick(() => {
			visitModal('/content/welcome');
		});
	});

	function save() {
		if (form.processing) {
			return;
		}

		form.post('/start', {
			onError: (errors) => {
				if (errors.name || errors.locale) {
					step.value = 1;
				} else if (errors.race) {
					step.value = 2;
				}
			},
		});
	}
</script>