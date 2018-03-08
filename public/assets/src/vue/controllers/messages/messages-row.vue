<template>
	<div>
		<div class="row">
			<div class="col-1 th text-center">
				<input name="delete[]" type="checkbox" :value="item['id']" v-model="item['deleted']" title="Удалить">
			</div>
			<div class="col-3 th text-center">{{ date("d.m.y H:i:s", item['time']) }}</div>
			<div class="col-6 th text-center">
				<a v-if="item['from'] > 0" :href="$root.getUrl('players/'+item['from']+'/')" class="window popup-user">
					{{ item['theme'] }}
				</a>
				<span v-else="">
					{{ item['theme'] }}
				</span>
			</div>
			<div class="col-2 th text-center">
				<span v-if="item['type'] === 1">
					<a :href="$root.getUrl('messages/write/'+item['from']+'/')" title="Ответить">
						<span class="sprite skin_m"></span>
					</a>
					<a :href="$root.getUrl('messages/write/'+item['from']+'/quote/'+item['id']+'/')" title="Цитировать сообщение">
						<span class="sprite skin_z"></span>
					</a>
					<a v-on:click.prevent="abuse" title="Отправить жалобу">
						<span class="sprite skin_s"></span>
					</a>
				</span>
			</div>
		</div>
		<div class="row">
			<div :style="'background-color:'+$root.getLang('MESSAGE_TYPES_BACKGROUNDS', item['type'])" class="col-12 b">
				<div v-if="$parent.page['parser']">
					<text-viewer :text="item['text']"></text-viewer>
				</div>
				<div v-else="">
					{{ item['text'] }}
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "messages-row",
		props: ['item'],
		methods: {
			abuse ()
			{
				if (window.confirm("Вы уверены что хотите отправить жалобу на это сообщение?"))
					window.location.href = $root.getUrl('messages/abuse/'+item['id']+'/');
			}
		}
	}
</script>