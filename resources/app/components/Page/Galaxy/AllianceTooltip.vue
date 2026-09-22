<template>
	<div class="galaxy-tooltip-card galaxy-profile-card">
		<div class="galaxy-tooltip-summary">
			<div class="galaxy-profile-placeholder" aria-hidden="true">
				<AllianceIcon focusable="false"/>
			</div>
			<div class="galaxy-tooltip-details">
				<span class="galaxy-tooltip-type">{{ $t('menu.alliance') }}</span>
				<strong class="galaxy-tooltip-title">{{ item.alliance.name }}</strong>
				<span class="galaxy-tooltip-meta">[{{ item.alliance.tag }}]</span>
			</div>
		</div>
		<dl class="galaxy-tooltip-parameters">
			<div>
				<dt>{{ $t('pages.alliance.index.members') }}</dt>
				<dd>{{ $formatNumber(item.alliance.members) }}</dd>
			</div>
		</dl>
		<div class="galaxy-tooltip-actions">
			<Link :href="'/alliance/info/' + item.alliance.id" class="button is-secondary">
				<AllianceIcon aria-hidden="true" focusable="false"/>
				{{ $t('pages.galaxy.alliance_info') }}
			</Link>
			<Link href="/stats/alliances" class="button is-secondary">
				<svg class="icon" fill="currentColor" aria-hidden="true" focusable="false"><use xlink:href="/assets/images/symbols.svg#icon-statistics"/></svg>
				{{ $t('pages.galaxy.alliance_stats') }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import AllianceIcon from '~/images/icons/alliance.svg?component';

	defineProps({
		item: Object,
	});
</script>
