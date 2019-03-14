export default ({ store, $axios }, inject) =>
{
	$axios.setHeader('X-Requested-With', 'XMLHttpRequest');
	$axios.defaults.timeout = 15000;

	const $get = (url, data) =>
	{
		if (data === undefined)
			data = {}

		data['_'] = Math.random()

		return $axios({
			method: 'get',
			url: url,
			params: data
		})
		.then(result => {
			return parseData(result.data.data)
		})
		.catch((error) => {
			return parseData(error.response.data.data)
		})
	}

	const $post = (url, data) =>
	{
		let headers = {}

		if (data.toString().indexOf('FormData') < 0)
			data = objectToFormData(data)

		headers['Content-Type'] = 'multipart/form-data'

		return $axios({
			url: url,
			method: 'post',
			data: data,
			headers: headers
		})
		.then(result => {
			return parseData(result.data.data)
		})
		.catch((error) => {
			return parseData(error.response.data.data)
		})
	}

	inject('get', $get)
	inject('post', $post)
}

function parseData (data)
{
	if (data !== undefined)
	{
		if (data.page !== undefined && Array.isArray(data.page) && data.page.length === 0)
			data.page = null

		return data
	}
	else
		throw new Error("request error");
}

function objectToFormData (obj, rootName)
{
	let formData = new FormData();

	function appendFormData(data, root)
	{
		root = root || '';

		if (data instanceof File)
			formData.append(root, data);
		else if (Array.isArray(data))
		{
			for (let i = 0; i < data.length; i++)
				appendFormData(data[i], root + '[' + i + ']');
		}
		else if (typeof data === 'object' && data)
		{
			for (let key in data)
			{
				if (data.hasOwnProperty(key))
				{
					if (root === '')
						appendFormData(data[key], key);
					else
						appendFormData(data[key], root + '[' + key + ']');
				}
			}
		}
		else
		{
			if (data !== null && typeof data !== 'undefined')
				formData.append(root, data);
		}
	}

	appendFormData(obj, rootName);

	return formData;
}