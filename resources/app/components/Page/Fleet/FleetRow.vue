<template>
	<div class="grid grid-cols-12 divide-x page-fleet-fly-item">
		<div class="col-span-3 sm:col-span-1 th">{{ i + 1 }}</div>
		<div class="col-span-6 sm:col-span-2 th">
			<div>{{ $t('fleet_mission.' + item.mission) }}</div>
			<div v-if="item.start.time + 1 === item.target.time">
				<a v-tooltip="$t('pages.fleets.list.mission_R')">(R)</a>
			</div>
			<div v-else>
				<a v-tooltip="$t('pages.fleets.list.mission_A')">(A)</a>
			</div>
		</div>
		<div class="col-span-3 sm:col-span-1 th">
			<Popper>
				<template #content>
					<div class="flex flex-col gap-2 p-2">
						<div v-for="unit in item['units']">
							{{ $t('tech.' + unit['i']) }}: {{ unit['c'] }}
						</div>
					</div>
				</template>
				<div class="cursor-pointer">{{ $formatNumber(item['amount']) }}</div>
			</Popper>
		</div>
		<div class="col-span-4 sm:col-span-3 border-t sm:border-t-0 th">
			<div>
				<Link :href="'/galaxy?galaxy='+item['target']['galaxy']+'&system='+item['target']['system']" class="negative">
					[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]
				</Link>
			</div>
			{{ $formatDate(item['start']['date'], 'DD MMM HH:mm:ss') }}
			<Timer :value="item['start']['date']" delimiter="" class="positive"/>
		</div>
		<div v-if="item['target']['date']" class="col-span-4 sm:col-span-3 border-t sm:border-t-0 th">
			<div>
				<Link :href="'/galaxy?galaxy='+item['start']['galaxy']+'&system='+item['start']['system']" class="positive">
					[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]
				</Link>
			</div>
			{{ $formatDate(item['target']['date'], 'DD MMM HH:mm:ss') }}
			<Timer :value="item['target']['date']" delimiter="" class="positive"/>
		</div>
		<div v-else class="col-span-4 sm:col-span-3 border-t sm:border-t-0 th">
			-
		</div>
		<div class="col-span-4 sm:col-span-2 border-t sm:border-0 th flex flex-col gap-1">
			<Link v-if="item['stage'] === 0 && item['mission'] === 1" :href="'/fleet/verband/' + item.id" class="button w-full">
				{{ $t('pages.fleets.list.merge') }}
			</Link>

			<button v-if="(item['stage'] === 3 && item['mission'] !== 15) || (item['stage'] === 0 && item['mission'] !== 20)" class="button w-full" @click.prevent="backAction">
				{{ $t('pages.fleets.list.recall') }}
			</button>
		</div>
	</div>
</template>

<script setup>
	import { Link, useForm } from '@inertiajs/vue3';
	import Timer from '~/components/Timer.vue';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import Popper from '~/components/Popper.vue';

	const props = defineProps({
		i: {
			type: Number,
		},
		item: {
			type: Object,
		}
	});

	const { t } = useI18n();

	function backAction () {
		openConfirmModal(
			null,
			t('pages.fleets.return_popup.title'),
			[{
				title: t('pages.fleets.return_popup.no'),
			}, {
				title: t('pages.fleets.return_popup.yes'),
				handler() {
					useForm({
						id: props.item['id'],
					})
					.post('/fleet/back', {
						preserveUrl: true,
						onSuccess() {
							useSuccessNotification(t('pages.fleets.return_popup.success'));
						}
					});
				}
			}]
		);
	}
</script>