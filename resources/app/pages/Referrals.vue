<template>
	<Head :title="$t('pages.referrals.head_title')"/>
	<div class="page-referrals">
		<header class="referrals-heading">
			<h1>{{ $t('pages.referrals.head_title') }}</h1>
			<span>{{ $t('pages.referrals.total_players', { count: page.items.length }) }}</span>
		</header>
		<section class="referrals-panel">
			<h2>{{ $t('pages.referrals.invite_title') }}</h2>
			<div class="referrals-panel-content">
				<p class="referrals-description">{{ $t('pages.referrals.share_project_prompt') }}</p>
				<CopyField id="referral-link" :label="$t('pages.referrals.invite_link')" :value="referralUrl"/>
				<div class="referrals-social">
					<div
						class="yashare-auto-init"
						data-yashareL10n="ru"
						data-yashareTheme="counter"
						data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus"
						:data-yashareLink="referralUrl"
						data-yashareTitle=""
					/>
				</div>
			</div>
		</section>
		<section class="referrals-panel">
			<h2>{{ $t('pages.referrals.recruited_players_title') }}</h2>
			<div v-if="page.items.length" class="referrals-table-wrap">
				<table class="referrals-table">
					<thead>
						<tr>
							<th scope="col">{{ $t('pages.referrals.table_col_username') }}</th>
							<th scope="col">{{ $t('pages.referrals.table_col_registered_at') }}</th>
							<th scope="col">{{ $t('pages.referrals.table_col_development') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="item in page.items" :key="item.id">
							<td>
								<Link :href="'/players/' + item.id" class="referral-player">{{ item.username }}<span aria-hidden="true">↗</span></Link>
							</td>
							<td>
								<time :datetime="item.created_at">
									{{ $formatDate(item.created_at, 'DD MMM YYYY') }}
									<span>{{ $formatDate(item.created_at, 'HH:mm:ss') }}</span>
								</time>
							</td>
							<td>
								<div class="referral-levels">
									<span :title="$t('pages.referrals.miner_level')">
										<span>{{ $t('pages.referrals.miner_short') }}</span>
										<strong>{{ item.lvl_minier }}</strong>
									</span>
									<span class="referral-level-raid" :title="$t('pages.referrals.raid_level')">
										<span>{{ $t('pages.referrals.raid_short') }}</span>
										<strong>{{ item.lvl_raid }}</strong>
									</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div v-else class="referrals-empty">{{ $t('pages.referrals.empty') }}</div>
		</section>
		<div v-if="page.you" class="referrals-referred-by">
			<span>{{ $t('pages.referrals.referred_by_caption') }}</span>
			<Link :href="'/players/' + page.you.id">{{ page.you.username }} <span aria-hidden="true">↗</span></Link>
		</div>
		<section class="referrals-panel">
			<h2>{{ $t('pages.referrals.userbar_title') }}</h2>
			<div class="referrals-panel-content">
				<div class="referrals-userbar">
					<img :src="'/userbar' + user.id + '.jpg'" :alt="$t('pages.referrals.userbar_title')" loading="lazy">
				</div>
				<CopyField id="referral-html" :label="$t('pages.referrals.embed_html_label')" :value="html"/>
				<CopyField id="referral-bb" :label="$t('pages.referrals.embed_bb_label')" :value="bbCode"/>
			</div>
		</section>
	</div>
</template>

<script setup>
	import CopyField from '~/components/Page/Referrals/CopyField.vue';
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
	const host = computed(() => (import.meta.env.VITE_APP_URL || window.location.origin).replace(/\/$/, ''));

	useScriptTag(
		'https://yandex.st/share/share.js',
		() => {},
		{
			defer: true,
			async: true,
		},
	);

	const referralUrl = computed(() => host.value + '/?' + user.value.id);
	const userbarUrl = computed(() => host.value + '/userbar' + user.value.id + '.jpg');
	const html = computed(() => '<a href="' + referralUrl.value + '"><img src="' + userbarUrl.value + '"></a>');
	const bbCode = computed(() => '[url=' + referralUrl.value + '][img]' + userbarUrl.value + '[/img][/url]');
</script>