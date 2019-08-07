export function number (value)
{
	if (value > 1000000000)
		return number_format(Math.floor(value / 1000000), 0, ',', '.')+'kk';

	return number_format(value, 0, ',', '.');
}

export function time (value, separator, full)
{
	if (typeof separator === 'undefined')
		separator = '';

	if (typeof full === 'undefined')
		full = false;

	if (value < 0)
		return '-';

	let dd = Math.floor(value / (24 * 3600));
	let hh = Math.floor(value / 3600 % 24);
	let mm = Math.floor(value / 60 % 60);
	let ss = Math.floor(value / 1 % 60);

	let time = '';

	if (dd !== 0)
		time += ((separator !== '' && dd < 10) ? '0' : '')+dd+((separator !== '') ? separator : ' д. ');

	if (hh > 0 || full)
		time += ((separator !== '' && hh < 10) ? '0' : '')+hh+((separator !== '') ? separator : ' ч. ');

	if (mm > 0 || full)
		time += ((separator !== '' && mm < 10) ? '0' : '')+mm+((separator !== '') ? separator : ' м. ');

	time += ((separator !== '' && ss < 10) ? '0' : '')+ss+((separator !== '') ? '' : ' с. ');

	if (!time.length)
		time = '-';

	return time;
}

export function date (format, timestamp)
{
	timestamp = parseInt(timestamp);

	let jsdate = new Date(timestamp ? timestamp * 1000 : null);
	let pad = (n, c) =>
	{
		if ((n = n + "").length < c)
			return new Array(++c - n.length).join("0") + n;
		else
			return n;
	};

	let txt_weekdays = ["Sunday","Monday","Tuesday","Wednesday", "Thursday","Friday","Saturday"];
	let txt_ordin = {1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};
	let txt_months =  ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

	let f = {
		d: () => {
			return pad(f.j(), 2);
		},
		D: () => {
			let t = f.l(); return t.substr(0,3);
		},
		j: () => {
			return jsdate.getDate();
		},
		l: () => {
			return txt_weekdays[f.w()];
		},
		N: () => {
			return f.w() + 1;
		},
		S: () => {
			return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th';
		},
		w: () => {
			return jsdate.getDay();
		},
		z: () => {
			return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0;
		},
		W: () => {
			let a = f.z(), b = 364 + f.L() - a;
			let nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;

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
		F: () => {
			return txt_months[f.n()];
		},
		m: () => {
			return pad(f.n(), 2);
		},
		M: () => {
			let t = f.F(); return t.substr(0,3);
		},
		n: () => {
			return jsdate.getMonth() + 1;
		},
		t: () => {
			let n;
			if( (n = jsdate.getMonth() + 1) === 2 ){
				return 28 + f.L();
			} else{
				if( n & 1 && n < 8 || !(n & 1) && n > 7 ){
					return 31;
				} else{
					return 30;
				}
			}
		},
		L: () => {
			let y = f.Y();
			return (!(y && 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0;
		},
		//o not supported yet
		Y: () => {
			return jsdate.getFullYear();
		},
		y: () => {
			return (jsdate.getFullYear() + "").slice(2);
		},
		a: () => {
			return jsdate.getHours() > 11 ? "pm" : "am";
		},
		A: () => {
			return f.a().toUpperCase();
		},
		B: () => {
			// peter paul koch:
			let off = (jsdate.getTimezoneOffset() + 60)*60;
			let theSeconds = (jsdate.getHours() * 3600) +
							 (jsdate.getMinutes() * 60) +
							  jsdate.getSeconds() + off;
			let beat = Math.floor(theSeconds/86.4);
			if (beat > 1000) beat -= 1000;
			if (beat < 0) beat += 1000;
			if ((String(beat)).length === 1) beat = "00"+beat;
			if ((String(beat)).length === 2) beat = "0"+beat;
			return beat;
		},
		g: () => {
			return jsdate.getHours() % 12 || 12;
		},
		G: () => {
			return jsdate.getHours();
		},
		h: () => {
			return pad(f.g(), 2);
		},
		H: () => {
			return pad(jsdate.getHours(), 2);
		},
		i: () => {
			return pad(jsdate.getMinutes(), 2);
		},
		s: () => {
			return pad(jsdate.getSeconds(), 2);
		},

		O: () => {
		   let t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4);
		   if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t;
		   return t;
		},
		P: () => {
			let O = f.O();
			return (O.substr(0, 3) + ":" + O.substr(3, 2));
		},
		c: () => {
			return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P();
		},
		U: () => {
			return Math.round(jsdate.getTime()/1000);
		}
	};

	return format.replace(/[\\]?([a-zA-Z])/g, (t, s) =>
	{
		if (t !== s)
			return s;
		else if(f[s])
			return f[s]();
		else
			return s;
	});
}

export function morph (n, titles)
{
	return titles[(n % 10 === 1 && n % 100 !== 11) ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2]
}

function number_format(number, decimals, dec_point, thousands_sep)
{
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

	let n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s,
		toFixedFix = function (n, prec)
		{
			let k = Math.pow(10, prec);
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