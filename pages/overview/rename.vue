<template>
	<div class="page-overview-rename">
		<div class="block">
			<div class="title">Переименовать или покинуть планету</div>
			<div class="content border-0">
				<router-form :action="'/overview/rename/pl/'+page['planet_id']+'/'">
					<div class="table middle">
						<div class="row">
							<div class="col th d-none d-sm-flex">{{ page['galaxy_galaxy'] }}:{{ page['galaxy_system'] }}:{{ page['galaxy_planet'] }}</div>
							<div class="col th">{{ page['planet_name'] }}</div>
							<div class="col th">
								<nuxt-link to="/overview/delete/">
									<input type="button" value="Покинуть колонию" alt="Покинуть колонию">
								</nuxt-link>
							</div>
						</div>
						<div class="row">
							<div class="col th d-none d-sm-flex">Сменить название</div>
							<div class="col th"><input type="text" :placeholder="page['planet_name']" name="newname" maxlength="20"></div>
							<div class="col th"><input type="submit" name="action" value="Сменить название"></div>
						</div>
					</div>
				</router-form>
			</div>
		</div>
		<div v-if="page['type'] !== ''" class="block page-overview-planet-image">
			<div class="title">Сменить фон планеты</div>
			<div class="content border-0">
				<router-form :action="'/overview/rename/pl/'+page['planet_id']+'/'">
					<div class="table">
						<div class="row">
							<div v-for="i in page['images'][page['type']]" class="col-6 col-sm-3 col-md-2">
								<input type="radio" v-model="image" name="image" :value="i" :id="'image_'+i">
								<label :for="'image_'+i">
									<img :src="'/images/planeten/'+page['type']+'planet'+(i < 10 ? '0' : '')+i+'.jpg'" align="absmiddle" width="100%" alt="">
								</label>
							</div>
						</div>
						<div v-if="image > 0" class="row">
							<div class="col th">
								<input type="submit" name="action" value="Сменить картинку (1 кредит)">
							</div>
						</div>
					</div>
				</router-form>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "overview-rename",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.path)
		},
		middleware: ['auth'],
		data () {
			return {
				image: 0
			}
		},
	}
</script>