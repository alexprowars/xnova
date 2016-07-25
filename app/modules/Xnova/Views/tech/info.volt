<!--suppress ALL -->
<div id="rf_techinfo"></div>
<script type="text/javascript" src="{{ url.getBaseUri() }}assets/js/techtree.js"></script>
<script type="text/javascript">

	var objx = ({
		{% for registry->resource AS $id => $code %}
			{{ id }}:{
				'name':'{{ _text('tech', id) }}',
				'img':'{{ id }}.gif',
				'req':[
				{% if (isset(registry->requeriments[$id]) and count(registry->requeriments[$id]) > 0 %}
					{% for registry->requeriments[$id] AS $ids => $level %}
						[{{ ids }},'{{ _text('tech', ids) }}',<?=(isset(user.{registry->resource[$ids]}) ? user.{registry->resource[$ids]} : planet->{registry->resource[$ids]}) ?>,-1,{{ level }}],
					{% endfor %}
				{% else %}
					['no']
				{% endif %}
				]
			},
		{% endfor %}
		0:{}
	})

	var rftr = new ECOTree('rftr', 'rf_techinfo');
	var ndcnt = 0;
	var mainred = 0;

	function CreateTree(tid, prntid, idx, lvltx, act_ind, fwrd)
	{
		var active = "lime";

		if (act_ind == 0)
		{
			active = "red";
			mainred = 1;
		}

		if (idx != -1)
			rftr.add(tid, prntid, '<div class="tch_tx_nmcont"><span class="tch_tx_name">'+objx[idx].name+'</span></div><img id="tch_img_'+tid+'" name="'+idx+'" src="{{ url.getBaseUri() }}assets/images/gebaeude/' + objx[idx].img + '" class="tch_icon_' + active + '" /><div class="tch_tx_lvl">'+lvltx+'</div>', null, null, active, active, active);
		else
			rftr.add(tid, prntid, '<div class="tch_tx_nmcont"><span class="tch_tx_name">'+fwrd+'</span></div><img id="tch_img_'+tid+'" src="skins/sn_space_blue/images/pixel.png" class="tch_icon_' + active + '" /><div class="tch_tx_lvl">'+lvltx+'</div>', null, null, active, active, active);

		ndcnt = ndcnt + 1;
		rftr.UpdateTree();

		if (active == "red")
			$('#tch_img_'+prntid).attr('class', 'tch_icon_red');

		if (idx != -1)
		{
			if (objx[idx].req[0][0] != 'no')
			{
				var reqnum = objx[idx].req.length;

				for (var i = 0; i < reqnum; i++)
				{
					var actclr = "lime";

					if (objx[idx].req[i][2] < objx[idx].req[i][4])
						actclr = "red";

					var lvtmp = '';

					if (objx[idx].req[i][3] != -1)
						lvtmp = '<font color='+actclr+'>' + objx[idx].req[i][2] + '</font><font color=gold>+'+objx[idx].req[i][3]+'</font>/<font color=lime>' + objx[idx].req[i][4] + '</font>';
					else
						lvtmp = '<font color='+actclr+'>' + objx[idx].req[i][2] + '</font>/<font color=lime>' + objx[idx].req[i][4] + '</font>';

					var fwrld = '';

					if (objx[idx].req[i][0] == -1)
					{
						fwrld = objx[idx].req[i][1];
					}
					CreateTree(ndcnt + 1, tid, objx[idx].req[i][0], lvtmp, ((objx[idx].req[i][2] < objx[idx].req[i][4]) ? 0 : 1), fwrld);
				}
			}
		}
		rftr.UpdateTree();

	}

	CreateTree(1, -1, {{ element }}, '<?=(isset(user.{registry->resource[$element]}) ? user.{registry->resource[$element]} : planet->{registry->resource[$element]}) ?>', <?=(\Xnova\Building::IsTechnologieAccessible(user, planet, $element) ? 1 : 0) ?>);

</script>