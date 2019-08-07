<template>
	<div class="page-imperium table-responsive">
		<table class="table">
			<tbody>
			<tr valign="left">
				<td class="c" :colspan="rows">Обзор империи</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<th v-for="planet in page['planets']" width="75">
					<nuxt-link :to="'/overview/?chpl='+planet['id']">
						<img :src="'/images/planeten/small/s_'+planet['image']+'.jpg'" height="75" width="75" alt="">
					</nuxt-link>
				</th>
				<th width="100">Сумма</th>
			</tr>
			<tr>
				<th>Название</th>
				<th v-for="planet in page['planets']">
					<nuxt-link :to="'/overview/?chpl='+planet['id']">{{ planet['name'] }}</nuxt-link>
				</th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<th>Координаты</th>
				<th v-for="planet in page['planets']">
					[<nuxt-link :to="'/galaxy/'+planet['position']['galaxy']+'/'+planet['position']['system']+'/'">{{ planet['position']['galaxy'] }}:{{ planet['position']['system'] }}:{{ planet['position']['planet'] }}</nuxt-link>]
				</th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<th>Поля</th>
				<th v-for="planet in page['planets']">
					{{ planet['fields'] }} / {{ planet['fields_max'] }}
				</th>
				<th>{{ total.fields }} / {{ total.fields_max }}</th>
			</tr>
			<tr>
				<th>Кредиты</th>
				<th :colspan="rows - 2">&nbsp;</th>
				<th>
					<span class="neutral">{{ page['credits']|number }}</span>
				</th>
			</tr>
			<tr>
				<td class="c" :colspan="rows" align="left">Ресурсы на планете</td>
			</tr>
			<tr v-for="(i, res) in $t('RESOURCES')" v-if="res !== 'energy'">
				<th>{{ $t('RESOURCES.'+res) }}</th>
				<th v-for="planet in page['planets']">
					<span :class="[planet['resources'][res]['current'] < planet['resources'][res]['storage'] ? 'positive' : 'negative']">{{ planet['resources'][res]['current']|number }}</span>
				</th>
				<th>{{ total['resources'][res]|number }}</th>
			</tr>
			<tr>
				<th>{{ $t('RESOURCES.energy') }}</th>
				<th v-for="planet in page['planets']">
					<span :class="[planet['resources']['energy']['current'] >= 0 ? 'positive' : 'negative']">{{ planet['resources']['energy']['current']|number }}</span>
				</th>
				<th>{{ total['resources']['energy']|number }}</th>
			</tr>
			<tr>
				<th>Заряд</th>
				<th v-for="planet in page['planets']">
					<span :class="[planet['resources']['energy']['storage'] === 100 ? 'positive' : 'negative']">{{ planet['resources']['energy']['storage']|number }}</span>%
				</th>
				<th>&nbsp;</th>
			</tr>

			<tr>
				<td class="c" :colspan="rows" align="left">Производство в час</td>
			</tr>
			<tr v-for="(i, res) in $t('RESOURCES')" v-if="res !== 'energy'">
				<th>{{ $t('RESOURCES.'+res) }}</th>
				<th v-for="planet in page['planets']">{{ planet['resources'][res]['production']|number }}</th>
				<th>{{ total['production'][res]|number }}</th>
			</tr>

			<tr>
				<td class="c" :colspan="rows" align="left">Уровень производства</td>
			</tr>
			<tr v-for="(item, i) in [1, 2, 3, 4, 12, 212]">
				<th>{{ $t('TECH.'+item) }}</th>
				<th v-for="planet in page['planets']">
					<span :class="[planet['factor'][item] >= 100 ? 'positive' : 'negative']">{{ planet['factor'][item]|number }}</span>%
				</th>
				<th v-if="i === 0" rowspan="6">&nbsp;</th>
			</tr>
			<tr>
				<td class="c" :colspan="rows" align="left">Постройки</td>
			</tr>
			<tr v-for="(name, id) in $t('TECH')" v-if="id < 100">
				<th>{{ name }}</th>
				<th v-for="planet in page['planets']">
					<span v-if="planet['elements'][id]['current'] > 0 || planet['elements'][id]['build'] > 0">
						{{ planet['elements'][id]['current']|number }}
					</span>
					<span v-else>-</span>
					<span v-if="planet['elements'][id]['build'] > 0" class="positive">-> {{ planet['elements'][id]['build']|number }}</span>
				</th>
				<th>
					<span>
						{{ total['elements'][id]['current']|number }}
					</span>
					<span v-if="total['elements'][id]['build'] > 0" class="positive">
						-> {{ total['elements'][id]['build']|number }}
					</span>
				</th>
			</tr>
			<tr>
				<td class="c" :colspan="rows" align="left">Флот</td>
			</tr>
			<tr v-for="(name, id) in $t('TECH')" v-if="id > 200 && id < 300">
				<th>{{ name }}</th>
				<th v-for="planet in page['planets']">
					<span v-if="planet['elements'][id]['current'] > 0 || planet['elements'][id]['build'] > 0 || planet['elements'][id]['fly'] > 0">
						{{ planet['elements'][id]['current']|number }}
					</span>
					<span v-else>-</span>
					<span v-if="planet['elements'][id]['build'] > 0" class="positive">
						+ {{ planet['elements'][id]['build']|number }}
					</span>
					<span v-if="planet['elements'][id]['fly'] > 0" class="neutral">
						+ {{ planet['elements'][id]['fly']|number }}
					</span>
				</th>
				<th>
					<span>
						{{ total['elements'][id]['current']|number }}
					</span>
					<span v-if="total['elements'][id]['build'] > 0" class="positive">
						+ {{ total['elements'][id]['build']|number }}
					</span>
					<span v-if="total['elements'][id]['fly'] > 0" class="neutral">
						+ {{ total['elements'][id]['fly']|number }}
					</span>
				</th>
			</tr>
			<tr>
				<td class="c" :colspan="rows" align="left">Оборона</td>
			</tr>
			<tr v-for="(name, id) in $t('TECH')" v-if="id > 400 && id < 600">
				<th>{{ name }}</th>
				<th v-for="planet in page['planets']">
					<span v-if="planet['elements'][id]['current'] > 0 || planet['elements'][id]['build'] > 0">
						{{ planet['elements'][id]['current']|number }}
					</span>
					<span v-else>-</span>
					<span v-if="planet['elements'][id]['build'] > 0" class="positive">
						+ {{ planet['elements'][id]['build']|number }}
					</span>
				</th>
				<th>
					<span>
						{{ total['elements'][id]['current']|number }}
					</span>
					<span v-if="total['elements'][id]['build'] > 0" class="positive">
						+ {{ total['elements'][id]['build']|number }}
					</span>
				</th>
			</tr>
			<tr>
				<td class="c" :colspan="rows" align="left">Технологии</td>
			</tr>
			<tr v-for="(item, id) in page['tech']">
				<th :colspan="rows - 1">{{ $t('TECH.'+id) }}</th>
				<th>
					<span class="neutral">{{ item['current'] }}</span>
					<span v-if="item['build'] > 0" class="positive">
						-> {{ item['build'] }}
					</span>
				</th>
			</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
	export default {
		name: 'imperium',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		computed: {
			rows ()
			{
				if (!this.page)
					return 2;

				return this.page['planets'].length + 2
			},
			total () {
				let result = {
					fields: 0,
					fields_max: 0,
					resources: {},
					production: {},
					elements: {},
				};

				let resources = Object.keys(this.$t('RESOURCES'));
				let elements = Object.keys(this.$t('TECH'));

				for (let res of resources)
				{
					result.resources[res] = 0
					result.production[res] = 0
				}

				for (let id of elements)
				{
					result.elements[id] = {
						current: 0,
						build: 0,
						fly: 0
					};
				}

				if (!this.page)
					return result

				this.page['planets'].forEach((planet) =>
				{
					result.fields += planet['fields']
					result.fields_max += planet['fields_max']

					for (let res of resources)
					{
						result.resources[res] += planet['resources'][res]['current']
						result.production[res] += planet['resources'][res]['production']
					}

					for (let id of elements)
					{
						if (id < 100)
						{
							if (result.elements[id].current < planet['elements'][id]['current'])
								result.elements[id].current = planet['elements'][id]['current']

							if (result.elements[id].build < planet['elements'][id]['build'])
								result.elements[id].build = planet['elements'][id]['build']
						}
						else if (id > 200 && id < 300)
						{
							result.elements[id].current += planet['elements'][id]['current']
							result.elements[id].build += planet['elements'][id]['build']
							result.elements[id].fly += planet['elements'][id]['fly']
						}
						else if (id > 400 && id < 600)
						{
							result.elements[id].current += planet['elements'][id]['current']
							result.elements[id].build += planet['elements'][id]['build']
						}
					}
				})

				for (let id of elements)
				{
					if (id < 100)
					{
						if (result.elements[id].current > result.elements[id].build - 1)
							result.elements[id].build = 0
					}
				}

				return result;
			}
		},
	}
</script>