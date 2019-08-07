<template>
	<div class="page-tutorial tutorial-detail">
		<div class="table tutorial">
			<div class="row">
				<div class="col k">
					<h3>Задание {{ page['info']['TITLE'] }}</h3>
				</div>
			</div>
			<div class="row">
				<div class="col k text-left">
					<div class="row">
						<div class="col-4 text-center">
							<img :src="'/images/tutorial/'+page['stage']+'.jpg'" class="pic" alt="">
						</div>
						<div class="col-8">
							<div class="description" v-html="page['info']['DESCRIPTION']"></div>
							<h3>Задачи:</h3>
							<ul>
								<li v-for="task in page['task']">
									<span v-html="task[0]"></span>
									<span>
										<img :src="'/images/'+(task[1] ? 'check' : 'none')+'.gif'" height="11" width="12" alt="">
									</span>
								</li>
							</ul>
							<div style="color:orange;">
								Награда: <span v-html="page['rewd']"></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col k">
					<input v-if="!page['errors']" type="button" class="end" @click.prevent="$router.push('/tutorial/'+page['stage']+'/?continue=Y')" value="Закончить">
					<div class="solution" v-html="page['info']['SOLUTION']"></div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'tutorial-info',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>