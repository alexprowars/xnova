<template>
	<router-form :action="'/alliance/admin/edit/give/id/'+page['id']+'/'">
		<table class="table">
			<tr>
				<td class="c" colspan="8">Передача альянса</td>
			</tr>
			<tr>
				<th colspan="3">Передать альянс игроку:</th>
				<th><select name="newleader" title="">{{ page['righthand'] }}</select></th>
				<th colspan="3"><input type="submit" value="Передача"></th>
			</tr>
			<tr>
				<td class="c" colspan="8"><nuxt-link to="/alliance/admin/edit/ally/">назад</nuxt-link></td>
			</tr>
		</table>
	</router-form>
</template>

<script>
	export default {
		name: 'alliance-edit-transfer',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>