<template>
	<div class="page-chat">
		<div class="col-12 th">
			<div ref="chatbox" class="page-chat-messages">
				<div v-for="item in messages" class="page-chat-row text-left">
					<span :class="{date1: !item['me'] && !item['my'], date2: !!item['me'], date3: !!item['my']}" v-on:click="toPlayer(item['user'])" style="cursor:pointer;">{{ date('H:m', item['time']) }}</span>
					<span v-if="item['my']" class="negative">{{ item['user'] }}</span><span v-else="" class="to" v-on:click="toPlayer(item['user'])">{{ item['user'] }}</span>:
					<span v-if="item['to'].length" :class="[item['private'] ? 'private' : 'player']">
						{{ item['private'] ? 'приватно' : 'для' }} [<span v-for="(u, i) in item['to']">{{ i > 0 ? ',' : '' }}<a v-if="!item['private']" v-on:click="toPlayer(u)">{{ u }}</a><a v-else="" v-on:click="toPrivate(u)">{{ u }}</a></span>]
					</span>
					<span class="page-chat-row-message" v-html="item['text']"></span>
				</div>
			</div>
		</div>
		<div class="col-12 th">
			<div class="float-right">
				<div class="toolbar d-inline-block">
					<span class="buttons" title="Вставить ссылку" v-on:click="addTag('[url]','[/url]', 1)"><span class="sprite bb_world_link"></span></span>
					<span class="buttons" title="Вставить картинку" v-on:click="addTag('[img]','[/img]', 3)"><span class="sprite bb_picture_add"></span></span>
					<span class="buttons" title="Смайлы" v-on:click="smiles = !smiles"><span class="sprite bb_emoticon_grin"></span></span>
				</div>
				<div v-if="smiles" class="smiles">
					<img v-for="smile in parser.patterns.smiles" :src="$root.getUrl('assets/images/smile/'+smile+'.gif')" :alt="smile" v-on:click="addSmile(smile)" style="cursor:pointer">
				</div>
			</div>
			<input ref="message" class="page-chat-message" type="text" v-model="message" v-on:keypress="$event.keyCode === '13' ? sendMessage : false" maxlength="750" title="">
			<div class="separator"></div>
			<input type="button" name="" value="Очистить" v-on:click.prevent="clear">
			<input type="button" name="" value="Отправить" v-on:click.prevent="sendMessage">
		</div>
	</div>
</template>

<script>
	export default {
		name: "chat",
		computed: {
			page () {
				return this.$store.state.page;
			}
		},
		data () {
			// noinspection RegExpRedundantEscape
			return {
				smiles: false,
				message: '',
				message_id: 1,
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
						'<a href="$1" target="_blank"><img src="$1" style="max-width:350px;" alt="" /></a>',
						'<a href="$1" target="_blank">$2</a>',
						'<a href="$1" target="_blank">$1</a>',
						'<p>$1</p>',
						'<a href="'+this.$store.state.path+'galaxy/$1/$2/">[$1:$2:$3]</a>'
					]
				}
			}
		},
		watch: {
			messages () {
				Vue.nextTick(() => {
					$(this.$refs['chatbox']).scrollTop($(this.$refs['chatbox']).height());
				});
			}
		},
		methods: {
			addTag (open, close, type) {
				this.message = addTagToElement(open, close, type, this.$refs['message'])
			},
			addSmile (smile)
			{
				this.message = this.message+' :'+smile+':';
				this.smiles = false;

				$(this.$refs['message']).focus();
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
			sendMessage ()
			{
				this.smiles = false;

				let message = this.message;

				while (message.indexOf('\'') >= 0)
					message = message.replace('\'', '`');

				this.message = '';

				this.socket.send(encodeURIComponent(message), this.$store.state.user.id, this.$store.state.user.name, this.page['color'], this.page['key']);
			},
			init ()
			{
				this.socket.on('connecting', () =>
				{
					this.messages.push({
						time: this.$root.serverTime(),
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
						time: this.$root.serverTime(),
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

						this.patterns.find.forEach((item, i) => {
							message['text'] = message['text'].replace(item, this.patterns.replace[i]);
						});

						this.messages.push({
							time: message['time'],
							user: message['user'],
							to: message['to'],
							text: message['text'],
							private: message['private'],
							me: message['me'],
							my: message['my']
						});
					});
				});
			}
		},
		created () {
			this.socket = io.connect(this.page['server'], {query: 'userId='+this.$store.state.user.id+'&userName='+this.$store.state.user.name+'&key='+this.page['key'], secure: true});
			this.init();
		}
	}
</script>