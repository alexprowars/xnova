let request_block = false;
let request_block_timer;

export default ({ app }) =>
{
	app.router.beforeEach((to, from, next) =>
	{
		if (request_block)
			return next(false);

		if (to.path === from.path && to.query.r === undefined)
		{
			to.query.r = Math.random()

			return next({
				path: to.path,
				replace: true,
				query: to.query
			});
		}

		request_block = true;

		request_block_timer = setTimeout(() => {
			request_block = false
		}, 500);

		return next();
	})
}