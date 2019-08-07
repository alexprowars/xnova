<template>
	<div class="block">
		<div v-if="page['id'] < 0" class="title">Имя [Галактика:Система:Планета]</div>
		<div v-else class="title">{{ page['name'] }} [{{ page['galaxy'] }}:{{ page['system'] }}:{{ page['planet'] }}]</div>
		<div class="content border-0">
			<router-form :action="page['id'] < 0 ? '/fleet/shortcut/add/' : '/fleet/shortcut/view/'+page['id']+'/'">
				<div class="table">
					<div class="row">
						<div class="col th">
							<input type="text" name="n" :value="page['name']" size="32" maxlength="32" title="Название">
							<input type="text" name="g" :value="page['galaxy']" size="3" maxlength="2" title="Галактика">
							<input type="text" name="s" :value="page['system']" size="3" maxlength="3" title="Система">
							<input type="text" name="p" :value="page['planet']" size="3" maxlength="2" title="Планета">
							<select name="t" v-model="page['type']">
								<option v-for="(title, type) in $t('PLANET_TYPE')" :value="type">{{ title }}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col th">
							<input type="reset" value="Очистить">
							<input v-if="page['id'] < 0" type="submit" value="Добавить">
							<input v-if="page['id'] >= 0" type="submit" value="Обновить">
							<input v-if="page['id'] >= 0" type="submit" name="delete" value="Удалить">
						</div>
					</div>
					<div class="row">
						<div class="col c">
							<nuxt-link to="/fleet/shortcut/">Назад</nuxt-link>
						</div>
					</div>
				</div>
			</router-form>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'fleet-shortcut-add',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
	}
</script>