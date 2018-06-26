function addScript (url)
{
	let script = document.createElement('script');
	script.setAttribute('src', url);

	document.head.appendChild(script);
}

export {
	addScript
}