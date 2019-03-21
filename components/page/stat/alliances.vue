<template>
	<div class="block page-stat-alliances">
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="c col-sm-1 col-2 middle">Место</div>
					<div class="c col-sm-1 d-none d-sm-block middle">+/-</div>
					<div class="c col-sm-5 col-4 middle">Альянс</div>
					<div class="c col-sm-1 col-2 middle">Игроки</div>
					<div class="c col-sm-2 d-none d-sm-block middle">Очки</div>
					<div class="c col-sm-2 d-none d-sm-block middle">Очки на игрока</div>
					<div class="c d-sm-none col-4 middle">Очки / Очки на игрока</div>
				</div>
				<div v-for="item in items" class="row page-stat-alliances-row">
					<div class="th col-sm-1 col-2">
						{{ item['position'] }}
						<div class="d-sm-none">
							<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
							<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
							<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
						</div>
					</div>
					<div class="th col-sm-1 d-none d-sm-block">
						<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
						<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
						<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
					</div>
					<div class="th col-sm-5 col-4 middle">
						<nuxt-link :class="{neutral: item['name_marked']}" :to="'/alliance/info/'+item['id']+'/'">{{ item['name'] }}</nuxt-link>
					</div>
					<div class="th col-sm-1 col-2 middle">
						{{ item['members'] }}
					</div>
					<div class="th col-sm-2 d-none d-sm-block">
						<nuxt-link :to="'/alliance/stat/'+item['id']+'/'">{{ item['points']|number }}</nuxt-link>
					</div>
					<div class="th col-sm-2 d-none d-sm-block">
						{{ $options.filters.number(Math.floor(item['points'] / item['members'])) }}
					</div>
					<div class="th d-sm-none col-4">
						<nuxt-link :to="'/alliance/stat/'+item['id']+'/'">{{ item['points']|number }}</nuxt-link>
						<br>
						{{ $options.filters.number(Math.floor(item['points'] / item['members'])) }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "stat-alliances",
		props: {
			items: Array
		}
	}
</script>