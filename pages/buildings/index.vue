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
						{{ page['fields_empty'] | morph('свободное', 'свободных', 'свободных') }}
						{{ page['fields_empty'] | morph('поле', 'поля', 'полей') }}
					</div>
				</div>
			</div>

			<BuildQueue v-if="page.queue && page.queue.length" :queue="page.queue"></BuildQueue>

			<div class="content page-building-items">
				<div class="row">
					<BuildRow v-for="(item, i) in page.items" :key="i" :item="item"></BuildRow>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import BuildRow from '~/components/page/buildings/build-row.vue'
	import BuildQueue from '~/components/page/buildings/build-queue.vue'

	export default {
		name: 'build',
		components: {
			BuildRow,
			BuildQueue
		},
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		computed: {
			fields_empty ()
			{
				if (!this.page)
					return 0;

				return this.page['fields_max'] - this.page['fields_current'] - this.page.queue.length;
			}
		},
	}
</script>