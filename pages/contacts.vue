<template>
	<div class="block">
		<div class="title">Начальство</div>
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="col th">
						<font color="orange">Здесь вы найдёте адреса всех администраторов и операторов игры для обратной связи</font>
					</div>
				</div>
				<div class="row">
					<div class="col c">Имя</div>
					<div class="col c">Должность</div>
					<div class="col c">E-mail</div>
				</div>
				<template v-for="item in page.items">
					<div class="row">
						<div class="col th">{{ item['name'] }}</div>
						<div class="col th">{{ item['auth'] }}</div>
						<div class="col th">
							<a :href="'mailto:'+item['mail']">{{ item['mail'] }}</a>
						</div>
					</div>
					<div class="row" v-if="item['info'].length > 0">
						<div class="col c">
							<text-viewer :text="item['info']"></text-viewer>
						</div>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'contacts',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
	}
</script>