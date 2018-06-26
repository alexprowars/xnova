<template>
	<div>
		<div class="block">
			<div class="title">{{ data['name'] }}</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col">
							<table class="margin5">
								<tr>
									<td valign="top"><img :src="$root.getUrl('assets/images/gebaeude/'+data['i']+'.gif')" class="info" align="top" height="120" width="120"></td>
									<td valign="top" class="text-left">{{ data['description'] }}</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="block">
			<div class="title">Производство</div>
			<div class="content border-0">
				<div v-if="data['i'] === 42" class="table">
					<div class="row">
						<div class="col c">Уровень</div>
						<div class="col c">Дальность</div>
					</div>
					<div v-for="row in data['table_data']" class="row">
						<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
						<div class="col th">{{ row['range'] }}</div>
					</div>
				</div>
				<div v-else-if="data['i'] === 22 || data['i'] === 23 || data['i'] === 24" class="table">
					<div class="row">
						<div class="col c">Уровень</div>
						<div class="col c">Вместимость</div>
					</div>
					<div v-for="row in data['table_data']" class="row">
						<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
						<div class="col th">{{ row['range'] }}k</div>
					</div>
				</div>
				<div v-else-if="data['i'] !== 4" class="table">
					<div class="row">
						<div class="col c">Уровень</div>
						<div class="col c">Выработка</div>
						<div class="col c">Разница</div>
						<div class="col c">Потребление энергии</div>
						<div class="col c">Разница</div>
					</div>
					<div v-for="row in data['table_data']" class="row">
						<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
						<div class="col th">{{ row['prod']|number }}</div>
						<div class="col th"><colored :value="row['prod_diff']"></colored></div>
						<div class="col th"><colored :value="row['need']"></colored></div>
						<div class="col th"><colored :value="row['need_diff']"></colored></div>
					</div>
				</div>
				<div v-else class="table">
					<div class="row">
						<div class="col c">Уровень</div>
						<div class="col c">Выработка</div>
						<div class="col c">Разница</div>
					</div>
					<div v-for="row in data['table_data']" class="row">
						<div class="col th"><span :class="{neutral: row['current']}">{{ row['level'] }}</span></div>
						<div class="col th">{{ row['prod']|number }}</div>
						<div class="col th"><colored :value="row['prod_diff']"></colored></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "info-resources",
		props: {
			data: Object
		}
	}
</script>