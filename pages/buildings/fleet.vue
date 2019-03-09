<template>
	<div class="page-building page-building-unit">
		<unit-queue v-if="page.queue.length > 0" :queue="page.queue"></unit-queue>
		<div class="block">
			<div class="content page-building-items">
				<form ref="form" action="" method="post" @submit.prevent="constructAction">
					<div class="row">
						<div class="col-12">
							<div class="c">
								<input type="submit" value="Построить">
							</div>
						</div>

						<unit-row v-for="(item, i) in page.items" :key="i" :item="item"></unit-row>

						<div class="col-12">
							<div class="c">
								<input type="submit" value="Построить">
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
	import UnitRow from '~/components/page/buildings/unit-row.vue'
	import UnitQueue from '~/components/page/buildings/unit-queue.vue'

	export default {
		name: "unit",
		components: {
			UnitRow,
			UnitQueue
		},
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
		methods: {
			constructAction ()
			{
				this.$root.loader = true;

				this.$post('/buildings/'+this.page.mode+'/', new FormData(this.$refs['form']))
				.then((result) =>
				{
					this.$children.forEach((item) =>
					{
						if (typeof item['count'] !== 'undefined')
							item['count'] = '';
					});

					this.$store.commit('PAGE_LOAD', result)
					this.$router.replace(result['url'])
				}, () => {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				})
				.then(() => {
					this.$root.loader = false;
				})
			}
		}
	}
</script>