<template>
	<div class="page-alliance-chat">
		<form :action="$root.getUrl('alliance/chat/')" method="post">
			<div class="table">
				<div class="row">
					<div class="col-12 c">
						<a :href="$root.getUrl('alliance/chat/')">Обновить</a>
					</div>
				</div>

				<div v-for="(item, index) in page['items']" class="row">
					<div class="col-2 b text-center">
						{{ date("H:i:s", item['time']) }}
						<br>
						<a :href="$root.getUrl('players/'+item['user_id']+'/')" target="_blank">{{ item['user'] }}</a>
						<a @click.prevent="quote(index)"> -> </a>
					</div>
					<div class="col-9 b">
						<text-viewer v-if="page['parser']" :text="item['text']"></text-viewer>
						<div v-else="">{{ item['text'] }}</div>
					</div>
					<div v-if="page['owner']" class="col-1 b text-center">
						<input name="delete[]" type="checkbox" :value="item['id']" title="" v-model="marked">
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
						<select id="deletemessages" name="deletemessages" title="">
							<option value="deletemarked">Удалить выделенные</option>
							<option value="deleteunmarked">Удалить не выделенные</option>
							<option value="deleteall">Удалить все</option>
						</select>
						<input value="Удалить" type="submit">
					</div>
				</div>
			</div>
		</form>
		<div class="separator"></div>
		<form :action="$root.getUrl('alliance/chat/')" method="post">
			<table class="table">
				<tr>
					<td class="c">Отправить сообщение в чат альянса</td>
				</tr>
				<tr>
					<th class="p-a-0">
						<text-editor :text="text"></text-editor>
					</th>
				</tr>
				<tr>
					<td class="c">
						<input type="reset" value="Очистить">
						<input type="submit" value="Отправить">
					</td>
				</tr>
			</table>
		</form>
		<span style="float:left;margin-left:10px;margin-top:7px;">
			<a :href="$root.getUrl('alliance/')">[назад к альянсу]</a>
		</span>
	</div>
</template>

<script>
	export default {
		name: "alliance_chat",
		computed: {
			page () {
				return this.$store.state.page;
			}
		},
		data () {
			return {
				text: '',
				marked: []
			}
		},
		methods: {
			quote (messageIndex) {

			}
		}
	}
</script>