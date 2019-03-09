//import {$get} from 'api'
//import app from 'app'

function addScript (url)
{
	let script = document.createElement('script');
	script.setAttribute('src', url);

	document.head.appendChild(script);
}

const loadPage = (url) =>
{
	return new Promise((resolve, reject) =>
	{
		if (app.request_block)
			return reject('request block');

		app.request_block = true;
		app.loader = true;

		app.request_block_timer = setTimeout(() => {
			app.request_block = false
		}, 500);

		$get(url).then((data) =>
		{
			if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['popup'] !== '')
			{
				$.confirm({
					title: 'Обучение',
					content: data['tutorial']['popup'],
					confirmButton: 'Продолжить',
					cancelButton: false,
					backgroundDismiss: false,
					confirm: () =>
					{
						if (data['tutorial']['url'] !== '')
							app.$router.push(data['tutorial']['url']);
					}
				});
			}

			if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['toast'] !== '')
			{
				$.toast({
					text: data['tutorial']['toast'],
					icon: 'info',
					stack: 1
				});
			}

			resolve(data)
		}, () => {
			reject();
			document.location = url;
		})
		.then(() =>
		{
			app.loader = false;
			app.request_block = false;

			clearTimeout(app.request_block_timer);
		})
	});
}

export {
	addScript,
	loadPage
}