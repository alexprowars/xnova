<template>
	<tabs class="page-techtree">
		<tab v-for="(groups, i) in page" :key="i" :name="groups['name']" class="container-fluid">
			<div v-for="list in groups['items']" class="row">
				<div class="col-sm-5 col-6 title">
					<nuxt-link :to="'/info/'+list['info']+'/'">{{ list['name'] }}</nuxt-link>
					<div v-if="list['required'] !== ''" class="float-right d-none d-sm-block">
						<nuxt-link :to="'/tech/'+list['info']+'/'">[i]</nuxt-link>
					</div>
				</div>
				<div class="col-sm-7 col-6" v-html="list['required']"></div>
			</div>
		</tab>
	</tabs>
</template>

<script>
	export default {
		name: 'techtree',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
	}
</script>