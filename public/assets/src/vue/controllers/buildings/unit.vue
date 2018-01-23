<template>
	<div>
		<div class="block">
			<div class="content">
				<form :action="$root.getUrl('buildings/'+page.mode+'/')" method="post">
					<div class="row shipyard">
						<div class="col-12 c">
							<input type="submit" value="Построить">
						</div>

						<game-page-buildings-unit-item v-for="item in page.items" v-bind:item="item"></game-page-buildings-unit-item>

						<div class="col-12 c">
							<input type="submit" value="Построить">
						</div>
					</div>
				</form>
			</div>
		</div>

		<game-page-buildings-unit-queue v-if="page.queue.length > 0" v-bind:queue="page.queue"></game-page-buildings-unit-queue>
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