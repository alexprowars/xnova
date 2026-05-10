<template>
	<div class="application" v-touch:swipe.left.right="swipe">
		<Header v-if="user && view['header']"/>
		<main>
			<MainMenu v-if="user && view['menu']" :active="sidebar === 'menu'" @toggle="sidebarToggle('menu')"/>
			<PlanetsList v-if="user && view['planets']" :active="sidebar === 'planet'" @toggle="sidebarToggle('planet')"/>
			<div class="main-content" v-touch:tap="tap">
				<PlanetPanel v-if="user && view['resources']"/>
				<div class="main-content-row">
					<MessagesRow v-for="message in state.messages" :type="message.type || ''" :text="message.text"/>
					<MessagesRow v-if="message" type="message" :text="message"/>
					<MessagesRow v-if="user?.vacation" type="warning" text="Включен режим отпуска! Функциональность игры ограничена."/>
					<MessagesRow v-if="user?.deleted_at" type="info" :text="'Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после ' + $formatDate(user.deleted_at, 'DD MMM YYYY HH:mm') + '. Выключить режим удаления можно в настройках игры.'"/>
					<slot/>
				</div>
			</div>
		</main>

		<Chat v-if="!isSSR() && user" :visible="!isChatPage && view['menu'] && view['chat']"/>

		<Footer v-if="user && view['header']"/>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { isMobile, isSSR } from '~/utils/helpers.js';
	import { ref, computed } from 'vue';
	import MessagesRow from '~/components/Layout/MessagesRow.vue';
	import Header from '~/components/Layout/Header.vue';
	import MainMenu from '~/components/Layout/MainMenu.vue';
	import PlanetsList from '~/components/Layout/PlanetsList.vue';
	import PlanetPanel from '~/components/Layout/PlanetPanel.vue';
	import Footer from '~/components/Layout/Footer.vue';
	import { usePage, router } from '@inertiajs/vue3';
	import Chat from '~/components/Chat.vue';

	const sidebar = ref('');
	const page = usePage();
	const state = useState();

	const props = defineProps({
		view: {
			type: Object,
			default: () => ({}),
		}
	});

	const message = ref(null);
	const user = computed(() => state.user);

	const isChatPage = computed(() => {
		return page.url.indexOf('/chat') !== -1;
	});

	router.on('navigate', () => {
		sidebar.value = '';
		message.value = null;
	});

	router.on('flash', (event) => {
		if (event.detail.flash.message) {
			message.value = event.detail.flash.message;
		}
	});

	const view = computed(() => {
		let views = Object.assign({
			header: true,
			footer: true,
			planets: true,
			menu: true,
			resources: true,
			chat: true,
		}, props.view || {});

		if (user && !user.value['options']['chatbox']) {
			views.chat = false;
		}

		return views;
	});

	function sidebarToggle (type) {
		if (sidebar.value === type) {
			sidebar.value = '';
		} else {
			sidebar.value = type;
		}
	}

	function swipe (direction, ev) {
		if (!isMobile()) {
			return;
		}

		if (ev.target.closest('.table-responsive') !== null) {
			return;
		}

		if (sidebar.value !== '') {
			sidebar.value = '';
			return;
		}

		if (direction === 'left') {
			sidebarToggle('planet');
		}

		if (direction === 'right') {
			sidebarToggle('menu');
		}
	}

	function tap () {
		if (!isMobile())
			return

		if (sidebar.value !== '') {
			sidebar.value = '';
		}
	}
</script>