<template>
	<Head :title="$t('pages.contacts.page_title')"/>
	<div class="game-page page-contacts">
		<UiHeading :title="$t('pages.contacts.page_title')" class="game-heading"/>
		<p class="contacts-intro">{{ $t('pages.contacts.intro_hint') }}</p>
		<UiPanel v-for="item in page.items" :key="item.id" class="game-panel contact-card">
			<header>
				<div>
					<h2>{{ item.name }}</h2>
					<span class="contact-role">{{ item.role }}</span>
				</div>
				<div class="contact-actions">
					<UiButton v-if="state.user" :as="SendMessagePopup" :id="item.id">
						{{ $t('pages.contacts.write_message') }}
					</UiButton>
					<UiButton as="a" variant="secondary" :href="'mailto:' + item.email">{{ item.email }}</UiButton>
				</div>
			</header>
			<div v-if="item.about" class="game-prose">
				<TextViewer :text="item.about"/>
			</div>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiHeading, UiPanel } from '~/components/UI';
	import { Head } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import SendMessagePopup from '~/components/Page/Messages/SendMessagePopup.vue';
	import useState from '~/composables/useState.js';

	const state = useState();

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});
</script>