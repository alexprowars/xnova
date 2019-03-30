<template>
	<div v-if="!mobile" class="component-chat" :class="{active: active}">
		<div class="block">
			<div class="title" v-on:click="toggleActive">
				Чат
				<span v-if="newMessages > 0">({{ newMessages }})</span>
				<span v-if="socket !== null" class="positive float-right">активен</span>
				<span v-else="" class="negative float-right">не активен</span>
			</div>
			<div v-show="active" class="content border-0">
				<div class="col-12 th">
					<div ref="chatbox" class="page-chat-messages">
						<div v-for="item in messages" class="page-chat-messages-row text-left">
							<span :class="{date1: !item['me'] && !item['my'], date2: !!item['me'], date3: !!item['my']}" v-on:click="toPrivate(item['user'])">{{ item['time']|date('H:m') }}</span>
							<span v-if="item['my']" class="negative">{{ item['user'] }}</span><span v-else="" class="to" v-on:click="toPlayer(item['user'])">{{ item['user'] }}</span>:
							<span v-if="item['to'].length" :class="[item['private'] ? 'private' : 'player']">
								{{ item['private'] ? 'приватно' : 'для' }} [<span v-for="(u, i) in item['to']">{{ i > 0 ? ',' : '' }}<a v-if="!item['private']" v-on:click.prevent="toPlayer(u)">{{ u }}</a><a v-else="" v-on:click.prevent="toPrivate(u)">{{ u }}</a></span>]
							</span>
							<span class="page-chat-row-message" v-html="item['text']"></span>
						</div>
					</div>
				</div>
				<div class="col-12 th d-flex">
					<input ref="text" class="page-chat-message" type="text" v-model="message" v-on:keypress.13.prevent="sendMessage" maxlength="750">

					<input type="button" value="Отправить" v-on:click.prevent="sendMessage">
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import parser from '~/utils/parser'
	import io from 'socket.io-client'

	export default {
		name: "chat",
		props: {
			visible: {
				type: Boolean,
				default: false,
			}
		},
		data () {
			return {
				mobile: this.$store.getters.isMobile || !this.visible,
				active: localStorage.getItem('mini-chat-active') === 'Y',
				messages: [],
				message: '',
				message_id: 1,
				newMessages: 0,
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
						'<a href="$1" target="_blank">[ИЗОБРАЖЕНИЕ]</a>',
						'<a href="$1" target="_blank">$2</a>',
						'<a href="$1" target="_blank">$1</a>',
						'<p>$1</p>',
						'<a href="'+this.$store.state.path+'galaxy/?galaxy=$1&system=$2">[$1:$2:$3]</a>'
					]
				}
			}
		},
		watch: {
			messages () {
				setTimeout(() => {
					this.scrollToBottom()
				}, 250);
			},
			message () {
				this.$refs['text'].focus();
			},
			mobile (value)
			{
				if (!value)
				{
					if (this.socket)
						this.socket.open();
					else if (this.active)
						this.init();
				}
				else
				{
					if (this.socket)
						this.socket.close();
				}
			},
			visible (value) {
				this.mobile = this.$store.getters.isMobile || !value
			}
		},
		methods: {
			scrollToBottom () {
				this.$refs['chatbox'].scrollTop = this.$refs['chatbox'].scrollHeight;
			},
			toggleActive ()
			{
				this.active = !this.active;

				try {
					localStorage.setItem('mini-chat-active', this.active ? 'Y' : 'N')
				} catch (e) {}

				if (this.active && this.socket === null)
					this.init();

				this.newMessages = 0;

				this.$nextTick(() => {
					this.scrollToBottom()
				});
			},
			toPlayer (user) {
				this.message = 'для ['+user+'] '+this.message;
			},
			toPrivate (user) {
				this.message = 'приватно ['+user+'] '+this.message;
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
					this.$store.state.user.color,
					this.$store.state['chat']['key']
				);
			},
			init ()
			{
				this.socket = io.connect(this.$store.state['chat']['server'], {
					query: 'userId='+this.$store.state.user.id+'&userName='+this.$store.state.user.name+'&key='+this.$store.state['chat']['key'],
					secure: true
				});

				this.socket.on('connect', () =>
				{
					this.socket.on('message', (message) =>
					{
						if (message['id'] <= this.message_id)
							return false;

						if (!this.active)
							this.newMessages++;

						this.message_id = message['id'];

						this.patterns.find.forEach((item, i) => {
							message['text'] = message['text'].replace(item, this.patterns.replace[i]);
						});

						let j = 0;

						parser.patterns.smiles.every((smile) =>
						{
							while (message['text'].indexOf(':'+smile+':') >= 0)
							{
								message['text'] = message['text'].replace(':'+smile+':', '<img src="/images/smile/'+smile+'.gif">');

								if (++j >= 3)
									break;
							}

							return j < 3;
						})

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
			},
			onResize ()
			{
				if (this.active)
					this.scrollToBottom();
			}
		},
		mounted ()
		{
			if (this.active && !this.mobile)
				this.init();

			window.addEventListener('resize', this.onResize, true);
		},
		destroyed () {
			window.removeEventListener('resize', this.onResize)
		}
	}
</script>