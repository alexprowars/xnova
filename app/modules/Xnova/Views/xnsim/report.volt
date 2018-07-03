{{ getDoctype() }}
<html lang="ru">
	<head>
		<title>Симуляция боя</title>
		{{ assets.outputCss() }}
		{{ assets.outputJs() }}
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<center>
			<table width="99%">
				<tr>
					<td>{{ report['html']|stripslashes }}</td>
				</tr>
			</table>
			Ссылка на результат симуляции<br><br>
			<input type="text" value="https://{{ _SERVER['SERVER_NAME'] }}/xnsim/report/?sid={{ sid }}" style="width:500px;padding:5px;text-align: center;" title="">
			<br><br>
			{% if statistics is defined %}
				Результаты потерь после 50 симуляций:
				<table>
					<tr>
						<th>№</th>
						<th>Потери атакующего</th>
						<th>Потери защитника</th>
					</tr>
					{% for i, s in statistics %}
						<tr>
							<th>{{ i }}</th>
							<th>{{ pretty_number(s['att']) }}</th>
							<th>{{ pretty_number(s['def']) }}</th>
						</tr>
					{% endfor %}
				</table>
			{% endif %}
			<br><br>
			Made by AlexPro for <a href="http://xnova.su/" target="_blank">XNova</a>
			<br><br>
		</center>
	</body>
</html>