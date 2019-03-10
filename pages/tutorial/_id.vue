<template>
	<table class="table tutorial">
		<tbody>
		<tr>
			<td class="k">
				<h3>Задание {{ page['info']['TITLE'] }}</h3>
			</td>
		</tr>
		<tr>
			<td class="k text-left">
				<div class="row">
					<div class="col-4 text-center">
						<img :src="'/images/tutorial/'+page['stage']+'.jpg'" class="pic" alt="">
					</div>
					<div class="col-8">
						<div class="description" v-html="page['info']['DESCRIPTION']"></div>
						<h3>Задачи:</h3>
						<ul>
							<li v-for="task in page['task']">
								<span>{{ task[0] }}</span>
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
			</td>
		</tr>
		<tr>
			<td class="k">
				<input v-if="!page['errors']" type="button" class="end" @click.prevent="$router.push('/tutorial/'+page['stage']+'/?continue=Y')" value="Закончить">
				<div class="solution" v-html="page['info']['SOLUTION']"></div>
			</td>
		</tr>
		</tbody>
	</table>
</template>

<script>
	export default {
		name: "tutorial-info",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>