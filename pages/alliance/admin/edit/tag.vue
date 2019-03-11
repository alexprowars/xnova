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
		name: "alliance-edit-tag",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>