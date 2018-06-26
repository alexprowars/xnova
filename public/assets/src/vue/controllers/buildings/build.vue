<template>
	<div v-if="page" class="page-building page-building-build">
		<div class="block">
			<div class="title">
				<div class="row">
					<div class="col-12 col-sm-6">
						Занято полей
						<span class="positive">{{ page['fields_current'] }}</span> из <span class="negative">{{ page['fields_max'] }}</span>
					</div>
					<div class="text-sm-right col-12 col-sm-6">
						Осталось
						<span class="positive">{{ fields_empty }}</span>
						свободн{{ page.fields_empty|morph(['ое', 'ых', 'ых']) }}
						пол{{ page.fields_empty|morph(['е', 'я', 'ей']) }}
					</div>
				</div>
			</div>

			<build-queue v-if="page.queue && page.queue.length" :queue="page.queue"></build-queue>

			<div class="content page-building-items">
				<div class="row">
					<build-row v-for="(item, i) in page.items" :key="i" :item="item"></build-row>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import BuildRow from './build-row.vue'
	import BuildQueue from './build-queue.vue'
	import router from 'router-mixin'

	export default {
		name: "build",
		mixins: [router],
		computed: {
			fields_empty () {
				return this.page['fields_max'] - this.page['fields_current'] - this.page.queue.length;
			}
		},
		components: {
			BuildRow,
			BuildQueue
		}
	}
</script>