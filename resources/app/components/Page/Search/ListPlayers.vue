<template>
	<table class="table">
		<tbody>
			<tr>
				<td class="c" width="120">{{ $t('pages.search.column_name') }}</td>
				<td class="c" width="40">&nbsp;</td>
				<td class="c" width="20">&nbsp;</td>
				<td class="c">{{ $t('pages.search.column_alliance') }}</td>
				<td class="c">{{ $t('pages.search.column_planet') }}</td>
				<td class="c" width="80">{{ $t('pages.search.column_coordinates') }}</td>
				<td class="c" width="40">{{ $t('pages.search.column_place') }}</td>
			</tr>
			<tr v-for="item in items">
				<td class="th">{{ item['username'] }}</td>
				<td class="th" nowrap>
					<SendMessagePopup :title="$t('send_message')" :id="item['id']"/>
					<Link :href="'/friends/new/' + item['id']" :title="$t('pages.search.friend_request_title')">
						<span class='sprite skin_b'></span>
					</Link>
				</td>
				<td class="th">
					<img v-if="item['race'] !== 0" :src="'/assets/images/skin/race'+item['race']+'.gif'" width="16" height="16" alt="">
				</td>
				<td class="th">{{ item['ally_name'] }}</td>
				<td class="th">{{ item['planet_name'] }}</td>
				<td class="th"><Link :href="'/galaxy?galaxy='+item['g']+'&system='+item['s']">{{ item['g'] }}:{{ item['s'] }}:{{ item['p'] }}</Link></td>
				<td class="th"><Link :href="'/stats?view=players&range='+item['total_rank']">{{ item['total_rank'] }}</Link></td>
			</tr>
			<tr v-if="items.length === 0">
				<td class="th" colspan="7">{{ $t('pages.search.no_results') }}</td>
			</tr>
		</tbody>
	</table>
</template>

<script setup>
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { Link } from '@inertiajs/vue3';

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})
</script>