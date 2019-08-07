<template>
	<router-form ref="form" action="/messages/">
		<div class="block">
			<div class="title">
				Сообщения
				<select name="category" @change.prevent="submitForm" v-model="page['category']">
					<option v-for="(type, index) in $t('MESSAGE_TYPES')" :value="index">{{ type }}</option>
				</select>
				по
				<select name="limit" @change.prevent="submitForm" v-model="page['limit']">
					<option v-for="i in limit" :value="i">{{ i }}</option>
				</select>
				на странице
				<div v-if="deleteItems.length > 0" class="d-inline-block">
					<input name="deletemessages" value="Удалить отмеченные" type="button" @click.prevent="submitForm">
				</div>
			</div>
			<div class="content noborder">
				<div class="table">
					<div class="row">
						<div class="col-1 th text-center">
							<input type="checkbox" class="checkAll" v-model="checkAll">
						</div>
						<div class="col-3 th text-center">Дата</div>
						<div class="col-6 th text-center">От</div>
						<div class="col-2 th text-center"></div>
					</div>

					<messages-row v-for="item in messages" :key="item['id']" :item="item"></messages-row>

					<div v-if="page['pagination']['total'] === 0" class="row">
						<div class="col-12 th text-center">нет сообщений</div>
					</div>
				</div>

				<div v-if="page['pagination']['total'] > page['pagination']['limit']" class="float-left">
					<pagination :options="page['pagination']"></pagination>
				</div>
				<div v-if="deleteItems.length > 0" class="float-right" style="padding: 5px">
					<input name="deletemessages" value="Удалить отмеченные" type="button" @click.prevent="submitForm">
				</div>
			</div>
		</div>
	</router-form>
</template>

<script>
	import MessagesRow from '~/components/page/messages/row.vue'

	export default {
		name: 'messages',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		components: {
			MessagesRow
		},
		computed: {
			messages ()
			{
				if (!this.page.items)
					return [];

				this.page.items.forEach((item) => {
					this.$set(item, 'deleted', false);
				});

				return this.page.items;
			},
			deleteItems ()
			{
				let del = [];

				this.messages.forEach((item, i) =>
				{
					if (item.deleted === true)
						del.push(i);
				});

				return del;
			}
		},
		data () {
			return {
				checkAll: false,
				limit: [5, 10, 25, 50, 100, 200]
			}
		},
		watch: {
			checkAll (value)
			{
				this.messages.forEach((item) => {
					item.deleted = value;
				});
			}
		},
		methods: {
			submitForm () {
				this.$refs['form'].send()
			}
		}
	}
</script>