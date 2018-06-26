<template>
	<tabs v-if="page" class="page-techtree">
		<tab v-for="(groups, i) in page" :key="i" :name="groups['name']" class="container-fluid">
			<div v-for="list in groups['items']" class="row">
				<div class="col-sm-5 col-6 title">
					<router-link :to="'/info/'+list['info']+'/'">{{ list['name'] }}</router-link>
					<div v-if="list['required'] !== ''" class="float-right d-none d-sm-block">
						<router-link :to="'/tech/'+list['info']+'/'">[i]</router-link>
					</div>
				</div>
				<div class="col-sm-7 col-6" v-html="list['required']"></div>
			</div>
		</tab>
	</tabs>
</template>

<script>
	import router from 'router-mixin'

	export default {
		name: "techtree",
		mixins: [router],
	}
</script>