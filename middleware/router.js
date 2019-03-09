export default function ({ store, route, redirect })
{
	if (route.path === '/' && store.state.user !== null)
		return redirect('/overview/')
}