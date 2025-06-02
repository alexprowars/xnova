<x-dynamic-component
	:component="$getFieldWrapperView()"
	:field="$field"
>
	{{ $getChildComponentContainer() }}
</x-dynamic-component>