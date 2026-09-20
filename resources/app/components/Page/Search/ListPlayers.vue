<template>
	<UiEmptyState v-if="!items.length">{{ $t('pages.search.no_results') }}</UiEmptyState>
	<div v-else class="game-table-wrap">
		<table class="game-table search-players">
			<thead>
				<tr>
					<th>{{ $t('pages.search.column_name') }}</th>
					<th>{{ $t('pages.search.column_alliance') }}</th>
					<th>{{ $t('pages.search.column_planet') }}</th>
					<th>{{ $t('pages.search.column_coordinates') }}</th>
					<th>{{ $t('pages.search.column_place') }}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="item in items" :key="item.id + ':' + item.g + ':' + item.s + ':' + item.p">
					<td class="search-player-name">
						<component
							v-if="raceIcons[item.race]"
							:is="raceIcons[item.race]"
							:aria-label="$t('races.' + item.race)"
							role="img"
						/>
						<ModalLink navigate :href="'/players/' + item.id">{{ item.username }}</ModalLink>
					</td>
					<td :data-label="$t('pages.search.column_alliance')">{{ item.alliance_name || '—' }}</td>
					<td :data-label="$t('pages.search.column_planet')">{{ item.planet_name }}</td>
					<td :data-label="$t('pages.search.column_coordinates')">
						<Link :href="'/galaxy?galaxy=' + item.g + '&system=' + item.s" class="game-coordinates">
							[{{ item.g }}:{{ item.s }}:{{ item.p }}]
						</Link>
					</td>
					<td :data-label="$t('pages.search.column_place')">
						<Link :href="'/stats?view=players&page=' + Math.ceil(item.total_rank / 100)">{{ item.total_rank }}</Link>
					</td>
					<td>
						<div class="search-player-actions">
							<UiButton
								:as="SendMessagePopup"
								variant="secondary"
								icon
								:id="item.id"
								:title="$t('send_message')"
								:aria-label="$t('send_message')"
							>
								<SendIcon aria-hidden="true"/>
							</UiButton>
							<UiButton
								:as="Link"
								variant="secondary"
								icon
								:href="'/friends/new/' + item.id"
								:title="$t('pages.search.friend_request_title')"
								:aria-label="$t('pages.search.friend_request_title')"
							>
								<UserAddIcon aria-hidden="true"/>
							</UiButton>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
	import { UiButton, UiEmptyState } from '~/components/UI';
	import ConfederationIcon from '~/images/icons/races/confederation.svg?component';
	import BionicsIcon from '~/images/icons/races/bionics.svg?component';
	import CylonsIcon from '~/images/icons/races/cylons.svg?component';
	import AncientsIcon from '~/images/icons/races/ancients.svg?component';
	import { ModalLink } from '@inertiaui/modal-vue';
	import SendIcon from '~/images/icons/send.svg?component';
	import UserAddIcon from '~/images/icons/user-add.svg?component';
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { Link } from '@inertiajs/vue3';


	const raceIcons = { 1: ConfederationIcon, 2: BionicsIcon, 3: CylonsIcon, 4: AncientsIcon };

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})
</script>