<template>
	<table v-if="page" class="table">
		<tr>
			<td colspan="3" class="c"><b>Начальство</b></td>
		</tr>
		<tr>
			<th colspan="3">
				<font color="orange">Здесь вы найдёте адреса всех администраторов и операторов игры для обратной связи</font>
			</th>
		</tr>
		<tr>
			<th width="33%">Имя</th>
			<th width="33%">Должность</th>
			<th width="33%">eMail</th>
		</tr>
		<template v-for="item in page.items">
			<tr>
				<th>{{ item['name'] }}</th>
				<th>{{ item['auth'] }}</th>
				<th><a :href="'mailto:'+item['mail']">{{ item['mail'] }}</a></th>
			</tr>
			<tr v-if="item['info'].length > 0">
				<td class="c" colspan="3">
					<text-viewer :text="item['info']"></text-viewer>
				</td>
			</tr>
		</template>
	</table>
</template>

<script>
	import router from 'router-mixin'

	export default {
		name: "contacts",
		mixins: [router]
	}
</script>