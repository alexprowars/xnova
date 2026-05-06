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