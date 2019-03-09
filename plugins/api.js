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
		.then(result =>
		{
			if (result.data.data !== undefined)
			{
				if (result.data.data.page !== undefined && Array.isArray(result.data.data.page) && result.data.data.page.length === 0)
					result.data.data.page = null

				return result.data.data
			}
			else
				throw new Error("request error");
		})
	}

	const $post = (url, data) =>
	{
		let headers = {}

		if (data.toString().indexOf('FormData') < 0)
			data = $.param(data);
		else
			headers['Content-Type'] = 'multipart/form-data'

		return $axios({
			url: url,
			method: 'post',
			data: data,
			headers: headers
		})
		.then(result =>
		{
			if (result.data.data !== undefined)
			{
				if (result.data.data.page !== undefined && Array.isArray(result.data.data.page) && result.data.data.page.length === 0)
					result.data.data.page = null

				return result.data.data
			}
			else
				throw new Error("request error");
		})
	}

	inject('get', $get)
	inject('post', $post)
}