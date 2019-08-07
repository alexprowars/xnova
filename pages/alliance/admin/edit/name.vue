<template>
	<router-form action="/alliance/admin/edit/name/">
		<div class="block">
			<div class="title">Введите новое название альянса</div>
			<div class="content table border-0 middle">
				<div class="row">
					<div class="col th">
						<input type="text" name="name" :value="page['name']">
						<input type="submit" value="Изменить">
					</div>
				</div>
				<div class="row c">
					<nuxt-link to="/alliance/admin/edit/ally/">вернутся к обзору</nuxt-link>
				</div>
			</div>
		</div>
	</router-form>
</template>

<script>
	export default {
		name: 'alliance-edit-name',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>