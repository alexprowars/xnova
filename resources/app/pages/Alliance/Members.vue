<template>
	<Head :title="$t('pages.alliance.members.page_title')"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.alliance.members.title', [data['members'].length]) }}
		</div>
		<div class="content">
			<table class="table text-center">
				<tbody>
				<tr>
					<td class="th">{{ $t('pages.alliance.members.number') }}</td>
					<td class="th"><Link :href="url + '?sort=name&order=' + data['order']">{{ $t('pages.alliance.members.nickname') }}</Link></td>
					<td class="th">&nbsp;</td>
					<td class="th">&nbsp;</td>
					<td class="th"><Link :href="url + '?sort=rank&order=' + data['order']">{{ $t('pages.alliance.members.rank') }}</Link></td>
					<td class="th"><Link :href="url + '?sort=points&order=' + data['order']">{{ $t('pages.alliance.members.points') }}</Link></td>
					<td class="th">{{ $t('pages.alliance.members.coordinates') }}</td>
					<td class="th"><Link :href="url + '?sort=date&order=' + data['order']">{{ $t('pages.alliance.members.join_date') }}</Link></td>
					<td class="th" v-if="data['status']"><Link :href="url + '?sort=active&order=' + data['order']">{{ $t('pages.alliance.members.activity') }}</Link></td>
					<td class="th" v-if="data['admin']">{{ $t('pages.alliance.members.management') }}</td>
				</tr>
				<template v-for="(m, index) in data['members']">
					<tr>
						<td class="th">{{ index + 1 }}</td>
						<td class="th">{{ m['username'] }}</td>
						<td class="th">
							<SendMessagePopup :title="$t('send_message')" :id="m['id']"/>
						</td>
						<td class="th">
							<img :src="'/assets/images/skin/race' + m['race'] + '.gif'" width="16" height="16" alt="">
						</td>
						<td class="th">{{ m['range'] }}</td>
						<td class="th">{{ m['points'] }}</td>
						<td class="th">
							<Link :href="'/galaxy?galaxy=' + m['galaxy'] + '&system=' + m['system']">
								{{ m['galaxy'] }}:{{ m['system'] }}:{{ m['planet'] }}
							</Link>
						</td>
						<td class="th">{{ $formatDate(m['date'], 'DD MMM YYYY') }}</td>
						<td class="th" v-html="m['online'] || ''"></td>
						<td class="th" v-if="data['admin']">
							<a href="" @click.prevent="kick(m['id'])">
								<img src="/assets/images/abort.gif" alt="">
							</a>
							&nbsp;
							<a href="" @click.prevent="setRank(m['id'])">
								<img src="/assets/images/key.gif" alt="">
							</a>
						</td>
					</tr>
					<tr v-if="m['id'] === changeRank && data['admin']">
						<td colspan="10" class="th p-0">
							<div class="table border-0">
								<div>
									<div class="th">{{ $t('pages.alliance.members.set_rank_for', [m['username']]) }}</div>
									<div class="th">
										<select v-model="m['rank']">
											<option value="0">{{ $t('pages.alliance.members.novice') }}</option>
											<option v-for="rank in data['ranks']" :value="rank['id']">{{ rank['name'] }}</option>
										</select>
									</div>
									<div class="th">
										<button @click.prevent="saveRank(m['id'], m['rank'])">
											{{ $t('pages.alliance.members.save') }}
										</button>
									</div>
								</div>
							</div>
						</td>
					</tr>
				</template>
				</tbody>
			</table>
		</div>
	</div>
	<div class="mt-2">
		<Link :href="'/alliance' + (data['admin'] ? '/admin' : '')" class="button">
			{{ $t('pages.alliance.members.back_to_overview') }}
		</Link>
	</div>
</template>

<script setup>
	import SendMessagePopup from '../../components/Page/Messages/SendMessagePopup.vue';
	import { computed, ref } from 'vue';
	import { Head, Link, router } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '../../composables/useModals.js';
	import { useApiSubmit } from '../../composables/useApi.js';
	import { useSuccessNotification } from '../../composables/useToast.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		data: Object,
	})

	const { t } = useI18n();

	const changeRank = ref();

	const url = computed(() => {
		return '/alliance/' + (props.data['admin'] ? 'admin/members' : 'members');
	});

	function setRank(id) {
		if (changeRank.value === id) {
			changeRank.value = 0;
		} else {
			changeRank.value = id;
		}
	}

	function saveRank(id, rank) {
		useApiSubmit('alliance/admin/members/rank', { id, rank }, () => {
			changeRank.value = 0;

			router.reload();
		});
	}

	function kick(id) {
		openConfirmModal(
			null,
			t('pages.alliance.members.kick_confirm.title'),
			[{
				title: t('pages.alliance.members.kick_confirm.no'),
			}, {
				title: t('pages.alliance.members.kick_confirm.yes'),
				handler: () => {
					useApiSubmit('alliance/admin/members/kick', { id }, () => {
						useSuccessNotification(t('pages.alliance.members.kick_confirm.success'));

						router.reload();
					});
				}
			}]
		);
	}
</script>