<template>
	<div class="page-building page-building-unit">
		<UnitQueue v-if="page.queue.length > 0" :queue="page.queue"></UnitQueue>
		<div class="block">
			<div class="content page-building-items">
				<form ref="form" action="" method="post" @submit.prevent="constructAction">
					<div class="row">
						<div class="col-12">
							<div class="c">
								<input type="submit" value="Построить">
							</div>
						</div>

						<UnitRow v-for="(item, i) in page.items" :key="i" :item="item"></UnitRow>

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
		name: 'unit',
		components: {
			UnitRow,
			UnitQueue
		},
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		methods: {
			constructAction ()
			{
				this.$store.commit('setLoadingStatus', true)

				this.$post('/buildings/'+this.page.mode+'/', new FormData(this.$refs['form']))
				.then((result) =>
				{
					this.$children.forEach((item) =>
					{
						if (typeof item['count'] !== 'undefined')
							item['count'] = '';
					});

					this.$store.commit('PAGE_LOAD', result)
					this.$store.commit('setLoadingStatus', false)
				})
				.catch(() => {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				});
			}
		}
	}
</script>