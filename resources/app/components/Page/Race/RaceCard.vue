<template>
	<component :is="selectable ? 'label' : 'article'" class="race-card" :class="['race-card-' + faction.id, { 'is-selectable': selectable, 'is-selected': selectable && selected, 'is-current': !selectable && selected }]">
		<input v-if="selectable" type="radio" name="race" :value="faction.id" :checked="selected" :aria-label="$t('pages.race.' + faction.name)" @change="emit('select', faction.id)">
		<span v-if="selectable && selected" class="race-choice-mark" aria-hidden="true">
			<CheckIcon stroke-width="2"/>
		</span>
		<div class="race-card-heading">
			<RaceIcon :code="faction.id" class="race-emblem" aria-hidden="true" focusable="false"/>
			<div class="race-card-intro">
				<component :is="selectable ? 'h3' : 'h2'" class="race-card-title">{{ $t('pages.race.' + faction.name) }}</component>
				<p class="race-description">{{ $t('pages.race.description_race' + faction.id) }}</p>
				<span v-if="selected && !selectable" class="race-current">
					<CheckIcon stroke-width="1.8" aria-hidden="true"/>
					{{ $t('pages.race.your_faction') }}
				</span>
			</div>
		</div>
		<div class="race-card-content">
			<component :is="selectable ? 'h4' : 'h3'" class="race-perks-title">{{ $t('pages.race.race_features') }}</component>
			<ul class="race-perks">
				<li v-for="(perk, index) in $t('pages.race.perks_race' + faction.id).split('<br>')" :key="index">{{ perk }}</li>
			</ul>
		</div>
		<component :is="selectable ? 'div' : ModalLink" v-bind="selectable ? {} : { navigate: true, href: '/info/' + faction.ship }" class="race-ship">
			<img :src="'/assets/images/elements/' + faction.ship + '.webp'" alt="" width="44" height="44" loading="lazy">
			<div>
				<span class="race-ship-label">{{ $t('pages.race.unique_ship_label') }}</span>
				<strong>{{ $t('pages.race.ship_race' + faction.id + '_name') }}</strong>
				<span class="race-ship-description">{{ $t('pages.race.ship_race' + faction.id + '_desc') }}</span>
			</div>
			<RightIcon v-if="!selectable" stroke-width="1.6" aria-hidden="true"/>
		</component>
	</component>
</template>

<script setup>
	import CheckIcon from '~/images/icons/check.svg?component';
	import RightIcon from '~/images/icons/right.svg?component';
	import RaceIcon from '~/components/RaceIcon.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineProps({
		faction: { type: Object, required: true },
		selectable: Boolean,
		selected: Boolean,
	});
	const emit = defineEmits(['select']);
</script>