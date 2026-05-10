<template>
	<Head title="Сырьё"/>
	<div class="page-resources">
		<div class="block">
			<div class="title">
				{{ $t('pages.resources.planet_production', [planet['name']]) }}
			</div>
			<div class="content border-0">
				<form class="table-responsive" method="post" @submit.prevent="updateState">
					<table class="table">
						<tbody>
							<tr class="text-center">
								<td class="th" width="200"></td>
								<td class="th">{{ $t('pages.resources.lvl') }}</td>
								<td class="th">{{ $t('pages.resources.bonus') }}</td>
								<td class="th"><ModalLink navigate href="info/1" :title="$t('tech.1')">{{ $t('resources.metal') }}</ModalLink></td>
								<td class="th"><ModalLink navigate href="info/2" :title="$t('tech.2')">{{ $t('resources.crystal') }}</ModalLink></td>
								<td class="th"><ModalLink navigate href="info/3" :title="$t('tech.3')">{{ $t('resources.deuterium') }}</ModalLink></td>
								<td class="th"><ModalLink navigate href="info/4" :title="$t('tech.4')">{{ $t('resources.energy') }}</ModalLink></td>
								<td class="th" width="100">{{ $t('pages.resources.efficiency') }}</td>
							</tr>
							<tr>
								<td class="th text-left" nowrap>{{ $t('pages.resources.base_production') }}</td>
								<td class="k text-center"></td>
								<td class="k text-center"></td>
								<td v-for="res in page['resources']" class="k text-center">{{ $formatNumber(planet['resources'][res]['basic']) }}</td>
								<td class="k text-center">{{ $formatNumber(planet['resources']['energy']['basic']) }}</td>
								<td class="k text-center">100%</td>
							</tr>
							<ResourcesRow v-for="(item, index) in page['items']" :key="index" :item="item" :resources="page['resources']"/>
							<tr>
								<td class="th" colspan="2">{{ $t('pages.resources.storage') }}</td>
								<td class="th text-center">{{ page['bonus_h'] }}%</td>
								<td v-for="res in page['resources']" class="k text-center" v-once>
									<span :class="[(planet['resources'][res]['capacity'] > planet['resources'][res]['value']) ? 'positive' : 'negative']">
										{{ $formatNumber(planet['resources'][res]['capacity'] / 1000) }} k
									</span>
								</td>
								<td class="k text-center">
									<span class="positive">{{ $formatNumber(planet['resources']['energy']['capacity']) }}</span>
								</td>
								<td v-if="!isVacation" class="k text-center">
									<button type="submit" class="button" name="action" value="Y">
										{{ $t('pages.resources.calculate') }}
									</button>
								</td>
							</tr>
							<tr>
								<td class="th" colspan="3">{{ $t('pages.resources.total') }}</td>
								<td v-for="res in page['resources']" class="k text-center">
									<Colored :value="planet['resources'][res]['production']"/>
								</td>
								<td class="k text-center"><Colored :value="planet['resources']['energy']['value']"/></td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>

		<div class="block">
			<div class="block-table rounded overflow-clip">
				<div class="grid grid-cols-6">
					<div class="col-span-2 c">{{ $t('pages.resources.production_level') }}</div>
					<div class="th text-center">{{ page['production_level'] }}%</div>
					<div class="col-span-3 th">
						<ResourcesBar :value="page['production_level']" :reverse="true"/>
					</div>
				</div>
				<div class="grid grid-cols-6">
					<div class="col-span-2 c">
						<Link href="/info/113">{{ $t('tech.113') }}</Link>
					</div>
					<div class="th text-center">
						{{ user['technology']['energy_tech'] }} {{ $t('pages.resources.lvl').toLowerCase() }}
					</div>
					<div class="col-span-3 th"></div>
				</div>
			</div>
		</div>

		<div v-if="!isVacation" class="block">
			<div class="title">
				{{ $t('pages.resources.production_info') }}
			</div>
			<div class="content">
				<div class="block-table">
					<div class="grid grid-cols-12 text-center">
						<div class="col-span-2 th">&nbsp;</div>
						<div class="col-span-2 th">{{ $t('pages.resources.per_hour') }}</div>
						<div class="col-span-2 th">{{ $t('pages.resources.per_day') }}</div>
						<div class="col-span-3 th">{{ $t('pages.resources.per_week') }}</div>
						<div class="col-span-3 th">{{ $t('pages.resources.per_month') }}</div>
					</div>
					<div class="grid grid-cols-12" v-for="res in page['resources']">
						<div class="col-span-2 th">
							{{ $t('resources.' + res) }}
						</div>
						<div class="col-span-2 th text-center">
							<Colored :value="planet['resources'][res]['production']"/>
						</div>
						<div class="col-span-2 th text-center">
							<Colored :value="planet['resources'][res]['production'] * 24"/>
						</div>
						<div class="col-span-3 th text-center">
							<Colored :value="planet['resources'][res]['production'] * 24 * 7"/>
						</div>
						<div class="col-span-3 th text-center">
							<Colored :value="planet['resources'][res]['production'] * 24 * 7 * 30"/>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="title">
				{{ $t('pages.resources.storage_status') }}
			</div>
			<div class="content">
				<div class="block-table">
					<StorageRow v-for="res in page['resources']" :key="res" :resource="res"/>
				</div>
			</div>
		</div>

		<div v-if="!isVacation" class="block">
			<div class="title">
				{{ $t('pages.resources.production_management') }}
			</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-2">
						<div class="th">
							<button @click.prevent="shutdown('Y')" class="button h-12">
								{{ $t('pages.resources.production_on') }}
							</button>
						</div>
						<div class="th">
							<button @click.prevent="shutdown('N')" class="button h-12">
								{{ $t('pages.resources.production_off') }}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<BuyResources :item="page['buy_form']"/>
	</div>
</template>

<script setup>
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