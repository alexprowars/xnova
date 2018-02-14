<template>
	<div class="page-building page-building-unit">
		<game-page-buildings-unit-queue v-if="page.queue.length > 0" :queue="page.queue"></game-page-buildings-unit-queue>
		<div class="content page-building-items">
			<form :action="$root.getUrl('buildings/'+page.mode+'/')" method="post">
				<div class="row">
					<div class="col-12">
						<div class="c">
							<input type="submit" value="Построить">
						</div>
					</div>

					<game-page-buildings-unit-item v-for="item in page.items" :item="item"></game-page-buildings-unit-item>

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
	export default {
		name: "unit",
		computed: {
			page () {
				return this.$store.state.page;
			},
		},
		components: {
			'game-page-buildings-unit-item': require('./unit-row.vue'),
			'game-page-buildings-unit-queue': require('./unit-queue.vue'),
		},
	}
</script>