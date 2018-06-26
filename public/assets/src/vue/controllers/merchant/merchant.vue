<template>
	<div v-if="page" class="block start">
		<div class="title">Обмен сырья</div>
		<div class="content border-0">
			<form action="/merchant/" method="post" class="container-fluid table">
				<input type="hidden" name="exchange" value="Y">
				<input type="hidden" name="type" v-model="type">
				<div class="row">
					<div class="col th">
						Вы можете вызвать межгалактического торговца для обмена ресурсов.<br>
						<div class="negative">Каждая операция обмена будет стоить вам 1 кредит.</div><br><br>

						<select v-model="type">
							<option value="">Выберите ресурс для обмена</option>
							<option value="metal">Металл</option>
							<option value="crystal">Кристалл</option>
							<option value="deuterium">Дейтерий</option>
						</select>

						<br><br>
						(курс {{ page['mod']['deuterium'] }}/{{ page['mod']['crystal'] }}/{{ page['mod']['metal'] }})
						<br><br>
					</div>
					<div v-if="type !== ''" class="col th">
						<div class="table">
							<div class="row">
								<div class="c col">Обменять {{ $root.getLang('RESOURCES', type)|lower }} на</div>
							</div>
							<div class="row">
								<div class="col-3 th"></div>
								<div class="col-3 th">Курс</div>
								<div class="col-6 th"></div>
							</div>
							<div v-for="res in ['metal', 'crystal', 'deuterium']" class="row">
								<div class="col-3 th middle">{{ $root.getLang('RESOURCES', res) }}</div>
								<div class="col-3 th middle">{{ page['mod'][res] / page['mod'][type] }}</div>
								<div class="col-6 th middle">
									<number v-if="type !== res" :name="res" min="0" v-model="exchange[res]" placeholder="введите кол-во" v-on:input="calculate"></number>
									<span v-else="">{{ exchange[res] }}</span>
								</div>
							</div>
							<div class="row">
								<div class="col th negative">Внимание! Стоимость обмена 1 кр.</div>
							</div>
							<div class="row">
								<div class="col c"><input type="submit" value="Обменять ресурсы"></div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script>
	import router from 'router-mixin'

	export default {
		name: "merchant",
		mixins: [router],
		data () {
			return {
				type: '',
				exchange: {
					metal: 0,
					crystal: 0,
					deuterium: 0
				}
			}
		},
		methods: {
			calculate ()
			{
				let res = 0;

				['metal', 'crystal', 'deuterium'].forEach((item) =>
				{
					if (this.type !== item)
						res += this.exchange[item] * (this.page['mod'][item] / this.page['mod'][this.type]);
				});

				this.exchange[this.type] = res;
			}
		}
	}
</script>