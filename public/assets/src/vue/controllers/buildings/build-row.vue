<template>
	<div class="col-md-6 col-xs-12" v-bind:id="'object_'+item.i">
		<div v-bind:class="['viewport', 'buildings', (!item.allow ? 'shadow' : '')]">
			<div v-if="item.allow === false" class="notAvailable tooltip" v-on:click="openWindow">
				<div class="tooltip-content" v-if="item.need">
					Требования:
					<br>
					<div v-html="item.need"></div>
				</div>
				<span>недоступно</span>
			</div>

			<div class="img">
				<a href="javascript:;" v-on:click="openWindow">
					<img v-bind:src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" align="top" alt="" class="tooltip img-responsive" v-bind:data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
				</a>
				<game-page-buildings-build-row-price v-bind:price="item.price"></game-page-buildings-build-row-price>
			</div>
			<div class="title">
				<a v-bind:href="$root.getUrl('info/'+item.i+'/')">{{ item.name }}</a>
			</div>
			<div class="actions">
				Уровень: <span v-bind:class="[(item.level > 0 ? 'positive' : 'negative')]">{{ Format.number(item.level) }}</span><br>
				<div v-if="item.allow">
					Время: {{ Format.time(item.time) }}<br>
					<div v-html="item.add"></div>
					<div class="startBuild">
						<a v-if="item.action === 'allow'" v-bind:href="$root.getUrl('buildings/index/cmd/insert/building/'+item.i+'/')">
							<span class="resYes">
								{{ item.level ? 'Улучшить' : 'Построить' }}
								<span v-if="item.exp" class="exp">(+{{ item.exp }} exp)</span>
							</span>
						</a>
						<span v-else-if="item.action === 'resources'" class="resNo">нет ресурсов</span>
						<a v-else-if="item.action === 'queue'" v-bind:href="$root.getUrl('buildings/index/cmd/insert/building/'+item.i+'/')">
							<span class="resYes">
								В очередь
								<span v-if="item.exp" class="exp">(+{{ item.exp }} exp)</span>
							</span>
						</a>
						<span v-else-if="item.action === 'wait'" class="resNo">{{ item.level ? 'Улучшить' : 'Построить' }}</span>
						<span v-else-if="item.action === 'fields'" class="resNo">нет места</span>
						<span v-else-if="item.action === 'working'" class="resNo">занято</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build-row",
		props: ['item'],
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue')
		},
		methods: {
			openWindow: function ()
			{
				showWindow('', this.$root.getUrl('info/'+this.item.i+'/'), 600)
			}
		}
	}
</script>