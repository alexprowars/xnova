<template>
	<Head title="Офицеры"/>
	<div class="block">
		<div class="officiers-list">
			<div class="officiers-item">
				<div class="officiers-item-title">
					{{ $t('pages.officiers.credits', [$formatNumber(user['credits'])]) }}
				</div>
				<div class="flex flex-wrap sm:flex-nowrap gap-2">
					<div class="hidden sm:block sm:basis-1/6 officiers-item-image">
						<img src="/assets/images/officiers/bigcredits.jpg" alt="">
					</div>
					<div class="basis-full sm:basis-5/6 text-left">
						{{ $t('pages.officiers.credits_info') }}

						<div class="flex my-4 gap-2">
							<div>
								<img src="/assets/images/officiers/smalcredts.gif" alt="">
							</div>
							<div class="flex flex-col justify-center">
								<div class="text-sky-300">
									{{ $t('pages.officiers.credits_info_2') }}
								</div>
							</div>
						</div>

						<Link href="/credits" class="button positive">
							{{ $t('pages.officiers.get_credits') }}
						</Link>
					</div>
				</div>
			</div>
			<Row v-for="item in page.items" :key="item['id']" :item="item"/>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import Row from '~/components/Page/Officier/Row.vue';
	import { Head, Link } from '@inertiajs/vue3';
	import { computed } from 'vue';

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
</script>