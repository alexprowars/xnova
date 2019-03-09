<template>
	<div class="page-building page-building-build">
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
	import BuildRow from '~/components/page/buildings/build-row.vue'
	import BuildQueue from '~/components/page/buildings/build-queue.vue'

	export default {
		name: "build",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
		computed: {
			fields_empty ()
			{
				if (!this.page)
					return 0;

				return this.page['fields_max'] - this.page['fields_current'] - this.page.queue.length;
			}
		},
		components: {
			BuildRow,
			BuildQueue
		}
	}
</script>