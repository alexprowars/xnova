<template>
	<div class="block">
		<div class="title">Производство</div>
		<div class="content border-0">
			<div v-if="item === 42" class="table">
				<div class="row">
					<div class="col c">Уровень</div>
					<div class="col c">Дальность</div>
				</div>
				<div v-for="row in production" class="row">
					<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
					<div class="col th">{{ row['range'] }}</div>
				</div>
			</div>
			<div v-else-if="item === 22 || item === 23 || item === 24" class="table">
				<div class="row">
					<div class="col c">Уровень</div>
					<div class="col c">Вместимость</div>
				</div>
				<div v-for="row in production" class="row">
					<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
					<div class="col th">{{ row['range'] }}k</div>
				</div>
			</div>
			<div v-else-if="item !== 4" class="table">
				<div class="row">
					<div class="col c">Уровень</div>
					<div class="col c">Выработка</div>
					<div class="col c">Разница</div>
					<div class="col c">Энергия</div>
					<div class="col c">Разница</div>
				</div>
				<div v-for="row in production" class="row">
					<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
					<div class="col th">{{ row['prod']|number }}</div>
					<div class="col th"><colored :value="row['prod_diff']"></colored></div>
					<div class="col th"><colored :value="row['need']"></colored></div>
					<div class="col th"><colored :value="row['need_diff']"></colored></div>
				</div>
			</div>
			<div v-else class="table">
				<div class="row">
					<div class="col c">Уровень</div>
					<div class="col c">Выработка</div>
					<div class="col c">Разница</div>
				</div>
				<div v-for="row in production" class="row">
					<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
					<div class="col th">{{ row['prod']|number }}</div>
					<div class="col th"><colored :value="row['prod_diff']"></colored></div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "info-production",
		props: {
			production: Array,
			item: Number
		}
	}
</script>