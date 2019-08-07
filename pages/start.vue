<template>
	<div class="page-start">
		<div v-if="page['sex'] === 0 || page['avatar'] === 0" class="block start">
			<div class="title">Основная информация</div>
			<div class="content border-0">
				<router-form action="">
					<input type="hidden" name="save" value="Y">
					<div class="table">
						<div class="row">
							<div class="col th">Введите ваш игровой ник</div>
							<div class="col th"><input name="character" size="20" maxlength="20" type="text" :value="page['name']" title=""></div>
						</div>
						<div class="row">
							<div class="col c">Выберите ваш игровой образ</div>
						</div>
						<div class="row">
							<div class="col th">
								<tabs>
									<tab name="Мужской">
										<div class="row">
											<div v-for="i in 8" class="col-3">
												<input type="radio" name="face" :value="'1_'+i" :id="'f1_'+i" title="">
												<label :for="'f1_'+i" class="avatar">
													<img :src="'/images/faces/1/'+i+'s.png'" alt="">
												</label>
											</div>
										</div>
									</tab>
									<tab name="Женский">
										<div class="row">
											<div v-for="i in 8" class="col-3">
												<input type="radio" name="face" :value="'2_'+i" :id="'f2_'+i" title="">
												<label :for="'f2_'+i" class="avatar">
													<img :src="'/images/faces/2/'+i+'s.png'" alt="">
												</label>
											</div>
										</div>
									</tab>
								</tabs>
							</div>
						</div>
						<div class="row">
							<div class="col th">
								<button type="submit">Продолжить</button>
							</div>
						</div>
					</div>
				</router-form>
			</div>
		</div>
		<div v-else="page['race'] === 0" class="block start race">
			<div class="title">Выбор фракции</div>
			<div class="content">
				<router-form action="" id="tabs">
					<input type="hidden" name="save" value="Y">
					<template v-for="race in page['races']">
						<input type="radio" name="race" :value="race['i']" :id="'f_'+race['i']">
						<label :for="'f_'+race['i']" class="avatar">
							<img :src="'/images/skin/race'+race['i']+'.gif'" alt=""><br>
							<h3>{{ race['name'] }}</h3>
							<span v-html="race['description']"></span>
						</label>
					</template>
					<br>
					<button type="submit">Продолжить</button>
					<br><br>
				</router-form>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'start',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
	}
</script>