<template>
	<div class="block">
		<div class="title">Характеристики</div>
		<div class="content border-0">
			<div class="table">
				<div v-if="item !== 212" class="row">
					<div class="col th">Атака</div>
					<div class="col th">{{ fleet['attack']|number }} ({{ fleet['attack_full']|number }})</div>
				</div>
				<div class="row">
					<div class="col th">Броня</div>
					<div class="col th">{{ fleet['armor']|number }} ({{ fleet['armor_full']|number }})</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Щиты</div>
					<div class="col th">{{ fleet['shield']|number }}</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Грузоподъёмность</div>
					<div class="col th">{{ fleet['capacity']|number }}</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Скорость</div>
					<div class="col th">{{ fleet['speed']|number }} ({{ fleet['speed_full']|number }})</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Потребление топлива (дейтерий)</div>
					<div class="col th">{{ fleet['consumption']|number }}</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Тип двигателя</div>
					<div class="col th">{{ fleet['type_engine'] }}</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Тип оружия</div>
					<div class="col th">{{ fleet['type_gun'] }}</div>
				</div>
				<div v-if="item !== 212" class="row">
					<div class="col th">Тип брони</div>
					<div class="col th">{{ fleet['type_armour'] }}</div>
				</div>
				<div class="row">
					<div class="col c text-left">Скорострел</div>
				</div>
				<div class="row">
					<div class="col th" v-html="fleet['rapidfire']['to']"></div>
				</div>
				<div class="row">
					<div class="col th" v-html="fleet['rapidfire']['from']"></div>
				</div>
				<div class="row">
					<div class="col c text-left">Затраты на производство</div>
				</div>
				<div class="row">
					<div class="col th">Металл</div>
					<div class="col th">{{ fleet['resources']['metal']['base']|number }} ({{ fleet['resources']['metal']['full']|number }})</div>
				</div>
				<div class="row">
					<div class="col th">Кристалл</div>
					<div class="col th">{{ fleet['resources']['crystal']['base']|number }} ({{ fleet['resources']['crystal']['full']|number }})</div>
				</div>
				<div class="row">
					<div class="col th">Дейтерий</div>
					<div class="col th">{{ fleet['resources']['deuterium']['base']|number }} ({{ fleet['resources']['deuterium']['full']|number }})</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "info-fleet",
		props: {
			fleet: Object,
			item: Number
		}
	}
</script>