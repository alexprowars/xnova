<template>
	<table width=150>
		<tr><td width=30%>КПД:</td><td align=right>{{ resource.power }}%</td></tr>
		<tr><td>В час:</td><td align=right>{{ Format.number(resource.production) }}</td></tr>
		<tr><td>День:</td><td align=right>{{ Format.number(resource.production * 24) }}</td></tr>
	</table>'
</template>

<script>
	export default {
		name: "application-planet-panel-resource-tooltip",
		props: ['resource']
	}
</script>