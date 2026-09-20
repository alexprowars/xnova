<template>
	<Head :title="$t('pages.alliance.members.page_title')"/>
	<div class="page-alliance page-alliance-members">
		<AllianceBack :href="page.admin ? '/alliance/admin' : '/alliance'"/>
		<header class="alliance-heading">
			<h1>
				{{ $t('pages.alliance.members.page_title') }}
				<span class="alliance-count">{{ page.members.length }}</span>
			</h1>
		</header>
		<section class="alliance-panel">
			<div class="alliance-table-wrap">
				<table class="alliance-table alliance-members-table">
					<thead>
						<tr>
							<th>{{ $t('pages.alliance.members.number') }}</th>
							<th>
								<Link :href="url + '?sort=name&order=' + page.order">{{ $t('pages.alliance.members.nickname') }} ↕</Link>
							</th>
							<th>
								<Link :href="url + '?sort=rank&order=' + page.order">{{ $t('pages.alliance.members.rank') }} ↕</Link>
							</th>
							<th>
								<Link :href="url + '?sort=points&order=' + page.order">{{ $t('pages.alliance.members.points') }} ↕</Link>
							</th>
							<th>{{ $t('pages.alliance.members.coordinates') }}</th>
							<th>
								<Link :href="url + '?sort=date&order=' + page.order">{{ $t('pages.alliance.members.join_date') }} ↕</Link>
							</th>
							<th v-if="page.status">
								<Link :href="url + '?sort=active&order=' + page.order">{{ $t('pages.alliance.members.activity') }} ↕</Link>
							</th>
							<th v-if="page.admin">{{ $t('pages.alliance.members.management') }}</th>
						</tr>
					</thead>
					<tbody>
						<template v-for="(m, index) in page.members" :key="m.id">
							<tr>
								<td class="alliance-member-number">{{ index + 1 }}</td>
								<td class="alliance-member-identity">
									<div>
										<component
											v-if="raceIcons[m.race]"
											:is="raceIcons[m.race]"
											class="alliance-race"
											:class="'race-' + m.race"
											:aria-label="$t('pages.race.' + raceNames[m.race])"
											role="img"
										/>
										<SendMessagePopup :id="m.id" :title="$t('send_message')">
											{{ m.username }}
											<SendIcon aria-hidden="true"/>
										</SendMessagePopup>
									</div>
								</td>
								<td :data-label="$t('pages.alliance.members.rank')">{{ m.range }}</td>
								<td class="alliance-number" :data-label="$t('pages.alliance.members.points')">{{ m.points }}</td>
								<td :data-label="$t('pages.alliance.members.coordinates')">
									<Link :href="'/galaxy?galaxy=' + m.galaxy + '&system=' + m.system" class="alliance-coordinates">
										[{{ m.galaxy }}:{{ m.system }}:{{ m.planet }}]
									</Link>
								</td>
								<td :data-label="$t('pages.alliance.members.join_date')">
									<time>{{ $formatDate(m.date, 'DD MMM YYYY') }}</time>
								</td>
								<td v-if="page.status" :data-label="$t('pages.alliance.members.activity')">
									<span class="alliance-online" v-html="m.online || '—'"></span>
								</td>
								<td v-if="page.admin" class="alliance-member-actions">
									<div>
										<button
											type="button"
											class="button is-secondary icon-button"
											:disabled="memberForm.processing"
											:title="$t('pages.alliance.members.set_rank_for', [m.username])"
											:aria-label="$t('pages.alliance.members.set_rank_for', [m.username])"
											:aria-expanded="changeRank === m.id"
											@click="setRank(m.id)"
										>
											<EditIcon aria-hidden="true"/>
										</button>
										<button
											type="button"
											class="button is-danger icon-button"
											:disabled="memberForm.processing"
											:title="$t('pages.alliance.ui.kick')"
											:aria-label="$t('pages.alliance.ui.kick') + ': ' + m.username"
											@click="kick(m.id)"
										>
											<TrashIcon aria-hidden="true"/>
										</button>
									</div>
								</td>
							</tr>
							<tr v-if="m.id === changeRank && page.admin" class="alliance-member-edit">
								<td :colspan="7 + (page.status ? 1 : 0)">
									<form class="alliance-rank-editor" @submit.prevent="saveRank(m.id, m.rank)">
										<label :for="'member-rank-' + m.id">
											{{ $t('pages.alliance.members.set_rank_for', [m.username]) }}
										</label>
										<select :id="'member-rank-' + m.id" v-model="m.rank">
											<option :value="null">{{ $t('pages.alliance.members.novice') }}</option>
											<option v-for="rank in page.ranks" :key="rank.id" :value="rank.id">{{ rank.name }}</option>
										</select>
										<button type="submit" class="button" :disabled="memberForm.processing">
											{{ $t('pages.alliance.members.save') }}
										</button>
									</form>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
			<div v-if="!page.members.length" class="alliance-empty">{{ $t('pages.alliance.ui.no_members') }}</div>
		</section>
		<div v-for="(error, key) in memberForm.errors" :key="key" class="alliance-errors">{{ error }}</div>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import SendIcon from '~/images/icons/send.svg?component';
	import EditIcon from '~/images/icons/edit.svg?component';
	import TrashIcon from '~/images/icons/trash.svg?component';
	import ConfederationIcon from '~/images/icons/races/confederation.svg?component';
	import BionicsIcon from '~/images/icons/races/bionics.svg?component';
	import CylonsIcon from '~/images/icons/races/cylons.svg?component';
	import AncientsIcon from '~/images/icons/races/ancients.svg?component';

	import SendMessagePopup from '~/components/Page/Messages/SendMessagePopup.vue';
	import { computed, ref } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	})

	const { t } = useI18n();

	const changeRank = ref();
	const memberForm = useForm({ id: null, rank: null });
	const raceIcons = { 1: ConfederationIcon, 2: BionicsIcon, 3: CylonsIcon, 4: AncientsIcon };
	const raceNames = { 1: 'faction_confederation', 2: 'faction_bionics', 3: 'faction_cylons', 4: 'faction_ancients' };

	const url = computed(() => {
		return '/alliance/' + (props.page['admin'] ? 'admin/members' : 'members');
	});

	function setRank(id) {
		if (changeRank.value === id) {
			changeRank.value = 0;
		} else {
			changeRank.value = id;
		}
	}

	function saveRank(id, rank) {
		memberForm.id = id;
		memberForm.rank = rank;
		memberForm.post('/alliance/admin/members/rank', {
			preserveUrl: true,
			preserveScroll: true,
			onSuccess() {
				changeRank.value = 0;
			}
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
				handler() {
					memberForm.id = id;
					memberForm.post('/alliance/admin/members/kick', {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							changeRank.value = 0;
						}
					});
				}
			}]
		);
	}
</script>