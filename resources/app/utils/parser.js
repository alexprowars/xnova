import i18n from '../i18n.js';
import DOMPurify from 'isomorphic-dompurify';

export function sanitizeHtml(html) {
	return DOMPurify.sanitize(html ?? '', {
		USE_PROFILES: { html: true },
		FORBID_TAGS: ['style'],
		ADD_ATTR: ['target'],
	});
}

function tableAttributes(value) {
	const attributes = [];
	const width = value.match(/\(w=([0-9]{1,3})\)/i);
	const colspan = value.match(/\(cs=([0-9]{1,2})\)/i);
	const classes = value.match(/\(cl=([a-z0-9_ -]+)\)/i);

	if (width) {
		attributes.push('style="width:' + Math.max(1, Math.min(Number(width[1]), 100)) + '%"');
	}

	if (colspan) {
		attributes.push('colspan="' + Math.max(1, Number(colspan[1])) + '"');
	}

	if (classes) {
		attributes.push('class="' + classes[1] + '"');
	}

	return attributes.length ? ' ' + attributes.join(' ') : '';
}

export default {
	patterns: {
		find: [
			/\n/g,
			/\[quote\](.*?)\[\/quote\]/gi,
			/\[quote author=(.*?)\](.*?)\[\/quote\]/gi,
			/\[b\](.*?)\[\/b\]/gi,
			/\[i\](.*?)\[\/i\]/gi,
			/\[u\](.*?)\[\/u\]/gi,
			/\[s\](.*?)\[\/s\]/gi,
			/\[left\](.*?)\[\/left\]/gi,
			/\[center\](.*?)\[\/center\]/gi,
			/\[right\](.*?)\[\/right\]/gi,
			/\[justify\](.*?)\[\/justify\]/gi,
			/\[size=([1-9]|1[0-9]|2[0-5])\](.*?)\[\/size\]/gi,
			/\[color=#?([A-F0-9]{3}|[A-F0-9]{6})\](.*?)\[\/color\]/gi,
			/\[img\](https?:\/\/.*?\.(?:jpg|jpeg|gif|png|bmp))\[\/img\]/gi,
			/\[img_big\](https?:\/\/.*?\.(?:jpg|jpeg|gif|png|bmp))\[\/img_big\]/gi,
			/\[url=((?:ftp|https?):\/\/.*?)\](.*?)\[\/url\]/g,
			/\[url\]((?:ftp|https?):\/\/.*?)\[\/url\]/g,
			/\[numlist\](.*?)\[\/numlist\]/gi,
			/\[list\]([\s\S]*?)\[\/list\]/gi,
			/\[\*\](.*?)\[\/\*\]/gi,
			/\[youtube\]http:\/\/www.youtube.com\/watch\?v=(.*?)\[\/youtube\]/gi,
			/\[spoiler=(.*?)\](.*?)\[\/spoiler\]/gi,
			/\[bgcolor=#?([A-F0-9]{3}|[A-F0-9]{6})\](.*?)\[\/bgcolor\]/gi,
			/\[background=(https?:\/\/.*?\.(?:jpg|jpeg|gif|png|bmp)) w=([0-9]*) h=([0-9]*)\](.*?)\[\/background\]/gi,
			/\[p\](.*?)\[\/p\]/gi,
			/\[([1-9]{1}):([0-9]{1,3}):([0-9]{1,2})\]/gi,
			/\[table(.*?)\](.*?)\[\/table\]/gi,
			/\[tr\](.*?)\[\/tr\]/gi,
			/\[td(.*?)\](.*?)\[\/td\]/gi,
			/\[th(.*?)\](.*?)\[\/th\]/gi,
		],
		replace: [
			'<br>',
			'<div class="quotewrapper"><div class="quotecontent">$1</div></div>',
			(match, author, content) => '<div class="quotewrapper"><div class="quotetitle">' + author + ' ' + i18n.global.t('editor.wrote') + ':</div><div class="quotecontent">' + content + '</div></div>',
			'<strong>$1</strong>',
			'<em>$1</em>',
			'<span style="text-decoration: underline;">$1</span>',
			'<span style="text-decoration: line-through;">$1</span>',
			'<div align="left">$1<\/div>',
			'<div align="center">$1<\/div>',
			'<div align="right">$1<\/div>',
			'<div style="text-align:justify;word-spacing:-0.3ex;">$1<\/div>',
			'<span style="font-size: $1px;">$2</span>',
			'<span style="color: #$1;">$2</span>',
			'<a href="$1" target="_blank"><img src="$1" style="max-width:300px;" alt=""></a>',
			'<img src="$1" style="max-width:100%;" class="image" alt="">',
			'<a href="$1" target="_blank">$2</a>',
			'<a href="$1" target="_blank">$1</a>',
			"<ol>$1</ol>",
			"<ul>$1</ul>",
			"<li>$1</li>",
			'<a href="https://www.youtube.com/watch?v=$1" target="_blank" rel="noopener noreferrer">YouTube</a>',
			'<details><summary class="quotetitle">$1</summary><div class="quotecontent">$2</div></details>',
			'<span style="background-color:#$1;">$2</span>',
			'<span style="background-image:url($1);background-repeat:no-repeat;display:block;width:$2;height:$3;max-width:716px;">$4</span>',
			'<p>$1</p>',
			'<a href="/galaxy?galaxy=$1&system=$2">[$1:$2:$3]</a>',
			(match, attributes, content) => '<table' + tableAttributes(attributes) + '>' + content + '</table>',
			'<tr>$1</tr>',
			(match, attributes, content) => '<td' + tableAttributes(attributes) + '>' + content + '</td>',
			(match, attributes, content) => '<th' + tableAttributes(attributes) + '>' + content + '</th>',
		],
		smiles: [
			'adolf','am','angel','angl','aplause','baby','boxing','bye','crazy','dollar','duel','evil','face1','face2','face5','fingal','fuu','girl','gun1','ha',
			'happy','heart','hello','help','hummer','hummer2','ill','inlove','jack','jedy','killed','king','kiss2','knut','lick','lips','lol','med','roze','mol',
			'ninja','nunchak','ogo','pare','police','prise','punk','ravvin','rip','rupor','scare','shut','sleep','song','strong','training','user','wall','rofl',
			'hunter','bratan','diskot','vglaz','duet','ff','smoke','bita','perec','popope','morpeh','naem','pirat','baraban','klizma','gamer2','pulemet','good2',
			'negative','quiet','ball','pooh','vv','fig1', 'spam', 'arbuz'
		],
	},
	parse(txt) {
		if (txt === null) {
			return '';
		}

		let j = 0;

		this.patterns.smiles.every((smile) => {
			while (txt.indexOf(':' + smile + ':') >= 0) {
				txt = txt.replace(':' + smile + ':', '<img src="/assets/images/smile/' + smile + '.gif" alt="' + smile + '">');

				if (++j >= 3) {
					break;
				}
			}

			return j < 3;
		})

		this.patterns.find.forEach((part, i) => {
			txt = txt.replace(part, this.patterns.replace[i]);

			if (i === 2 || i === 3 || i === 22) {
				while (txt.match(part)) {
					txt = txt.replace(part, this.patterns.replace[i]);
				}
			}
		});

		return sanitizeHtml(txt);
	},
	addTag (tag, select, type) {
		if (typeof type === 'undefined') {
			type = 0;
		}

		let tags = tag.split('|');

		let openTag = tags[0];
		let closeTag = tags[1];

		let rep = '', url;

		if (type === 1) {
			url = prompt(i18n.global.t('editor.enter_link'), '');
		} else if (type === 2) {
			url = prompt(i18n.global.t('editor.enter_video'), '');
		} else if (type === 3 || type === 4) {
			url = prompt(i18n.global.t('editor.enter_image'), '');
		} else if (type === 6) {
			url = prompt(i18n.global.t('editor.enter_audio'), '');
		}

		if (type > 0 && type <= 6 && (url === '' || url === null)) {
			return '';
		}

		if (type === 0) {
			rep = openTag + select + closeTag;
		} else if (type === 1) {
			if (select === "") {
				rep = '[url]' + url + '[/url]';
			} else {
				rep = '[url=' + url + ']' + select + '[/url]';
			}
		} else if (type === 2) {
			rep = '[youtube]'  + url + '[/youtube]';
		} else if (type === 3) {
			rep = '[img]'  + url + '[/img]';
		} else if (type === 4) {
			rep = '[img_big]'  + url + '[/img_big]';
		} else if (type === 5) {
			let list = select.split('\n');

			for (let i = 0;i < list.length; i++) {
				list[i] = '[*]' + list[i] + '[/*]';
			}

			rep = openTag + '\n' + list.join("\n") + '\n' +closeTag;
		} else if (type === 6) {
			rep = '[mp3]'  + url + '[/mp3]';
		}

		return rep;
	}
}