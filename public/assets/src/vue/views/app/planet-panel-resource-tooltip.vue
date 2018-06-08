<template>
	<div class="resource-panel-item-tooltip">
		<h1>{{ $root.getLang('RESOURCES', type) }}</h1>
		<div class="line"></div>

		<table>
			<tr>
				<td width="30%">Мощность:</td>
				<td align="right">{{ resource['power'] }}%</td>
			</tr>
			<tr>
				<td>Производство:</td>
				<td align="right">{{ resource['production']|number }}</td>
			</tr>
			<tr>
				<td>Вместимость:</td>
				<td align="right">{{ resource['max']|number }}</td>
			</tr>
		</table>
	</div>
</template>

<script>
	export default {
		name: "application-planet-panel-resource-tooltip",
		props: ['resource', 'type']
	}
</script>