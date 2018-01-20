<template>
	<div v-bind:class="['planet', 'type_'+item.t, ($root.user.planet === item.id ? 'current' : '')]">
		<a v-on:click="changeItem" v-bind:title="item.name">
			<img v-bind:src="$root.getUrl('assets/images/planeten/small/s_'+item.image+'.jpg')" height="40" width="40" v-bind:alt="item.name">
		</a>
		<span class="d-none d-md-block d-lg-none" v-html="$root.getPlanetUrl(item.g, item.s, item.p)">{{ $root.getPlanetUrl(item.g, item.s, item.p) }}</span>
		<div class="d-sm-none d-md-block">
			{{ item.name }}<br>
			<span v-html="$root.getPlanetUrl(item.g, item.s, item.p)"></span>
		</div>
		<div class="clear"></div>
	</div>
</template>

<script>
	export default {
		name: "application-planets-list-row",
		props: ['item'],
		methods:
		{
			changeItem: function ()
			{
				var path = window.location.pathname.replace(this.$root.path, '').split('/');
				var url = this.$root.getUrl(path[0]+(path[1] !== undefined && path[1] !== '' && path[0] !== 'galaxy' && path[0] !== 'fleet' ? '/'+path[1] : '')+'/?chpl='+this.item.id);
	
				load(url);
			}
		}
	}
</script>