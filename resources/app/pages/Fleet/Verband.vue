<template>
	<Head :title="$t('pages.fleets.verband.meta_title')"/>
	<div class="page-fleet-verband">
		<div class="block">
			<div class="title">{{ $t('pages.fleets.verband.block_title_fleets_joint') }}</div>
			<div class="content">
				<table class="table">
					<tbody>
					<tr>
						<td class="th">{{ $t('pages.fleets.verband.col_mission') }}</td>
						<td class="th">{{ $t('pages.fleets.verband.col_quantity') }}</td>
						<td class="th">{{ $t('pages.fleets.verband.col_departed') }}</td>
						<td class="th">{{ $t('pages.fleets.verband.col_arrival') }}</td>
						<td class="th">{{ $t('pages.fleets.verband.col_target') }}</td>
						<td class="th">{{ $t('pages.fleets.verband.col_return') }}</td>
					</tr>
					<FleetRow v-for="item in page['items']" :key="item['id']" :item="item"/>
					<tr v-if="page['items'].length === 0"><td class="th" colspan="6">-</td></tr>
					</tbody>
				</table>
			</div>
		</div>
		<div v-if="!page['assault']" class="block">
			<div class="title">{{ $t('pages.fleets.verband.block_create_association') }}</div>
			<div class="content">
				<Create :id="page['fleetid']"/>
			</div>
		</div>
		<div v-else-if="page['fleetid'] === page['assault']['fleet_id']" class="block">
			<div class="title">{{ $t('pages.fleets.verband.block_association_title', { name: page['assault']['name'] }) }}</div>
			<div class="content">
				<div class="block-table">
					<div class="grid">
						<div class="th">
							<ChangeName :id="page['fleetid']" :name="page['assault']['name']"/>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<table class="table">
								<tbody>
								<tr>
									<td class="c">{{ $t('pages.fleets.verband.th_invited_participants') }}</td>
									<td class="c">{{ $t('pages.fleets.verband.th_invite_participants') }}</td>
								</tr>
								<tr>
									<td class="th" width="50%" valign="top">
										<select size="10" style="width:75%;">
											<option v-for="user in page['users']">{{ user }}</option>
											<option v-if="page['users'].length === 0">{{ $t('pages.fleets.verband.option_no_participants') }}</option>
										</select>
									</td>
									<td class="th">
										<InviteUser :id="page['fleetid']" :friends="page['friends']" :alliance="page['alliance']"/>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import Create from '~/components/Page/Fleet/Verband/Create.vue';
	import ChangeName from '~/components/Page/Fleet/Verband/ChangeName.vue';
	import InviteUser from '~/components/Page/Fleet/Verband/InviteUser.vue';
	import FleetRow from '~/components/Page/Fleet/Verband/FleetRow.vue';
	import { Head } from '@inertiajs/vue3';

	defineProps({
		page: Object,
	})
</script>