<template>
	<Head :title="$t('pages.fleets.shortcut.index.meta_title')"/>
	<div class="fleet-page page-fleet-shortcut">
		<div class="block">
			<div class="title">
				{{ $t('pages.fleets.shortcut.index.title') }}
			</div>
			<div class="content">
				<div class="fleet-shortcut-content">
					<div class="fleet-shortcut-list">
						<div v-for="item in page.items" :key="item.id" class="fleet-shortcut-card">
							<Link :href="'/fleet/shortcut/' + item['id']">
								<strong>{{ item.name }}</strong><span>[{{ item.galaxy }}:{{ item.system }}:{{ item.planet }}] · {{ $t('planet_type.' + item.planet_type) }}</span>
							</Link>
						</div>
						<div v-if="page.items.length === 0" class="col-span-2 th">{{ $t('pages.fleets.shortcut.index.empty') }}</div>
					</div>
					<div class="grid">
						<div class="fleet-actions">
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
		page: Object,
	});
</script>