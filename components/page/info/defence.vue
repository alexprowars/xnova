<template>
	<div class="block">
		<div class="title">Характеристики</div>
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="col th">Атака</div>
					<div class="col th">{{ defence['attack']|number }} ({{ defence['attack_full']|number }})</div>
				</div>
				<div class="row">
					<div class="col th">Броня</div>
					<div class="col th">{{ defence['armor']|number }}</div>
				</div>
				<div v-if="defence['shield'] > 0" class="row">
					<div class="col th">Щиты</div>
					<div class="col th">{{ defence['shield']|number }}</div>
				</div>
				<div v-if="defence['type_gun']" class="row">
					<div class="col th">Тип оружия</div>
					<div class="col th">{{ defence['type_gun'] }}</div>
				</div>
				<div v-if="defence['type_armour']" class="row">
					<div class="col th">Тип брони</div>
					<div class="col th">{{ defence['type_armour'] }}</div>
				</div>
				<div class="row">
					<div class="col c">Затраты на производство</div>
				</div>
				<div class="row">
					<div class="col th">Металл</div>
					<div class="col th">{{ defence['resources']['metal']|number }}</div>
				</div>
				<div class="row">
					<div class="col th">Кристалл</div>
					<div class="col th">{{ defence['resources']['crystal']|number }}</div>
				</div>
				<div class="row">
					<div class="col th">Дейтерий</div>
					<div class="col th">{{ defence['resources']['deuterium']|number }}</div>
				</div>
				<div v-if="defence['rapidfire']" class="row">
					<div class="col c">Скорострел</div>
				</div>
				<div v-if="defence['rapidfire']" class="row">
					<div class="col c text-left">Тип юнита</div>
					<div class="col c positive">Поражает флот</div>
					<div class="col c negative">Теряет флот</div>
				</div>
				<div v-if="defence['rapidfire']" v-for="(battle, fId) in defence['rapidfire']" class="row">
					<div class="col th text-left">
						<nuxt-link :to="'/info/'+fId+'/'">{{ $t('TECH.'+fId) }}</nuxt-link>
					</div>
					<div class="col th positive">
						<span v-if="battle['TO']">{{ battle['TO'] }}</span>
						<span v-else>< 1</span>
					</div>
					<div class="col th negative">
						<span v-if="battle['FROM']">{{ battle['FROM'] }}</span>
						<span v-else>< 1</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "info-defence",
		props: {
			defence: Object,
			item: Number
		}
	}
</script>