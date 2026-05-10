<template>
	<Head :title="$t('pages.referrals.head_title')"/>
	<div class="page-referrals">
		<div v-if="page.items.length > 0" class="block">
			<div class="title">{{ $t('pages.referrals.recruited_players_title') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.referrals.table_col_username') }}</div>
						<div class="c">{{ $t('pages.referrals.table_col_registered_at') }}</div>
						<div class="c">{{ $t('pages.referrals.table_col_development') }}</div>
					</div>
					<div class="grid grid-cols-3" v-for="item in page.items">
						<div class="th">
							<Link :href="'/players/' + item['id']">{{ item['username'] }}</Link>
						</div>
						<div class="th">{{ $formatDate(item['created_at'], 'DD MMM YYYY HH:mm:ss') }}</div>
						<div class="th">{{ $t('pages.referrals.levels_short', { miner: item['lvl_minier'], raid: item['lvl_raid'] }) }}</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="page.you !== undefined" class="block">
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-2">
						<div class="th">{{ $t('pages.referrals.referred_by_caption') }}</div>
						<div class="th">
							<Link :href="'/players/' + page.you['id']">{{ page.you['username'] }}</Link>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="content">
				<div class="block-table text-center">
					<div class="grid">
						<div class="th" style="padding:15px;">
							{{ $t('pages.referrals.share_project_prompt') }}<br><br>

							<div class="yashare-auto-init"
								data-yashareL10n="ru"
								data-yashareTheme="counter"
								data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus"
								:data-yashareLink="host + '/?' + user.id"
								data-yashareTitle=""
							></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="block">
			<div class="title">{{ $t('pages.referrals.userbar_title') }}</div>
			<div class="content block-table text-center">
				<div class="grid">
					<div class="flex justify-center my-4">
						<img :src="'/userbar' + user.id + '.jpg'" :alt="$t('pages.referrals.userbar_title')">
					</div>
					<div class="mt-2">
						{{ $t('pages.referrals.embed_html_label') }}
						<input style="width:100%" type="text" :value="html">
					</div>
					<div class="mt-2">
						{{ $t('pages.referrals.embed_bb_label') }}
						<input style="width:100%" type="text" :value="'[url=' + host + '/?' + user.id + '][img]' + host + '/userbar' + user.id + '.jpg[/img][/url]'">
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { Head, Link } from '@inertiajs/vue3';
	import { useScriptTag } from '@vueuse/core';

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

	const state = useState();
	const user = computed(() => state.user);
	const host = computed(() => import.meta.env.VITE_APP_URL || '');

	useScriptTag(
		'https://yandex.st/share/share.js',
		() => {},
		{
			defer: true,
			async: true,
		},
	);

	const html = computed(() => {
		return '<a href="' + host.value + '/?' + user.value.id + '"><img src="' + host.value + '/userbar' + user.value.id + '.jpg"></a>'
	});
</script>