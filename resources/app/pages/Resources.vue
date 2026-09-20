<template>
	<Head :title="$t('menu.resources')"/>
	<div class="page-resources">
		<div class="block resources-production">
			<div class="title">{{ $t('pages.resources.planet_production', [planet.name]) }}</div>
			<div class="content">
				<form method="post" @submit.prevent="updateState">
					<div class="table-responsive">
						<table class="table resources-table">
							<thead>
								<tr>
									<th scope="col"></th>
									<th scope="col">{{ $t('pages.resources.lvl') }}</th>
									<th scope="col">{{ $t('pages.resources.bonus') }}</th>
									<th v-for="(res, index) in [...page.resources, 'energy']" :key="res" scope="col">
										<ModalLink
											navigate
											:href="'/info/' + (index + 1)"
											:title="$t('tech.' + (index + 1))"
											class="resources-label"
											:class="res"
										>
											<ResourceIcon :code="res" aria-hidden="true" focusable="false"/>
											{{ $t('resources.' + res) }}
										</ModalLink>
									</th>
									<th scope="col">{{ $t('pages.resources.efficiency') }}</th>
								</tr>
							</thead>
							<tbody>
								<tr class="resources-base">
									<td>{{ $t('pages.resources.base_production') }}</td>
									<td></td>
									<td></td>
									<td v-for="res in page.resources" :key="res">{{ $formatNumber(planet.resources[res].basic) }}</td>
									<td>{{ $formatNumber(planet.resources.energy.basic) }}</td>
									<td>100%</td>
								</tr>
								<ResourcesRow v-for="item in page.items" :key="item.id" :item="item" :resources="page.resources"/>
							</tbody>
							<tfoot>
								<tr class="resources-capacity">
									<td colspan="2">{{ $t('pages.resources.storage') }}</td>
									<td>{{ page.bonus_h }}%</td>
									<td v-for="res in page.resources" :key="res">
										<span
											:class="planet.resources[res].capacity > planet.resources[res].value ? 'positive' : 'negative'"
										>
											{{ $formatNumber(planet.resources[res].capacity / 1000) }} k
										</span>
									</td>
									<td>
										<span class="positive">{{ $formatNumber(planet.resources.energy.capacity) }}</span>
									</td>
									<td></td>
								</tr>
								<tr class="resources-total">
									<td colspan="3">{{ $t('pages.resources.total') }}</td>
									<td v-for="res in page.resources" :key="res">
										<Colored :value="planet.resources[res].production"/>
									</td>
									<td>
										<Colored :value="planet.resources.energy.value"/>
									</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div v-if="!isVacation" class="resources-form-actions">
						<button type="submit" class="button" name="action" value="Y">{{ $t('pages.resources.calculate') }}</button>
					</div>
				</form>
			</div>
		</div>
		<div class="resources-status">
			<div class="resources-efficiency">
				<div class="resources-status-heading">
					<span>{{ $t('pages.resources.production_level') }}</span>
					<strong>{{ page.production_level }}%</strong>
				</div>
				<ResourcesBar :value="page.production_level" :reverse="true" :aria-label="$t('pages.resources.production_level')"/>
			</div>
			<div class="resources-technology">
				<Link href="/info/113" class="resources-label energy">
					<ResourceIcon code="energy" aria-hidden="true" focusable="false"/>
					{{ $t('tech.113') }}
				</Link>
				<strong>{{ user.technology.energy_tech }} <span>{{ $t('pages.resources.lvl').toLowerCase() }}</span></strong>
			</div>
		</div>
		<div v-if="!isVacation" class="block resources-forecast">
			<div class="title">{{ $t('pages.resources.production_info') }}</div>
			<div class="content table-responsive">
				<table class="table resources-table">
					<thead>
						<tr>
							<th scope="col"></th>
							<th v-for="period in periods" :key="period.label" scope="col">{{ $t('pages.resources.' + period.label) }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="res in page.resources" :key="res">
							<th scope="row">
								<span class="resources-label" :class="res">
									<ResourceIcon :code="res" aria-hidden="true" focusable="false"/>
									{{ $t('resources.' + res) }}
								</span>
							</th>
							<td v-for="period in periods" :key="period.label">
								<Colored :value="planet.resources[res].production * period.hours"/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="block">
			<div class="title">{{ $t('pages.resources.storage_status') }}</div>
			<div class="content resources-storage-grid is-padded">
				<StorageRow v-for="res in page.resources" :key="res" :resource="res"/>
			</div>
		</div>
		<div v-if="!isVacation" class="block">
			<div class="title">{{ $t('pages.resources.production_management') }}</div>
			<div class="content resources-management-actions is-padded">
				<button type="button" @click="shutdown('Y')" class="button">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
						<path d="M12 3v9M7 5a8 8 0 1 0 10 0"/>
					</svg>
					{{ $t('pages.resources.production_on') }}
				</button>
				<button type="button" @click="shutdown('N')" class="button is-danger">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<rect x="5" y="5" width="14" height="14" rx="2"/>
					</svg>
					{{ $t('pages.resources.production_off') }}
				</button>
			</div>
		</div>
		<BuyResources :item="page.buy_form"/>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import useState from '~/composables/useState.js';
	import ResourcesBar from '~/components/Page/Resources/Bar.vue';
	import ResourcesRow from '~/components/Page/Resources/Row.vue';
	import StorageRow from '~/components/Page/Resources/StorageRow.vue';
	import BuyResources from '~/components/Page/Resources/BuyResources.vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { computed } from 'vue';
	import Colored from '~/components/Colored.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	const props = defineProps({
		page: Object,
	});

	const periods = [
		{ label: 'per_hour', hours: 1 },
		{ label: 'per_day', hours: 24 },
		{ label: 'per_week', hours: 24 * 7 },
		{ label: 'per_month', hours: 24 * 30 },
	];

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);
	const isVacation = computed(() => user.value?.vacation !== null);

	async function shutdown(active) {
		useForm({ active }).post('/resources/shutdown', {
			preserveUrl: true,
		});
	}

	async function updateState() {
		let state = {};

		props.page['items'].forEach((item) => state[item['id']] = item['factor']);

		useForm({ state }).post('/resources/state', {
			preserveUrl: true,
		});
	}
</script>