<template>
	<div class="page-chat">
		<div class="col-12 th">
			<div ref="chatbox" class="page-chat-messages">
				<div class="page-chat-history">
					<a @click="loadMore">загрузить прошлые сообщения</a>
				</div>
				<div v-for="item in messages" class="page-chat-messages-row text-left">
					<span :class="{date1: !item['me'] && !item['my'], date2: !!item['me'], date3: !!item['my']}" @click="toPrivate(item['user'])">{{ item['time']|date('H:i') }}</span>
					<span v-if="item['my']" class="negative">{{ item['user'] }}</span><span v-else="" class="to" @click="toPlayer(item['user'])">{{ item['user'] }}</span>:
					<span v-if="item['to'].length" :class="[item['private'] ? 'private' : 'player']">
						{{ item['private'] ? 'приватно' : 'для' }} [<span v-for="(u, i) in item['to']">{{ i > 0 ? ',' : '' }}<a v-if="!item['private']" @click.prevent="toPlayer(u)">{{ u }}</a><a v-else="" @click.prevent="toPrivate(u)">{{ u }}</a></span>]
					</span>
					<span class="page-chat-row-message" v-html="item['text']"></span>
				</div>
			</div>
		</div>
		<div class="col-12 th">
			<div class="float-right">
				<div class="editor-component-toolbar d-inline-block">
					<button type="button" class="buttons" title="Вставить ссылку" @click="addTag('[url]|[/url]', 1)">
						<span class="sprite bb_world_link"></span>
					</button>
					<button type="button" class="buttons" title="Вставить картинку" @click="addTag('[img]|[/img]', 3)">
						<span class="sprite bb_picture_add"></span>
					</button>
					<button type="button" class="buttons" title="Смайлы" @click="smiles = !smiles">
						<span class="sprite bb_emoticon_grin"></span>
					</button>
				</div>
				<div v-if="smiles" class="smiles">
					<img v-for="smile in smilesList" :src="'/images/smile/'+smile+'.gif'" :alt="smile" @click="addSmile(smile)">
				</div>
			</div>
			<input ref="text" class="page-chat-message" type="text" v-model="message" @keypress.13.prevent="sendMessage" maxlength="750">

			<input type="button" value="Очистить" @click.prevent="clear">
			<input type="button" value="Отправить" @click.prevent="sendMessage">
		</div>
	</div>
</template>

<script>
	import parser from '~/utils/parser'
	import io from 'socket.io-client'

	export default {
		name: 'chat',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		data () {
			// noinspection RegExpRedundantEscape
			return {
				smiles: false,
				smilesList: parser.patterns.smiles,
				message: '',
				message_id: 1,
				history_id: 0,
				messages: [],
				socket: null,
				patterns: {
					find: [
						/script/g,
						/\[b](.*?)\[\/b\]/gi,
						/\[i\](.*?)\[\/i\]/gi,
						/\[u\](.*?)\[\/u\]/gi,
						/\[s\](.*?)\[\/s\]/gi,
						/\[left\](.*?)\[\/left\]/gi,
						/\[center\](.*?)\[\/center\]/gi,
						/\[right\](.*?)\[\/right\]/gi,
						/\[justify\](.*?)\[\/justify\]/gi,
						/\[size=([1-9]|1[0-9]|2[0-5])\](.*?)\[\/size\]/gi,
						/\[img\](https?:\/\/.*?\.(?:jpg|jpeg|png))\[\/img\]/gi,
						/\[url=((?:ftp|https?):\/\/.*?)\](.*?)\[\/url\]/g,
						/\[url\]((?:ftp|https?):\/\/.*?)\[\/url\]/g,
						/\[p\](.*?)\[\/p\]/gi,
						/\[([1-9]):([0-9]{1,3}):([0-9]{1,2})\]/gi
					],
					replace: [
						'',
						'<strong>$1</strong>',
						'<em>$1</em>',
						'<span style="text-decoration: underline;">$1</span>',
						'<span style="text-decoration: line-through;">$1</span>',
						'<div align="left">$1<\/div>',
						'<div align="center">$1<\/div>',
						'<div align="right">$1<\/div>',
						'<div style="text-align:justify;">$1<\/div>',
						'<span style="font-size: $1px;">$2</span>',
						'<a href="$1" target="_blank"><img src="$1" style="max-width:350px;" alt=""></a>',
						'<a href="$1" target="_blank">$2</a>',
						'<a href="$1" target="_blank">$1</a>',
						'<p>$1</p>',
						'<a href="'+this.$store.state.path+'galaxy/?galaxy=$1&system=$2">[$1:$2:$3]</a>'
					]
				}
			}
		},
		watch: {
			message () {
				this.$refs['text'].focus();
			}
		},
		methods: {
			scrollToBottom ()
			{
				if (this.$refs['chatbox'])
					this.$refs['chatbox'].scrollTop = this.$refs['chatbox'].scrollHeight;
			},
			addTag (tag, type)
			{
				let len 	= this.message.length;
				let start 	= this.$refs.text.selectionStart;
				let end 	= this.$refs.text.selectionEnd;

				let rep = parser.addTag(tag, this.message.substring(start, end), type)

				this.message = this.message.substring(0, start) + rep + this.message.substring(end, len);
			},
			addSmile (smile)
			{
				this.message = this.message+' :'+smile+':';
				this.smiles = false;
			},
			toPlayer (user) {
				this.message = 'для ['+user+'] '+this.message;
			},
			toPrivate (user) {
				this.message = 'приватно ['+user+'] '+this.message;
			},
			clear () {
				this.messages = [];
				this.smiles = false;
			},
			reformat (message)
			{
				this.patterns.find.forEach((item, i) => {
					message['text'] = message['text'].replace(item, this.patterns.replace[i]);
				});

				let j = 0;

				parser.patterns.smiles.every((smile) =>
				{
					while (message['text'].indexOf(':'+smile+':') >= 0)
					{
						message['text'] = message['text'].replace(':'+smile+':', '<img src="/images/smile/'+smile+'.gif" alt="'+smile+'">');

						if (++j >= 3)
							break;
					}

					return j < 3;
				})

				return message;
			},
			loadMore () {
				this.socket.emit('history', this.history_id, this.$store.state.user.name);
			},
			sendMessage ()
			{
				this.smiles = false;

				let message = this.message;

				while (message.indexOf('\'') >= 0)
					message = message.replace('\'', '`');

				this.message = '';

				this.socket.send(
					encodeURIComponent(message),
					this.$store.state.user.id,
					this.$store.state.user.name,
					this.$store.state['user']['color'],
					this.$store.state['chat']['key']
				);
			},
			init ()
			{
				this.socket.on('connecting', () =>
				{
					this.messages.push({
						time: this.$store.getters.getServerTime(),
						user: '',
						to: [],
						text: 'Соединение...',
						private: 0,
						me: 0,
						my: 0
					});
				});

				this.socket.on('connect', () =>
				{
					this.messages.push({
						time: this.$store.getters.getServerTime(),
						user: '',
						to: [],
						text: 'Соединение установлено',
						private: 0,
						me: 0,
						my: 0
					});

					this.socket.on('message', (message) =>
					{
						if (message['id'] <= this.message_id)
							return false;

						this.message_id = message['id'];

						if (message['id'] < this.history_id || this.history_id === 0)
							this.history_id = message['id'];

						message = this.reformat(message);

						this.messages.push({
							id: message['id'],
							time: message['time'],
							user: message['user'],
							to: message['to'],
							text: message['text'],
							private: message['private'],
							me: message['me'],
							my: message['my']
						});

						setTimeout(() => {
							this.scrollToBottom()
						}, 250);
					});
				});

				this.socket.on('history', (list) =>
				{
					if (list.length === 0)
						return;

					this.messages.reverse();

					list.forEach((message) =>
					{
						message = this.reformat(message);

						if (message['id'] < this.history_id || this.history_id === 0)
							this.history_id = message['id'];

						this.messages.push({
							id: message['id'],
							time: message['time'],
							user: message['user'],
							to: message['to'],
							text: message['text'],
							private: message['private'],
							me: message['me'],
							my: message['my']
						});
					})

					this.messages.reverse();

					this.$refs['chatbox'].scrollTop = 0;
				});
			}
		},
		mounted ()
		{
			this.socket = io.connect(this.$store.state['chat']['server'], {
				query: 'userId='+this.$store.state.user.id+'&userName='+this.$store.state.user.name+'&key='+this.$store.state['chat']['key'],
				secure: true
			})

			this.init()

			window.addEventListener('resize', this.scrollToBottom, true)
		},
		beforeDestroy ()
		{
			window.removeEventListener('resize', this.scrollToBottom)

			this.socket.close()
			this.socket = null
		}
	}
</script>