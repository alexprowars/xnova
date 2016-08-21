<script type="text/javascript" src="{{ url.getBaseUri() }}assets/js/jqplot/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="{{ url.getBaseUri() }}assets/js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script type="text/javascript" src="{{ url.getBaseUri() }}assets/js/jqplot/plugins/jqplot.highlighter.min.js"></script>
<link rel="stylesheet" type="text/css" href="{{ url.getBaseUri() }}assets/js/jqplot/jquery.jqplot.min.css">
<table class="table">
	<tr>
		<td class="c"><b>Статистика по месту игрока "{{ parse['name'] }}"</b></td>
	</tr>
	<tr>
		<th style="padding: 10px;">
			<div id="chart1"></div>
		</th>
	</tr>
</table>
<script type="text/javascript">
	{% set max = 0 %}
	var temp1 = [
		{% for data in parse['data']   %}
				{% if max < data['total_rank'] %}
					{% set max = data['total_rank'] %}
				{% endif %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['total_rank'] }}],
		{% endfor %}
	];

	var temp2 = [
		{% for data in parse['data'] %}
			{% if max < data['tech_rank'] %}
				{% set max = data['tech_rank'] %}
			{% endif %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['tech_rank'] }}],
		{% endfor %}
	];

	var temp3 = [
		{% for data in parse['data'] %}
			{% if max < data['build_rank'] %}
				{% set max = data['build_rank'] %}
			{% endif %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['build_rank'] }}],
		{% endfor %}
	];

	var temp4 = [
		{% for data in parse['data'] %}
			{% if max < data['fleet_rank'] %}
				{% set max = data['fleet_rank'] %}
			{% endif %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['fleet_rank'] }}],
		{% endfor %}
	];

	var temp5 = [
		{% for data in parse['data'] %}
			{% if max < data['defs_rank'] %}
				{% set max = data['defs_rank'] %}
			{% endif %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['defs_rank'] }}],
		{% endfor %}
	];

	$(document).ready(function()
	{
		$.jqplot('chart1', [temp1, temp3, temp2, temp4, temp5],
		{
			color: '#ffffff',
			legend: {
				show: true,
				location: 'ne',
				xoffset: 12,
				yoffset: 12,
				labels: ['В', 'П', 'T', 'Ф', 'О']
			},
			seriesDefaults: {
				pointLabels: {
					show:true,
					location: 'se'
				},
				shadow: false
			},
			series: [
				{showMarker: true}
			],
			axes: {
				xaxis: {
					label: '',
					renderer: $.jqplot.CategoryAxisRenderer,
					tickOptions: {formatString: '%Y', textColor:'#ffffff'}

				},
				yaxis: {
					label: '',
					tickOptions: {textColor:'#ffffff', formatString:'%i'},
					max: -1,
					min: {{ (max * 1.1)|round + 1 }}
				}
			},
			highlighter: {
			  	show: true,
				tooltipAxes: 'y'
			},
			cursor: {
			  	show: false
			}
		});
	})
</script>
<div class="separator"></div>
<div id="tabs">
	<div class="head">
		<ul>
			<li><a href="#tabs-0">Всего очков</a></li>
			<li><a href="#tabs-1">Очки построек</a></li>
			<li><a href="#tabs-2">Очки технологий</a></li>
			<li><a href="#tabs-3">Очки флота</a></li>
			<li><a href="#tabs-4">Очки обороны</a></li>
		</ul>
	</div>
	<div id="tabs-0" data-index="0">
		<div id="canvas0"></div>
	</div>
	<div id="tabs-1" data-index="1">
		<div id="canvas1"></div>
	</div>
	<div id="tabs-2" data-index="2">
		<div id="canvas2"></div>
	</div>
	<div id="tabs-3" data-index="3">
		<div id="canvas3"></div>
	</div>
	<div id="tabs-4" data-index="4">
		<div id="canvas4"></div>
	</div>
</div>

<style>
	#tabs-1, #tabs-0, #tabs-2, #tabs-3, #tabs-4 {
		padding: 10px;;
	}
</style>

<script type="text/javascript">

	$( "#tabs" ).tabs(
	{
		activate: function( event, ui )
		{
			pointPlot('canvas'+$(ui.newPanel).data('index'), points[$(ui.newPanel).data('index')]);
		}
	});

	var points = [];

	points[0] = [
		{% for data in parse['data'] %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['total_points'] }}],
		{% endfor %}
	];
	points[1] = [
		{% for data in parse['data'] %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['build_points'] }}],
		{% endfor %}
	];
	points[2] = [
		{% for data in parse['data'] %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['tech_points'] }}],
		{% endfor %}
	];
	points[3] = [
		{% for data in parse['data'] %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['fleet_points'] }}],
		{% endfor %}
	];
	points[4] = [
		{% for data in parse['data'] %}
			['{{ date("d.m H:i", data['time']) }}', {{ data['defs_points'] }}],
		{% endfor %}
	];

	var plot;

	function pointPlot (element, data)
	{
		if (plot !== undefined)
			plot.destroy();

		$('#'+element).empty();
		plot = $.jqplot(element, [data],
		{
			seriesDefaults: {
				pointLabels: {
					show:true,
					location: 'se'
				},
				shadow: false
			},
			series: [
				{showMarker: true}
			],
			axes: {
				xaxis: {
					label: '',
					renderer: $.jqplot.CategoryAxisRenderer,
					tickOptions: {formatString: '%Y', textColor:'#ffffff'}

				},
				yaxis: {
					label: '',
					tickOptions: {textColor:'#ffffff', formatString:'%i'}
				}
			},
			highlighter: {
			  	show: true,
				tooltipAxes: 'y'
			},
			cursor: {
			  	show: false
			}
		});
	}

	$(document).ready(function()
	{
		$( "#tabs" ).tabs( "option", "active", 1 ).tabs( "option", "active", 0 );
	})
</script>