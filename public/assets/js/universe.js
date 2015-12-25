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
			url: "?set=fleet&page=quick",
			data: "ajax=1&mode=6&g="+galaxy+"&s="+system+"&p="+obj.data('planet')+"&t="+obj.data('type')+"&count="+spyNum+"",
			success: function(msg)
			{
				$('#galaxyMessage').html(msg).show();

				setTimeout(function()
				{
					$('#galaxyMessage').hide();
				}, 3000);

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

var race_str = new Array('', 'Конфедерация', 'Бионики', 'Сайлоны', 'Древние');

function PrintRow ()
{
	var result = '';
	
	result += "<div class=\"table-responsive\">";
	result += "<table class=\"table galaxy\"><tr>";
	result += "<td class=c colspan=9>Солнечная система "+galaxy+":"+system+"</td>";
	result += "</tr><tr>";
	result += "<td class=c>№</td>";
	result += "<td class=c>&nbsp;</td>";
	result += "<td class=c>Планета</td>";
	result += "<td class=c>Луна</td>";
	result += "<td class=c>ПО</td>";
	result += "<td class=c>Игрок</td>";
	result += "<td class=c>&nbsp;</td>";
	result += "<td class=c>Альянс</td>";
	result += "<td class=c>Действия</td>";
	result += "</tr>";
	
	var planetcount = 0;
	
	for (var planet = 1; planet <= 15; planet++)
    {
		result += '<tr class="planetRow">';

		result += '<th width=30>'+planet+'</th>';

		result += '<th class="img">';

		if (row[planet] && row[planet]["destruyed"] == 0)
        {
			planetcount++;

			result += "<a href=\"javascript:;\" class=\"tooltip_sticky\" data-tooltip-content='";
			result += "<table width=240>";
			result += "<tr><td class=c colspan=2>Планета "+row[planet]["name"]+" ["+galaxy+":"+system+":"+planet+"]</td></tr>";
			result += "<tr>";
			result += "<th width=80><img src="+dpath+"planeten/small/s_"+row[planet]["image"]+".jpg height=75 width=75></th>";
			result += "<th align=left>";

			if (user['phalanx'] == 1)
				result += "<a href=\"javascript:void()\" onclick=fenster(\"?set=phalanx&amp;galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"\")>Фаланга</a><br />";

			if (row[planet]['user_id'] != user['id'] && !(row[planet]['ally_planet'] > 0 && row[planet]['ally_planet'] == user['ally_id']))
            {
				result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&amp;planettype="+row[planet]['planet_type']+"&amp;target_mission=1>Атаковать</a><br />";
				result += "<a href=?set=fleet&galaxy="+galaxy+"&system="+system+"&planet="+planet+"&planettype="+row[planet]['planet_type']+"&target_mission=5>Удерживать</a><br />";
			}
            else
            {
				result += "<a href=?set=fleet&galaxy="+galaxy+"&system="+system+"&planet="+planet+"&planettype="+row[planet]['planet_type']+"&target_mission=4>Оставить</a><br />";
			}

			result += "<a href=?set=fleet&galaxy="+galaxy+"&system="+system+"&planet="+planet+"&planettype="+row[planet]['planet_type']+"&target_mission=3>Транспорт</a>";


			result += "</th></tr>";
			result += "</table>'><img src="+dpath+"planeten/small/s_"+row[planet]["image"]+".jpg height=30 width=30></a>";
		}
        else
            result += "&nbsp;";

		result += "</th>";

		result += "<th width=130><div style=\"overflow:hidden;width:130px\">";

		if (row[planet] && row[planet]["destruyed"] == 0)
        {
			var TextColor = '';
			var EndColor  = '';

			if (row[planet]['last_active'] < 60)
            {
				if (row[planet]['last_active']  <= 10)
					result += "<span class='star'>(*)</span> ";
				else
					result += "<span class='star'>("+Math.floor(row[planet]['last_active'])+")</span> ";
			}

			if (row[planet]['ally_id'] == user['ally_id'] && row[planet]['user_id'] != user['id'] && row[planet]['ally_id'] != 0)
            {
				TextColor = "<font color=\"green\">";
				EndColor  = "</font>";
			}
            else if (row[planet]['user_id'] == user['id'])
            {
				TextColor = "<font color=\"red\">";
				EndColor  = "</font>";
			}

			result += TextColor+row[planet]['name']+EndColor;
		}
        else if (row[planet] && row[planet]["destruyed"] != 0)
        {
			result += 'Планета уничтожена';
		}
        else
        {
            result += "&nbsp;";
        }

		result += "</div></th>";

		result += "<th style=\"white-space: nowrap;\" width=30>";

		if (row[planet] && row[planet]["luna_destruyed"] == 0 && row[planet]["luna_id"])
        {
			result += "<a style=\"cursor: pointer;\" class=\"tooltip_sticky\" data-tooltip-content=\"";
			result += "<table width=240>";
			result += "<tr>";
			result += "<td class=c colspan=2>";
			result += "Луна: "+row[planet]["luna_name"]+" ["+galaxy+":"+system+":"+planet+"]";
			result += "</td>";
			result += "</tr><tr>";
			result += "<th width=80>";
			result += "<img src="+dpath+"planeten/mond.jpg height=75 width=75 />";
			result += "</th>";
			result += "<th>";
			result += "<table class='table'>";
			result += "<tr>";
			result += "<td class=c colspan=2>Характеристики</td>";
			result += "</tr><tr>";
			result += "<th>Диаметр</th>";
			result += "<th>"+XNova.format(row[planet]['luna_diameter'])+"</th>";
			result += "</tr><tr>";
			result += "<th>Температура</th><th>"+row[planet]['luna_temp']+"</th>";
			result += "</tr><tr>";
			result += "<td class=c colspan=2>Действия</td>";
			result += "</tr><tr>";
			result += "<th colspan=2 align=center>";

			if (row[planet]['user_id'] != user['id'])
            {
				result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&amp;planettype=3&amp;target_mission=1>Атаковать</a><br />";
				result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&planettype=3&target_mission=5>Удерживать</a><br />";

				if (user['destroy'] > 0)
                {
					result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&planettype=3&target_mission=9>Уничтожить</a><br>";
				}
			}
            else
            {
				result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&planettype=3&target_mission=4>Оставить</a><br />";
			}

			result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&planettype=3&target_mission=3>Транспорт</a><br />";

			result += "</tr>";
			result += "</table>";
			result += "</th>";
			result += "</tr>";
			result += "</table>\">";
			result += "<img src=\""+dpath+"planeten/small/s_mond.jpg\" height=\"30\" width=\"30\"></a>";
		}
        else if (row[planet] && row[planet]["luna_destruyed"] > 0 && row[planet]["luna_id"])
			result += "~";
		else
            result += "&nbsp;";

		result += "</th>";

		if (row[planet] && (row[planet]["metal"] != 0 || row[planet]["crystal"] != 0))
        {
			result += "<th style=\"";

			if ((parseInt(row[planet]["metal"]) + parseInt(row[planet]["crystal"])) >= 10000000) {
				result += "background-color: rgb(100, 0, 0);";
			} else if ((parseInt(row[planet]["metal"]) + parseInt(row[planet]["crystal"])) >= 1000000) {
				result += "background-color: rgb(100, 100, 0);";
			} else if ((parseInt(row[planet]["metal"]) + parseInt(row[planet]["crystal"])) >= 100000) {
				result += "background-color: rgb(0, 100, 0);";
			}

			result += "background-image: none;\" width=30>";
			result += "<a style=\"cursor: pointer;\" class=\"tooltip_sticky\" data-tooltip-content=\"";
			result += "<table width=240>";
			result += "<tr>";
			result += "<td class=c colspan=2>";
			result += "Обломки: ["+galaxy+":"+system+":"+planet+"]";
			result += "</td>";
			result += "</tr><tr>";
			result += "<th width=80>";
			result += "<img src="+dpath+"planeten/debris.jpg height=75 width=75 />";
			result += "</th>";
			result += "<th>";
			result += "<table class='table'>";
			result += "<tr>";
			result += "<td class=c colspan=2>Ресурсы</td>";
			result += "</tr><tr>";
			result += "<th>Металл</th><th>"+row[planet]['metal']+"</th>";
			result += "</tr><tr>";
			result += "<th>Кристалл</th><th>"+row[planet]['crystal']+"</th>";
			result += "</tr>";

			if (user['recycler'] > 0)
				result += "<tr><th colspan=2 align=left><a href=# onclick=QuickFleet(8,"+galaxy+","+system+","+planet+",2,0)>Собрать</a></th></tr>";

			result += "<tr><th colspan=2 align=left><a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&planettype=2&target_mission=8>Отправить флот</a></th>";
			result += "</tr></table>";
			result += "</th>";
			result += "</tr>";
			result += "</table>\">";
			result += "<img src="+dpath+"planeten/debris.jpg height=22 width=22></a>";
		}
        else
			result += "<th style=\"white-space: nowrap;\" width=30>&nbsp;";

		result += "</th>";
		result += "<th width=150>";

		if (row[planet] && row[planet]['user_id'] && row[planet]["destruyed"] == 0)
        {
			var CurrentPoints 	= user['total_points'];
			var RowUserPoints 	= row[planet]['total_points'];

			if (!RowUserPoints)
                RowUserPoints = 0;

			if (!row[planet]['total_rank'])
                row[planet]['total_rank'] = 0;

			var CurrentLevel  	= CurrentPoints * 5;
			var RowUserLevel  	= RowUserPoints * 5;

            var Systemtatus2 = '', Systemtatus = '';

			if (row[planet]['banaday'] > time && row[planet]['urlaubs_modus_time'] > 0) {
				Systemtatus2 = "U <a href=\"?set=banned\"><span class=\"banned\">G</span></a>";
				Systemtatus  = "<span class=\"vacation\">";
			} else if (row[planet]['banaday'] > time) {
				Systemtatus2 = "<a href=\"?set=banned\">G</a>";
				Systemtatus  = "<span class=\"banned\">";
			} else if (row[planet]['urlaubs_modus_time'] > 0) {
				Systemtatus2 = "U";
				Systemtatus  = "<span class=\"vacation\">";
			} else if (row[planet]['onlinetime'] == 1) {
				Systemtatus2 = "i";
				Systemtatus  = "<span class=\"inactive\">";
			} else if (row[planet]['onlinetime'] == 2) {
				Systemtatus2 = "iI";
				Systemtatus  = "<span class=\"longinactive\">";
			} else if (RowUserLevel < CurrentPoints && RowUserPoints < 50000) {
				Systemtatus2 = "N";
				Systemtatus  = "<span class=\"noob\">";
			} else if (RowUserPoints > CurrentLevel && CurrentPoints < 50000) {
				Systemtatus2 = "S";
				Systemtatus  = "<span class=\"strong\">";
			}

			if (Systemtatus2 != '')
                Systemtatus2 = ' <font color="white">(</font>'+Systemtatus2+'<font color="white">)</font>';

            var rank = '';

			if (row[planet]['authlevel'] == 3)
                rank = " <font color=\"red\">A</font>";
			else if (row[planet]['authlevel'] == 2)
                rank = " <font color=\"orange\">SGo</font>";
			else if (row[planet]['authlevel'] == 1)
                rank = " <font color=\"green\">Go</font>";

			var Systemtart = row[planet]['total_rank'];

			if (Systemtart < 100)
				Systemtart = 1;
			else
				Systemtart = (Math.floor( row[planet]['total_rank'] / 100 ) * 100) + 1;

            result += "<a style=\"cursor: pointer;\" class=\"tooltip_sticky\" data-tooltip-content='";
			result += "<table width=280>";
			result += "<tr>";
			result += "<td class=c colspan=2>Игрок "+row[planet]['username']+", место "+row[planet]['total_rank']+"</td>";
			result += "</tr><tr>";
			result += "<td width=122 height=126 rowspan=3 valign=middle class=c";

			if (row[planet]['user_image'] != 0)
			{
				result += " style=\"background:url("+XNova.path+"images/avatars/upload/"+row[planet]['user_image']+") 50% 50% no-repeat;\"></td>";
			}
			else if (row[planet]['avatar'] != 0)
            {
				if (row[planet]['avatar'] != 99)
                {
					result += " style=\"background:url("+XNova.path+"images/faces/"+row[planet]['sex']+"/"+row[planet]['avatar']+"s.png) 50% 50% no-repeat;\"></td>";
				}
                else
                {
					result += " style=\"background:url("+XNova.path+"images/avatars/upload/upload_"+row[planet]['user_id']+".jpg) 50% 50% no-repeat;\"></td>";
				}
			}
			else if (row[planet]['photo'] != '')
				result += " style=\"background:url("+row[planet]['photo']+") 50% 50% no-repeat;\"></td>";
            else
				result += ">нет<br>аватара</td>";

			if (row[planet]['user_id'] != user['id'])
            {
				result += "<th><a href=?set=messages&mode=write&id="+row[planet]['user_id']+">Послать сообщение</a></th>";
				result += "</tr><tr>";
				result += "<th><a href=?set=buddy&a=2&u="+row[planet]['user_id']+">Добавить в друзья</a></th>";
				result += "</tr><tr>";
			}

			result += "<th valign=top><a href=?set=stat&who=1&range="+Systemtart+"&pid="+row[planet]['user_id']+">Статистика</a></th>";
			result += "</tr>";
			result += "</table>'>";
			result += Systemtatus+row[planet]['username']+Systemtatus2+rank;
			result += "</span></a></th><th width='16'>";

			if (row[planet]['race'] == 0) {
				result += "&nbsp;";
			} else {
				result += "<a href='?set=infos&gid=70"+row[planet]['race']+"'><img src='"+dpath+"images/race"+row[planet]['race']+".gif' width='16' height='16' alt='"+race_str[row[planet]['race']]+"' title='"+race_str[row[planet]['race']]+"'></a>";
			}
		}
        else
            result += "&nbsp;</th><th width='18'>&nbsp;";

		result += "</th><th width=80>";

		if (row[planet] && row[planet]['ally_id'] != 0)
        {
			if (row[planet]['ally_name'])
            {
				result += "<a style=\"cursor: pointer;\" class=\"tooltip_sticky\" data-tooltip-content=\"";
				result += "<table width=240>";
				result += "<tr>";
				result += "<td class=c>Альянс "+row[planet]['ally_name']+" с "+row[planet]['ally_members']+" членами</td>";
				result += "</tr>";
				result += "<tr><th><a href=?set=alliance&mode=ainfo&a="+row[planet]['ally_id']+">Информация</a></th>";
				result += "</tr><tr>";
				result += "<th><a href=?set=stat&start=0&who=2>Статистика</a></th></tr>";

				if (row[planet]["ally_web"] != "") {
					result += "<tr><th><a href="+row[planet]["ally_web"]+" target=_new>Сайт альянса</th>";
				}

				result += "</table>\">";

				if (user['ally_id'] == row[planet]['ally_id']) {
					result += "<span class=\"allymember\">"+row[planet]['ally_tag']+"</span></a>";
				} else {
					result += row[planet]['ally_tag']+"</a>";
				}

				if (row[planet]['ally_id'] != user['ally_id'])
                {
					if (row[planet]['type'] == 0)
						result += "<br><small>[нейтральное]</small>";
					else if (row[planet]['type'] == 1)
						result += "<br><small><font color=\"orange\">[перемирие]</font></small>";
					else if (row[planet]['type'] == 2)
						result += "<br><small><font color=\"green\">[мир]</font></small>";
					else if (row[planet]['type'] == 3)
						result += "<br><small><font color=\"red\">[война]</font></small>";
				}
			}
		}
        else
            result += "&nbsp;";

		result += "</th><th style=\"white-space: nowrap;\" width=125>";

		if (row[planet] && row[planet]['user_id'] != user['id'])
        {
			if (row[planet]['user_id'] && row[planet]["destruyed"] == 0)
            {
				result += "<a href=\"javascript:;\" title=\"Отправить сообщение\" onclick=\"showWindow('"+row[planet]['username']+": отправить сообщение', '?set=messages&mode=write&id="+row[planet]["user_id"]+"&ajax&popup', 680)\"><span class='sprite skin_m'></span></a>&nbsp;";

				result += "<a href=\"?set=buddy&a=2&amp;u="+row[planet]["user_id"]+"\" title=\"Добавить в друзья\"><span class='sprite skin_b'></span></a>&nbsp;";

				if (user['missile'] == 1)
					result += "<a href=\"?set=galaxy&r=2&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&current="+user['current_planet']+"\" title=\"Ракетная атака\"><span class='sprite skin_r'></span></a>&nbsp;";

				if (user['spy_sonde'] > 0 && row[planet]['urlaubs_modus_time'] == 0)
					result += "<a href=\"javascript:;\" title=\"Шпионаж\" class=\"tooltip_sticky\" data-tooltip-content='<center><input type=text name=\"spy"+planet+"\" id=\"spy"+planet+"\" value=\""+user['spy']+"\"><br><input type=button class=spyButton data-planet=\""+planet+"\" data-type=\""+row[planet]['planet_type']+"\" value=\"Отправить на планету\">"+((row[planet]["luna_destruyed"] == 0 && row[planet]["luna_id"]) ? "<br><input type=button class=spyButton data-planet=\""+planet+"\" data-type=\"3\" value=\"Отправить на луну\">" : "")+"</center>'><span class='sprite skin_e'></span></a>&nbsp;";

				result += "<a href=\"?set=players&id="+row[planet]["user_id"]+"\" title=\"Информация об игроке\"><span class='sprite skin_s'></span></a>&nbsp;";
				result += "<a href=\"?set=fleet&page=shortcut&mode=add&g="+galaxy+"&s="+system+"&i="+planet+"&t="+row[planet]['planet_type']+"\" title=\"Добавить в закладки\"><span class='sprite skin_z'></span></a>";
			}
		}
        else if (!row[planet] && user['colonizer'] > 0)
                result += "<a href=\"?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet="+planet+"&amp;target_mission=7\" title=\"Колонизация\"><span class='sprite skin_e'></span></a>&nbsp;";
		else
            result += "&nbsp;";

		result += "</th>";
		result += "</tr>";
	}
	
	result += "<tr><th width=\"30\">16</th>";
	result += "<th colspan=8 class='c big'>";
	result += "<a href=?set=fleet&galaxy="+galaxy+"&amp;system="+system+"&amp;planet=16&amp;target_mission=15>неизведанные дали</a>";
	result += "</th>";
	result += "</tr><tr>";

	var PlanetCountMessage = '';
	
	if (planetcount == 1) {
		PlanetCountMessage = planetcount+" заселённая планета";
	} else if (planetcount == 0) {
		PlanetCountMessage = "нет заселённых планет";
	} else {
		PlanetCountMessage = planetcount+" заселённые планеты";
	}
	
	result += "<tr>";
	result += "<td class=c colspan=6>( "+PlanetCountMessage+" )</td>";
	result += "<td class=c colspan=3>";
	
	result += "<a href=\"javascript:;\" class=\"tooltip_sticky\" data-tooltip-content=\"";

	result += "<table width=240>";
	result += "<tr>";
	result += "<td width=220>Сильный игрок</td><td><span class=strong>S</span></td>";
	result += "</tr><tr>";
	result += "<td>Слабый игрок</td><td><span class=noob>N</span></td>";
	result += "</tr><tr>";
	result += "<td>Режим отпуска</td><td><span class=vacation>U</span></td>";
	result += "</tr><tr>";
	result += "<td>Заблокирован</td><td><span class=banned>G</span></td>";
	result += "</tr><tr>";
	result += "<td>Неактивен 7 дней</td><td><span class=inactive>i</span></td>";
	result += "</tr><tr>";
	result += "<td>Неактивен 28 дней</td><td><span class=longinactive>iI</span></td>";
	result += "</tr><tr>";
	result += "<td><font color=red>Администратор</font></td><td><font color=red>A</font></td>";
	result += "</tr><tr>";
	result += "<td><font color=green>Оператор</font></td><td><font color=green>GO</font></td>";
	result += "</tr><tr>";
	result += "<td><font color=orange>Супер оператор</font></td><td><font color=orange>SGO</font></td>";
	result += "</tr>";
	result += "</table>\">";
	result += "Легенда</a>";
	
	result += "</td>";
	result += "</tr>\n<tr>";
	result += "<td class=c colspan=3><span id=\"missiles\">"+user['interplanetary_misil']+"</span> межпланетных ракет</td>";
	result += "<td class=c colspan=3><span id=\"slots\">"+user['fleets']+"</span>/"+user['max_fleets']+" флотов</td>";
	result += "<td class=c colspan=3>";
	result += "<span id=\"recyclers\">"+XNova.format(user['recycler'])+"</span> переработчиков<br>";
	result += "<span id=\"probes\">"+XNova.format(user['spy_sonde'])+"</span> шпионских зондов</td>";
	result += "</tr></table></div>";
	result += "<div id=\"galaxyMessage\"></div>";

	return result;
}

function ChangePlanet(id)
{
	if (id > 0)
	{
		if (ajax_nav == 0)
			eval("location='?set=galaxy&r=3&"+document.getElementById('planet_select').options[id].value+"'");
		else
			load("?set=galaxy&r=3&"+document.getElementById('planet_select').options[id].value+"");
	}
}

function PrintSelector(fleet_shortcut)
{
	var result = '';

	result += "<form action=\"?set=galaxy&r=1\" method=\"post\" class='galaxy-select hidden-xs'>";
	result += "<input type=\"hidden\" class=\"auto\" value=\"dr\" >";
	result += "<div class='col-sm-4'>";

	result += "<table style='margin: 0 auto'><tr>";
	result += "<td class=\"c\" colspan=\"3\">Галактика</td></tr><tr>";
	result += "<th><input name=\"galaxyLeft\" value=\"&lt;-\" type=\"button\"></th>";
	result += "<th><input name=\"galaxy\" value=\""+galaxy+"\" size=\"5\" maxlength=\"3\" tabindex=\"1\" type=\"text\"></th>";
	result += "<th><input name=\"galaxyRight\" value=\"-&gt;\" type=\"button\"></th>";
	result += "</tr></table>";

	result += "</div><div class='col-sm-4'>";

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
	result += "<div class='col-sm-4'>";

	result += "<table style='margin: 0 auto'><tr>";
	result += "<td class=\"c\" colspan=\"3\">Солнечная система</td></tr><tr>";
	result += "<th><input name=\"systemLeft\" value=\"&lt;-\" type=\"button\"></th>";
	result += "<th><input name=\"system\" value=\""+system+"\" size=\"5\" maxlength=\"3\" tabindex=\"2\" type=\"text\"></th>";
	result += "<th><input name=\"systemRight\" value=\"-&gt;\" type=\"button\"></th>";
	result += "</tr></table>";

	result += "</div></form>";



	result += "<form action=\"?set=galaxy&r=1\" method=\"post\" class='galaxy-select visible-xs'>";
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