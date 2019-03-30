<template>
	<div class="resource-panel-item-tooltip">
		<h1>{{ $t('RESOURCES.'+type+'') }}</h1>
		<div class="line"></div>

		<table>
			<tbody>
				<tr>
					<td width="30%">Мощность:</td>
					<td align="right">{{ resource['power'] }}%</td>
				</tr>
				<tr>
					<td>Производство:</td>
					<td align="right">{{ resource['production'] | number }}</td>
				</tr>
				<tr>
					<td>Вместимость:</td>
					<td align="right">{{ resource['max'] | number }}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
	export default {
		name: "planet-panel-resource-tooltip",
		props: {
			resource: {
				type: Object
			},
			type: {
				type: String,
				default: ''
			}
		},
	}
</script>