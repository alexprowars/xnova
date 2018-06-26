<template>
	<div class="planet" :class="['type_'+item.t, ($store.state.user.planet === item.id ? 'current' : '')]">
		<a @click.prevent="changeItem" :title="item.name">
			<img :src="$root.getUrl('assets/images/planeten/small/s_'+item.image+'.jpg')" height="40" width="40" :alt="item.name">
		</a>
		<span class="d-none d-sm-block d-md-none" v-html="$root.getPlanetUrl(item.g, item.s, item.p)">{{ $root.getPlanetUrl(item.g, item.s, item.p) }}</span>
		<div class="d-sm-none d-md-block">
			{{ item.name }}<br>
			<span v-html="$root.getPlanetUrl(item.g, item.s, item.p)"></span>
		</div>
		<div class="clear"></div>
	</div>
</template>

<script>
	export default {
		name: "planets-list-row",
		props: ['item'],
		methods:
		{
			changeItem: function ()
			{
				let path = window.location.pathname.replace(this.$store.state.path, '').split('/');
				let url = this.$root.getUrl(path[0]+(path[1] !== undefined && path[1] !== '' && path[0] !== 'galaxy' && path[0] !== 'fleet' ? '/'+path[1] : '')+'/?chpl='+this.item.id);
	
				this.$root.load(url);
			}
		}
	}
</script>