<template>
	<div class="page-building page-building-unit">
		<game-page-buildings-unit-queue v-if="page.queue.length > 0" :queue="page.queue"></game-page-buildings-unit-queue>
		<div class="block">
			<div class="content">
				<form :action="$root.getUrl('buildings/'+page.mode+'/')" method="post">
					<div class="row shipyard">
						<div class="col-12 c">
							<input type="submit" value="Построить">
						</div>

						<game-page-buildings-unit-item v-for="item in page.items" :item="item"></game-page-buildings-unit-item>

						<div class="col-12 c">
							<input type="submit" value="Построить">
						</div>
					</div>
				</form>
			</div>
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