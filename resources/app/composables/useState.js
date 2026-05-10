import { usePage } from '@inertiajs/vue3';

export default function useState () {
	const page = usePage();

	return page.props.state || {};
}
