<template>
	<div id="rf_techinfo"></div>
</template>

<script>
	import { ECOTree } from '~/utils/techtree'

	export default {
		name: 'techtree-info',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		methods: {
			createTree (tid, prntid, element, level, access, fwrd)
			{
				let data = JSON.parse(JSON.stringify(this.page['data'][element]));

				let active = "lime";

				if (!access)
					active = "red";

				if (element !== -1)
					window.object.add(tid, prntid, '<div class="tch_tx_nmcont"><span class="tch_tx_name">'+data.name+'</span></div><img id="tch_img_'+tid+'" name="'+element+'" src="'+'/images/gebaeude/' + data.img+'" class="tch_icon_' + active + '"><div class="tch_tx_lvl">'+level+'</div>', null, null, active, active, active);
				else
					window.object.add(tid, prntid, '<div class="tch_tx_nmcont"><span class="tch_tx_name">'+fwrd+'</span></div><img id="tch_img_'+tid+'" src="skins/sn_space_blue/images/pixel.png" class="tch_icon_' + active + '"><div class="tch_tx_lvl">'+level+'</div>', null, null, active, active, active);

				this.counter++
				window.object.UpdateTree();

				if (!access)
					$('#tch_img_'+prntid).attr('class', 'tch_icon_red');

				if (element !== -1)
				{
					if (data['req'].length)
					{
						let reqnum = data['req'].length;

						for (let i = 0; i < reqnum; i++)
						{
							let actclr = "lime";

							if (data['req'][i][2] < data['req'][i][4])
								actclr = "red";

							let lvtmp = '';

							if (data['req'][i][3] !== -1)
								lvtmp = '<font color='+actclr+'>' + data['req'][i][2] + '</font><font color=gold>+'+data['req'][i][3]+'</font>/<font color=lime>' + data['req'][i][4] + '</font>';
							else
								lvtmp = '<font color='+actclr+'>' + data['req'][i][2] + '</font>/<font color=lime>' + data['req'][i][4] + '</font>';

							let fwrld = '';

							if (data['req'][i][0] === -1)
								fwrld = data['req'][i][1];

							this.createTree(this.counter + 1, tid, data['req'][i][0], lvtmp, data['req'][i][2] < data['req'][i][4], fwrld);
						}
					}
				}

				window.object.UpdateTree();
			}
		},
		mounted ()
		{
			window.object = new ECOTree('object', 'rf_techinfo');
			this.counter = 0;

			this.createTree(1, -1, this.page['element'], this.page['level'], this.page['access']);
		}
	}
</script>