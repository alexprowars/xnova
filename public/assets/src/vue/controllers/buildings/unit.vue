<template>
	<div class="page-building page-building-unit">
		<unit-queue v-if="page.queue.length > 0" :queue="page.queue"></unit-queue>
		<div class="content page-building-items">
			<form ref="form" :action="$root.getUrl('buildings/'+page.mode+'/')" method="post" class="noajax" @submit.prevent="constructAction">
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
</template>

<script>
	import UnitRow from './unit-row.vue'
	import UnitQueue from './unit-queue.vue'

	export default {
		name: "unit",
		computed: {
			page () {
				return this.$store.state.page;
			},
		},
		components: {
			UnitRow,
			UnitQueue
		},
		methods: {
			constructAction ()
			{
				this.$root.loader = true;

				$.ajax({
				    url: this.$root.getUrl('buildings/'+this.page.mode+'/'),
				    data: new FormData(this.$refs['form']),
				    type: 'post',
					dataType: 'json',
				    contentType: false,
				    processData: false,
					complete: () => {
						this.$root.loader = false;
					}
				})
				.then((result) =>
				{
					this.$children.forEach((item) =>
					{
						if (typeof item['count'] !== 'undefined')
							item['count'] = '';
					});

					this.$root.applyData(result.data);
				}, () => {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				})
			}
		}
	}
</script>