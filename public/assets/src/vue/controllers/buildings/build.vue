<template>
	<div class="block">
		<div class="title">
			Занято полей
			<span class="positive">{{ page.fields_current }}</span> из <span class="negative">{{ page.fields_max }}</span>
			<div class="float-right col-12 col-sm-6 p-a-0">
				Осталось
				<span class="positive">{{ fields_empty }}</span>
				свободн{{ morph(page.fields_empty, ['ое', 'ых', 'ых']) }}
				пол{{ morph(page.fields_empty, ['е', 'я', 'ей']) }}
			</div>
			<div class="clearfix"></div>
		</div>

		<div v-if="page.queue.length">
			<table v-for="(item, index) in page.queue" class="table" id="building">
				<tr>
					<td class="c" width="50%">
						{{ index + 1 }}: {{ item.name }} {{ item.level }}{{ item.mode === 1 ? '. Снос здания' : '' }}
					</td>
					<td class="k" v-if="index === 0">
						<div v-if="item.time > 0" class="z">
							{{ Format.time(item.time, ':') }}
							<br>
							<a v-if="cheat <= 0" v-on:click="load($root.getUrl('buildings/index/listid/'+(index + 1)+'/cmd/cancel/planet/'+pl+'/'))">Отменить</a>
						</div>
						<div v-else class="z">
							Завершено
							<br>
							<a v-on:click="load($root.getUrl('buildings/index/planet/'+$store.state.user.planet+'/'))">Продолжить</a>
						</div>
						<div class="positive">{{ date("d.m H:i:s", item.end) }}</div>
					</td>
					<td class="k" v-else>
						<a v-bind:href="$root.getUrl('buildings/index/listid/'+(index + 1)+'/cmd/remove/planet/'+$store.state.user.planet+'/')">Удалить</a>
					</td>
				</tr>
			</table>
		</div>

		<div class="content">
			<div class="row" id="building">
				<game-page-buildings-build-item v-for="item in page.items" v-bind:item="item"></game-page-buildings-build-item>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build",
		data: function () {
			return {
				cheat: 3
			}
		},
		computed: {
			page () {
				return this.$store.state.page;
			},
			fields_empty: function() {
				return this.page.fields_max - this.page.fields_current;
			}
		},
		components: {
			'game-page-buildings-build-item': require('./build-row.vue')
		},
		mounted: function ()
		{
			this.init();
		},
		methods:
		{
			init: function ()
			{
				clearTimeout(timeouts['build_queue']);

				this.cheat = 3;

				if (this.page.queue.length > 0)
					setTimeout(this.timer, 1000);
			},
			timer: function()
			{
				if (this.cheat > 0)
					this.cheat -= 1;

				this.page.queue[0].time -= 1;

				if (this.page.queue[0].time <= 0)
				{
					timeouts['build_queue'] = setTimeout(function()
					{
						load(this.$root.getUrl('buildings/index/planet/'+this.$store.state.user.planet+'/'));
					}.bind(this), 5000);
				}
				else
					timeouts['build_queue'] = setTimeout(this.timer, 1000);
			}
		},
		watch: {
			page: function () {
				this.init();
			}
		},
	}
</script>