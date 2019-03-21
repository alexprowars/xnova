<template>
	<table width="240">
		<tbody>
			<tr>
				<td width="220">Сильный игрок</td>
				<td><span class="strong">S</span></td>
			</tr>
			<tr>
				<td>Слабый игрок</td>
				<td><span class="noob">N</span></td>
			</tr>
			<tr>
				<td>Режим отпуска</td>
				<td><span class="vacation">U</span></td>
			</tr>
			<tr>
				<td>Заблокирован</td>
				<td><span class="banned">G</span></td>
			</tr>
			<tr>
				<td>Неактивен 7 дней</td>
				<td><span class="inactive">i</span></td>
			</tr>
			<tr>
				<td>Неактивен 28 дней</td>
				<td><span class="longinactive">iI</span></td>
			</tr>
			<tr>
				<td><span class="negative">Администратор</span></td>
				<td><span class="negative">A</span></td>
			</tr>
			<tr>
				<td><span class="positive">Оператор</span></td>
				<td><span class="positive">GO</span></td>
			</tr>
			<tr>
				<td><span class="neutral">Супер оператор</span></td>
				<td><span class="neutral">SGO</span></td>
			</tr>
		</tbody>
	</table>
</template>

<script>
	export default {
		name: "galaxy-legend",
	}
</script>