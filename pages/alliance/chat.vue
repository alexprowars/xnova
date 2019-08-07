<template>
	<div class="page-alliance-chat">
		<router-form action="/alliance/chat/">
			<div class="table">
				<div class="row">
					<div class="col-12 c">
						<nuxt-link to="/alliance/chat/">Обновить</nuxt-link>
					</div>
				</div>

				<div v-for="(item, index) in page['items']" class="row">
					<div class="col-2 b text-center">
						{{ item['time']|date('H:i:s') }}
						<br>
						<a :href="'/players/'+item['user_id']+'/'" target="_blank">{{ item['user'] }}</a>
						<a @click.prevent="quote(index)"> -> </a>
					</div>
					<div class="col-9 b">
						<text-viewer v-if="page['parser']" :text="item['text']"></text-viewer>
						<div v-else="">{{ item['text'] }}</div>
					</div>
					<div v-if="page['owner']" class="col-1 b text-center">
						<input name="delete[]" type="checkbox" :value="item['id']" v-model="marked">
					</div>
				</div>

				<div v-if="page['items'].length === 0" class="row">
					<div class="col-12 b" align="center">В альянсе нет сообщений.</div>
				</div>

				<div class="row">
					<div class="col-12 th">
						<pagination :options="page['pagination']"></pagination>
					</div>
				</div>

				<div v-if="marked.length" class="row">
					<div class="col-12 th">
						<select id="deletemessages" name="delete_type">
							<option value="marked">Удалить выделенные</option>
							<option value="unmarked">Удалить не выделенные</option>
							<option value="all">Удалить все</option>
						</select>
						<input value="Удалить" type="submit">
					</div>
				</div>
			</div>
		</router-form>
		<div class="separator"></div>
		<router-form action="/alliance/chat/">
			<table class="table">
				<tr>
					<td class="c">Отправить сообщение в чат альянса</td>
				</tr>
				<tr>
					<th class="p-a-0">
						<text-editor v-model="text"></text-editor>
					</th>
				</tr>
				<tr>
					<td class="c">
						<input type="reset" value="Очистить">
						<input type="submit" value="Отправить" @click="text = ''">
					</td>
				</tr>
			</table>
		</router-form>
		<span style="float:left;margin-left:10px;margin-top:7px;">
			<nuxt-link to="/alliance/">[назад к альянсу]</nuxt-link>
		</span>
	</div>
</template>

<script>
	export default {
		name: 'alliance_chat',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		data () {
			return {
				text: '',
				marked: []
			}
		},
		methods: {
			quote (messageIndex)
			{
				if (typeof this.page['items'][messageIndex] === 'undefined')
					return;

				let message = this.page['items'][messageIndex];

				let text = message['text'];
				text = text.replace(/<br>/gi, "\n");
			    text = text.replace(/<br \/>/gi, "\n");

				this.text = this.text + '[quote author='+message['user']+']'+text+'[/quote]';
			}
		}
	}
</script>