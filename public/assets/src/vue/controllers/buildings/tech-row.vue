<template>
	<div class="col-md-6 col-12">
		<div class="viewport buildings" :class="{shadow: !item.allow}">
			<div v-if="item.allow === false" class="notAvailable tooltip" v-on:click="openWindow">
				<div class="tooltip-content" v-if="item.need">
					Требования:
					<br>
					<div v-html="item.need"></div>
				</div>
				<span>недоступно</span>
			</div>

			<div class="img">
				<a v-on:click="openWindow">
					<img :src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" align="top" width="120" height="120" class="tooltip" :data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
				</a>
				<div class="overContent">
					<game-page-buildings-build-row-price v-bind:price="item.price"></game-page-buildings-build-row-price>
				</div>
			</div>
			<div class="title">
				<a v-bind:href="$root.getUrl('info/'+item.i+'/')">
					{{ item.name }}
				</a>
			</div>
			<div class="actions">
				Уровень:
				<span :class="{positive: item.level > 0, negative: item.level === 0}">
					{{ Format.number(item.level) }}
					<span v-if="item.max > 0">из <font color="yellow">{{ Format.number(item.max) }}</font></span>
				</span>
				<br>

				<div v-if="item.allow">
					Время: {{ Format.time(item.time) }}<br>
					<div v-html="item.effects"></div>

					<div class="startBuild">
						<span v-if="item.action === 'max'" class="negative">максимальный уровень</span>
						<span v-else-if="item.action === 'working'" :class="{positive: item.level, negative: item.level === 0}">{{ item.level ? 'Улучшить' : 'Исследовать' }}</span>
						<span v-else-if="item.action === 'resources'" class="resNo">нет ресурсов</span>
						<a v-else-if="item.action === 'allow'" :href="$root.getUrl('buildings/research/cmd/search/tech/'+item.i+'/')" :class="{positive: item.level, negative: item.level === 0}">
							{{ item.level ? 'Улучшить' : 'Исследовать' }}
						</a>
						<div v-else-if="item.action === 'progress'" class="z">
							<span v-if="time > 0">
								{{ Format.time(time, ':', true) }}&nbsp;<a :href="$root.getUrl('buildings/research/cmd/cancel/tech/'+item.i+'/')">Отменить<span v-if="item.build.name.length">на {{ item.build.name }}</span></a>
							</span>
							<a v-else :href="$root.getUrl('buildings/research/?chpl='+item.build.id+'')">завершено. продолжить...</a>
						</div>
						<center v-else>-</center>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "tech-row",
		props: ['item'],
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue')
		},
		data () {
			return {
				time: 0,
				timeout: null
			}
		},
		methods: {
			openWindow () {
				showWindow('', this.$root.getUrl('info/'+this.item['i']+'/'), 600)
			},
			update ()
			{
				if (this.time < 0 || typeof this.item['build'] === 'undefined')
					return;

				this.time = this.item.build.time - this.$root.serverTime();
			},
			stop () {
				clearTimeout(this.timeout);
			},
			start () {
				this.timeout = setTimeout(this.update, 1000);
			}
		},
		watch: {
			time () {
				this.start();
			}
		},
		updated ()
		{
			this.stop();
			this.update();
			this.start();
		},
		mounted ()
		{
			this.stop();
			this.update();
		},
		destroyed () {
			this.stop();
		}
	}
</script>