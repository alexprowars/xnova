<template>
	<router-form action="/alliance/admin/edit/tag/">
		<div class="block">
			<div class="title">Введите новую аббревиатуру альянса</div>
			<div class="content table border-0 middle">
				<div class="row">
					<div class="col th">
						<input type="text" name="tag" :value="page['tag']">
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
		name: 'alliance-edit-tag',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>