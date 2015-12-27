<form name="Atr" action="">
	<div class="separator"></div>
	<table width="100%">
		<tr>
			<th>
				Текущее производство: <div id=bx class=z></div>
			</th>
		</tr>
		<tr>
			<th><select name="auftr" disabled size="5" style="width:100%;"></select></th>
		</tr>
		<tr>
			<td class="c">Оставшееся время <?=$parse['time'] ?></td>
		</tr>
	</table>
	<div class="separator"></div>
</form>
<script type="text/javascript">
	v = new Date();
	p = 0;
	g = <?=$parse['b_hangar_id_plus'] ?>;
	s = 0;
	hs = 0;
	of = 1;
	c = new Array(<?=$parse['c'] ?>'');
	b = new Array(<?=$parse['b'] ?>'');
	a = new Array(<?=$parse['a'] ?>'');
	aa = 'завершено';

	function t() {
		if (hs == 0) {
			xd();
			hs = 1;
		}
		n = new Date();
		s = c[p] - g - Math.round((n.getTime() - v.getTime()) / 1000);
		s = Math.round(s);
		m = 0;
		h = 0;
		if (s < 0) {
			a[p]--;
			xd();
			if (a[p] <= 0) {
				p++;
				xd();
			}
			g = 0;
			v = new Date();
			s = 0;
		}
		if (s > 59) {
			m = Math.floor(s / 60);
			s = s - m * 60;
		}
		if (m > 59) {
			h = Math.floor(m / 60);
			m = m - h * 60;
		}
		if (s < 10) {
			s = "0" + s;
		}
		if (m < 10) {
			m = "0" + m;
		}
		if (p > b.length - 2) {
			$("#bx").html(aa);
		} else {
			$("#bx").html(b[p] + " " + h + ":" + m + ":" + s);
		}
		window.setTimeout("t();", 200);
	}

	function xd()
	{
		while (document.Atr.auftr.length > 0) {
			document.Atr.auftr.options[document.Atr.auftr.length - 1] = null;
		}
		if (p > b.length - 2) {
			document.Atr.auftr.options[document.Atr.auftr.length] = new Option(aa);
		}
		for (iv = p; iv <= b.length - 2; iv++) {
			if (a[iv] < 2) {
				ae = " ";
			} else {
				ae = " ";
			}
			if (iv == p) {
				act = " (в процессе)";
			} else {
				act = "";
			}
			document.Atr.auftr.options[document.Atr.auftr.length] = new Option(a[iv] + ae + " \"" + b[iv] + "\"" + act, iv + of);
		}
	}

	t();
</script>