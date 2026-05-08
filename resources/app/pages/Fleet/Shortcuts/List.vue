<template>
	<Head :title="$t('pages.fleets.shortcut.index.meta_title')"/>
	<div class="page-fleet-shortcut">
		<div class="block">
			<div class="title">
				{{ $t('pages.fleets.shortcut.index.title') }}
			</div>
			<div class="content">
				<div class="block-table">
					<div class="grid grid-cols-2">
						<div v-for="item in items" class="th">
							<Link :href="'/fleet/shortcut/' + item['id']">
								{{ item['name'] }} [{{ item['galaxy'] }}:{{ item['system'] }}:{{ item['planet'] }}] {{ $t('planet_type.' + item['planet_type']) }}
							</Link>
						</div>
						<div v-if="items.length === 0" class="col-span-2 th">{{ $t('pages.fleets.shortcut.index.empty') }}</div>
					</div>
					<div class="grid">
						<div class="c text-center">
							<Link href="/fleet/shortcut/create" class="button">{{ $t('pages.fleets.shortcut.index.add') }}</Link>
							<Link href="/fleet" class="button">{{ $t('pages.fleets.shortcut.index.back_fleet') }}</Link>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link } from '@inertiajs/vue3';

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})
</script>