function date ( format, timestamp )
{
	var a, jsdate = new Date(timestamp ? timestamp * 1000 : null);
	var pad = function(n, c){
		if( (n = n + "").length < c ) {
			return new Array(++c - n.length).join("0") + n;
		} else {
			return n;
		}
	};
	var txt_weekdays = ["Sunday","Monday","Tuesday","Wednesday",
		"Thursday","Friday","Saturday"];
	var txt_ordin = {1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};
	var txt_months =  ["", "January", "February", "March", "April",
		"May", "June", "July", "August", "September", "October", "November",
		"December"];

	var f = {
			d: function(){
				return pad(f.j(), 2);
			},
			D: function(){
				t = f.l(); return t.substr(0,3);
			},
			j: function(){
				return jsdate.getDate();
			},
			l: function(){
				return txt_weekdays[f.w()];
			},
			N: function(){
				return f.w() + 1;
			},
			S: function(){
				return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th';
			},
			w: function(){
				return jsdate.getDay();
			},
			z: function(){
				return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0;
			},
			W: function(){
				var a = f.z(), b = 364 + f.L() - a;
				var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;

				if(b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b){
					return 1;
				} else{

					if(a <= 2 && nd >= 4 && a >= (6 - nd)){
						nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31");
						return date("W", Math.round(nd2.getTime()/1000));
					} else{
						return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0);
					}
				}
			},
			F: function(){
				return txt_months[f.n()];
			},
			m: function(){
				return pad(f.n(), 2);
			},
			M: function(){
				t = f.F(); return t.substr(0,3);
			},
			n: function(){
				return jsdate.getMonth() + 1;
			},
			t: function(){
				var n;
				if( (n = jsdate.getMonth() + 1) == 2 ){
					return 28 + f.L();
				} else{
					if( n & 1 && n < 8 || !(n & 1) && n > 7 ){
						return 31;
					} else{
						return 30;
					}
				}
			},
			L: function(){
				var y = f.Y();
				return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0;
			},
			//o not supported yet
			Y: function(){
				return jsdate.getFullYear();
			},
			y: function(){
				return (jsdate.getFullYear() + "").slice(2);
			},
			a: function(){
				return jsdate.getHours() > 11 ? "pm" : "am";
			},
			A: function(){
				return f.a().toUpperCase();
			},
			B: function(){
				// peter paul koch:
				var off = (jsdate.getTimezoneOffset() + 60)*60;
				var theSeconds = (jsdate.getHours() * 3600) +
								 (jsdate.getMinutes() * 60) +
								  jsdate.getSeconds() + off;
				var beat = Math.floor(theSeconds/86.4);
				if (beat > 1000) beat -= 1000;
				if (beat < 0) beat += 1000;
				if ((String(beat)).length == 1) beat = "00"+beat;
				if ((String(beat)).length == 2) beat = "0"+beat;
				return beat;
			},
			g: function(){
				return jsdate.getHours() % 12 || 12;
			},
			G: function(){
				return jsdate.getHours();
			},
			h: function(){
				return pad(f.g(), 2);
			},
			H: function(){
				return pad(jsdate.getHours(), 2);
			},
			i: function(){
				return pad(jsdate.getMinutes(), 2);
			},
			s: function(){
				return pad(jsdate.getSeconds(), 2);
			},

			O: function(){
			   var t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4);
			   if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t;
			   return t;
			},
			P: function(){
				var O = f.O();
				return (O.substr(0, 3) + ":" + O.substr(3, 2));
			},
			c: function(){
				return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P();
			},
			U: function(){
				return Math.round(jsdate.getTime()/1000);
			}
	};

	return format.replace(/[\\]?([a-zA-Z])/g, function(t, s){
		if( t!=s ){
			// escaped
			ret = s;
		} else if( f[s] ){
			// a date function exists
			ret = f[s]();
		} else{
			// nothing special
			ret = s;
		}

		return ret;
	});
}

function number_format(number, decimals, dec_point, thousands_sep)
{
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec)
		{
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};

	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

	if (s[0].length > 3)
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);

	if ((s[1] || '').length < prec)
	{
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}

	return s.join(dec);
}

var Format = {
	number: function(value)
	{
		if (value > 1000000000)
			return number_format(Math.floor(value / 1000000), 0, ',', '.')+'kk';

		return number_format(value, 0, ',', '.');
	},
	time: function (value, separator)
	{
		if (typeof separator === 'undefined')
			separator = '';

		var dd = Math.floor(value / (24 * 3600));
		var hh = Math.floor(value / 3600 % 24);
		var mm = Math.floor(value / 60 % 60);
		var ss = Math.floor(value / 1 % 60);

		var time = '';

		if (dd !== 0)
			time += ((separator !== '' && dd < 10) ? '0' : '')+dd+((separator !== '') ? separator : ' д. ');

		if (hh > 0)
			time += ((separator !== '' && hh < 10) ? '0' : '')+hh+((separator !== '') ? separator : ' ч. ');

		if (mm > 0)
			time += ((separator !== '' && mm < 10) ? '0' : '')+mm+((separator !== '') ? separator : ' мин. ');

		if (ss !== 0)
			time += ((separator !== '' && ss < 10) ? '0' : '')+ss+((separator !== '') ? '' : ' с. ');

		if (!time.length)
			time = '-';

		return time;
	}
}

function raport_to_bb(raport)
{
	raport = $('#'+raport);

	var txt = raport.html();

	txt = txt.replace(/<tbody>/gi, "");
	txt = txt.replace(/<\/tbody>/gi, "");
	txt = txt.replace(/<tr>/gi, "[tr]");
	txt = txt.replace(/<\/tr>/gi, "[\/tr]");
	txt = txt.replace(/<td>/gi, "[td]");
	txt = txt.replace(/<\/td>/gi, "[\/td]");
	txt = txt.replace(/<\/table>/gi, "[\/table]");
	txt = txt.replace(/<th>/gi, "[th]");
	txt = txt.replace(/<th width="40%">/gi, "[th(w=40)]");
	txt = txt.replace(/<th width="10%">/gi, "[th(w=10)]");
	txt = txt.replace(/<\/th>/gi, "[\/th]");
	txt = txt.replace(/<td class="c" colspan="4">/gi, "[td(cl=c)(cs=4)]");
	txt = txt.replace(/<td colspan="4" class="c">/gi, "[td(cl=c)(cs=4)]");
	txt = txt.replace(/<table width="100%">/gi, "[table(w=100)]");
	txt = txt.replace(/<table width="100%" cellspacing="1">/gi, "[table(w=100)]");
	txt = txt.replace(/<table cellspacing="1" width="100%">/gi, "[table(w=100)]");
	txt = txt.replace(/<th width="220" align="right">/gi, "[th(w=33)]");
	txt = txt.replace(/<th align="right" width="220">/gi, "[th(w=33)]");
	txt = txt.replace(/<th width="220">/gi, "[th]");
	txt = txt.replace(/<br>/gi, " ");
	txt = txt.replace(/<\/a>/gi, "[\/url]");
	txt = txt.replace(/<a href="(.*?)">/gi, "[url=https://x.xnova.su$1]");

	raport.html(txt);
}