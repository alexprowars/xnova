<template>
	<div class="page-overview-delete">
		<div class="block">
			<div class="title">Система безопасности</div>
			<div class="content border-0">
				<router-form action="/overview/delete/">
					<input type="hidden" name="id" :value="page['id']">
					<input type="hidden" name="password" :value="page['number_check']">
					<div class="table">
						<div class="row">
							<div class="col th">
								Подтвердите удаление планеты {{ page['galaxy'] }}:{{ page['system'] }}:{{ page['planet'] }} вводом правильного ответа
							</div>
						</div>
						<div class="row">
							<div class="col th">{{ page['number_1'] }} + {{ page['number_2'] }} * {{ page['number_3'] }} = ???</div>
							<div class="col th"><input type="text" name="pw"></div>
							<div class="col th"><input type="submit" name="action" value="Удалить колонию"></div>
						</div>
					</div>
				</router-form>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'overview-delete',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>