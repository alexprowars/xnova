$(document).ready(function()
{
	$('body').on('click', '.spyButton', function(e)
	{
		e.preventDefault();

		var obj = $(this);

		obj.attr('disabled', 'disabled');

		var spyNum = obj.parent().find('input[type=text]').val();

		$.ajax({
			type: "GET",
			url: options.path+"fleet/quick/",
			data: "mode=6&g="+galaxy+"&s="+system+"&p="+obj.data('planet')+"&t="+obj.data('type')+"&count="+spyNum,
			dataType: 'json',
			success: function(data)
			{
				$.toast({
				  	text : data.message,
					position : 'bottom-center',
					icon: statusMessages[data.status]
				});

				obj.removeAttr('disabled');
			}
		});
	})
	.on('click', '.galaxy-select input[type=button]', function(e)
	{
		e.preventDefault();

		$(this).parents('form').find('.auto').attr('name', $(this).attr('name'));
		$(this).parents('form').submit();
	});
});

function ChangePlanet(id)
{
	if (id > 0)
		load(options.path+"galaxy/r/3/?"+document.getElementById('planet_select').options[id].value+"");
}

function PrintSelector(fleet_shortcut)
{
	var result = '';

	result += "<form action=\""+options.path+"galaxy/r/1/\" method=\"post\" class='galaxy-select hidden-xs-down row'>";
	result += "<input type=\"hidden\" class=\"auto\" value=\"dr\" >";
	result += "<div class='col-sm-4'>";

	result += "<table style='margin: 0 auto'><tr>";
	result += "<td class=\"c\" colspan=\"3\">Галактика</td></tr><tr>";
	result += "<th><input name=\"galaxyLeft\" value=\"&lt;-\" type=\"button\"></th>";
	result += "<th><input name=\"galaxy\" value=\""+galaxy+"\" size=\"5\" maxlength=\"3\" tabindex=\"1\" type=\"text\"></th>";
	result += "<th><input name=\"galaxyRight\" value=\"-&gt;\" type=\"button\"></th>";
	result += "</tr></table>";

	result += "</div><div class='col-sm-4'>";

	result += '<table style=\'max-width:200px;width:100%;margin: 0 auto\'><tr><td class=\'c\'><select id=\'planet_select\' onChange=\'ChangePlanet(this.selectedIndex);\' style=\"width:100%\">';
	result += '<option>--- выберите планету ---</option>';

	for (var i = 0; i < fleet_shortcut.length; i++)
    {
		result += '<option';

		if (fleet_shortcut[i][4] == 1 && ((fleet_shortcut[i-1] && (fleet_shortcut[i-1][1] != fleet_shortcut[i][1] || fleet_shortcut[i-1][2] != fleet_shortcut[i][2])) || !fleet_shortcut[i-1]))
			result += ' selected="selected" ';

		result += ' value="galaxy='+fleet_shortcut[i][1]+'&system='+fleet_shortcut[i][2]+'">'+fleet_shortcut[i][0]+'&nbsp;['+fleet_shortcut[i][1]+':'+fleet_shortcut[i][2]+':'+fleet_shortcut[i][3]+']&nbsp;&nbsp;</option>';
	}

	result += "</select></td></tr><tr><th><input value=\"Просмотр\" type=\"submit\" style=\"width:100%\"></th></tr></table></div>";
	result += "<div class='col-sm-4'>";

	result += "<table style='margin: 0 auto'><tr>";
	result += "<td class=\"c\" colspan=\"3\">Солнечная система</td></tr><tr>";
	result += "<th><input name=\"systemLeft\" value=\"&lt;-\" type=\"button\"></th>";
	result += "<th><input name=\"system\" value=\""+system+"\" size=\"5\" maxlength=\"3\" tabindex=\"2\" type=\"text\"></th>";
	result += "<th><input name=\"systemRight\" value=\"-&gt;\" type=\"button\"></th>";
	result += "</tr></table>";

	result += "</div></form>";

	result += "<form action=\""+options.path+"galaxy/r/1/\" method=\"post\" class='galaxy-select hidden-sm-up row'>";
	result += "<input type=\"hidden\" class=\"auto\" value=\"dr\" >";
	result += "<div class='col-xs-12'>";
	result += '<table style=\'margin: 0 auto\'><tr><td class=\'c\'><select id=\'planet_select\' onChange=\'ChangePlanet(this.selectedIndex);\' style=\"width:100%\">';
	result += '<option>--- выберите планету ---</option>';

	for (i = 0; i < fleet_shortcut.length; i++)
    {
		result += '<option';

		if (fleet_shortcut[i][4] == 1 && ((fleet_shortcut[i-1] && (fleet_shortcut[i-1][1] != fleet_shortcut[i][1] || fleet_shortcut[i-1][2] != fleet_shortcut[i][2])) || !fleet_shortcut[i-1]))
			result += ' selected="selected" ';

		result += ' value="galaxy='+fleet_shortcut[i][1]+'&system='+fleet_shortcut[i][2]+'">'+fleet_shortcut[i][0]+'&nbsp;['+fleet_shortcut[i][1]+':'+fleet_shortcut[i][2]+':'+fleet_shortcut[i][3]+']&nbsp;&nbsp;</option>';
	}

	result += "</select></td></tr><tr><th><input value=\"Просмотр\" type=\"submit\" style=\"width:200px\"></th></tr></table></div>";
	result += "<div class='separator'></div><div class='col-xs-6'>";
	result += "<table style='margin: 0 auto'><tr>";
	result += "<td class=\"c\" colspan=\"3\">Галактика</td></tr><tr>";
	result += "<th><input name=\"galaxyLeft\" value=\"&lt;-\" type=\"button\"></th>";
	result += "<th><input name=\"galaxy\" value=\""+galaxy+"\" size=\"5\" maxlength=\"3\" tabindex=\"1\" type=\"text\"></th>";
	result += "<th><input name=\"galaxyRight\" value=\"-&gt;\" type=\"button\"></th>";
	result += "</tr></table>";
	result += "</div>";
	result += "<div class='col-xs-6'>";
	result += "<table style='margin: 0 auto'><tr>";
	result += "<td class=\"c\" colspan=\"3\">Солнечная система</td></tr><tr>";
	result += "<th><input name=\"systemLeft\" value=\"&lt;-\" type=\"button\"></th>";
	result += "<th><input name=\"system\" value=\""+system+"\" size=\"5\" maxlength=\"3\" tabindex=\"2\" type=\"text\"></th>";
	result += "<th><input name=\"systemRight\" value=\"-&gt;\" type=\"button\"></th>";
	result += "</tr></table>";
	result += "</div></form><div class=\"separator\"></div>";
	
	return result;
}