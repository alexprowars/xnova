@foreach ($main_menu as $item)
	@if ($item['code'] == null)
		<x-backpack::menu-separator :title="$item['title']" />
	@elseif(empty($item['childrens']))
		<x-backpack::menu-item :title="$item['title']" :icon="$item['icon']" :link="$item['url']" />
	@else
		<x-backpack::menu-dropdown :title="$item['title']" :icon="$item['icon']">
			@foreach ($item['childrens'] as $child)
		   		<x-backpack::menu-dropdown-item :title="$child['title']" :icon="$child['icon'] ?? ''" :link="$child['url']" />
			@endforeach
		</x-backpack::menu-dropdown>
	@endif
@endforeach