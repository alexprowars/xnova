<template>
	<div class="block">
		<div class="title">{{ $t('pages.info.production.title') }}</div>
		<div class="content block-table border-t! text-center">
			<template v-if="item === 42">
				<div class="grid grid-cols-2">
					<div class="c">{{ $t('pages.info.production.level') }}</div>
					<div class="c">{{ $t('pages.info.production.range') }}</div>
				</div>
				<div v-for="row in production" class="grid grid-cols-2">
					<div class="th"><span :class="{neutral: row['value']}">{{ row['level'] }}</span></div>
					<div class="th">{{ row['range'] }}</div>
				</div>
			</template>
			<template v-else-if="item === 22 || item === 23 || item === 24">
				<div class="grid grid-cols-3">
					<div class="c">{{ $t('pages.info.production.level') }}</div>
					<div class="c">{{ $t('pages.info.production.capacity') }}</div>
					<div class="c">{{ $t('pages.info.production.difference') }}</div>
				</div>
				<div v-for="row in production" class="grid grid-cols-3">
					<div class="th"><span :class="{neutral: row['value']}">{{ row['level'] }}</span></div>
					<div class="th">{{ $formatNumber(row['prod']) }}k</div>
					<div class="th"><Colored :value="row['prod_diff']"/>k</div>
				</div>
			</template>
			<template v-else-if="item !== 4">
				<div class="grid grid-cols-5">
					<div class="c">{{ $t('pages.info.production.level') }}</div>
					<div class="c">{{ $t('pages.info.production.output') }}</div>
					<div class="c">{{ $t('pages.info.production.difference') }}</div>
					<div class="c">{{ $t('pages.info.production.energy') }}</div>
					<div class="c">{{ $t('pages.info.production.difference') }}</div>
				</div>
				<div v-for="row in production" class="grid grid-cols-5">
					<div class="th"><span :class="{neutral: row['value']}">{{ row['level'] }}</span></div>
					<div class="th">{{ $formatNumber(row['prod']) }}</div>
					<div class="th"><Colored :value="row['prod_diff']"/></div>
					<div class="th"><Colored :value="row['need']"/></div>
					<div class="th"><Colored :value="row['need_diff']"/></div>
				</div>
			</template>
			<template v-else>
				<div class="grid grid-cols-3">
					<div class="c">{{ $t('pages.info.production.level') }}</div>
					<div class="c">{{ $t('pages.info.production.output') }}</div>
					<div class="c">{{ $t('pages.info.production.difference') }}</div>
				</div>
				<div v-for="row in production" class="grid grid-cols-3">
					<div class="th"><span :class="{neutral: row['value']}">{{ row['level'] }}</span></div>
					<div class="th">{{ $formatNumber(row['prod']) }}</div>
					<div class="th"><Colored :value="row['prod_diff']"/></div>
				</div>
			</template>
		</div>
	</div>
</template>

<script setup>
	defineProps({
		production: {
			type: Array,
			default: () => []
		},
		item: {
			type: Number,
			default: 0,
		}
	})
</script>