<template>
	<tabs class="page-techtree">
		<tab v-for="(groups, i) in page" :key="i" :name="groups['name']" class="container-fluid">
			<div v-for="list in groups['items']" class="row">
				<div class="col-sm-5 col-6 title">
					<a :href="$root.getUrl('info/'+list['info']+'/')">{{ list['name'] }}</a>
					<div v-if="list['required'] !== ''" class="float-right d-none d-sm-block">
						<a :href="$root.getUrl('tech/'+list['info']+'/')">[i]</a>
					</div>
				</div>
				<div class="col-sm-7 col-6" v-html="list['required']"></div>
			</div>
		</tab>
	</tabs>
</template>

<script>
	export default {
		name: "techtree",
		computed: {
			page () {
				return this.$store.state.page;
			}
		},
	}
</script>