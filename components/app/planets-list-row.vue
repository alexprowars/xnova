<template>
	<div class="planet" :class="['type_'+item.t, ($store.state.user.planet === item.id ? 'current' : '')]">
		<a @click.prevent="changeItem" :title="item.name">
			<img :src="'/images/planeten/small/s_'+item.image+'.jpg'" height="40" width="40" :alt="item.name">
		</a>
		<span class="d-none d-sm-block d-md-none">
			<planet-link :galaxy="item.g" :system="item.s" :planet="item.p"></planet-link>
		</span>
		<div class="d-sm-none d-md-block">
			{{ item.name }}<br>
			<span>
				<planet-link :galaxy="item.g" :system="item.s" :planet="item.p"></planet-link>
			</span>
		</div>
		<div class="clear"></div>
	</div>
</template>

<script>
	export default {
		name: "planets-list-row",
		props: {
			item: {
				type: Object
			}
		},
		methods:
		{
			changeItem ()
			{
				let path = window.location.pathname.replace(this.$store.state.path, '').split('/');

				if (path[0] === '')
					path.splice(0, 1)

				let url = '/'+path[0]+(path[1] !== undefined && path[1] !== '' && path[0] !== 'galaxy' && path[0] !== 'fleet' ? '/'+path[1] : '')+'/?chpl='+this.item.id;

				this.$router.replace(url);
			}
		}
	}
</script>