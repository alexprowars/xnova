<template>
	<form :action="$root.getUrl('messages/')" method="post">
		<div class="block">
			<div class="title">
				Сообщения
				<select name="category" title="" v-on:change.prevent="submitForm" v-model="page['category']">
					<option v-for="(type, index) in $root.getLang('MESSAGE_TYPES')" :value="index">{{ type }}</option>
				</select>
				по
				<select name="limit" title="" v-on:change.prevent="submitForm" v-model="page['limit']">
					<option v-for="i in limit" :value="i">{{ i }}</option>
				</select>
				на странице
				<div style="float: right">
					<input name="deletemessages" value="Удалить отмеченные" type="submit">
				</div>
			</div>
			<div class="content noborder">
				<div class="table">
					<div class="row">
						<div class="col-1 text-center">
							<input type="checkbox" class="checkAll" style='width:14px;' title="">
						</div>
						<div class="col-3 text-center">Дата</div>
						<div class="col-6 text-center">От</div>
						<div class="col-2 text-center"></div>
					</div>

					<game-page-messages-row v-for="item in page.items" :item="item"></game-page-messages-row>

					<div v-if="page['pagination']['total'] === 0" class="row">
						<div class="col-12 th text-center">нет сообщений</div>
					</div>
				</div>

				<div style="float: left">
					<pagination :options="page['pagination']"></pagination>
				</div>
				<div style="float: right;padding: 5px">
					<input name="deletemessages" value="Удалить отмеченные" type="submit">
				</div>
			</div>
		</div>
	</form>
</template>

<script>
	export default {
		name: "messages",
		components: {
			'game-page-messages-row': require('./messages-row.vue'),
		},
		computed: {
			page () {
				return this.$store.state.page;
			},
		},
		data () {
			return {
				limit: [5, 10, 25, 50, 100, 200]
			}
		},
		methods: {
			submitForm (event) {
				$(event.target).closest('form').submit();
			}
		}
	}
</script>