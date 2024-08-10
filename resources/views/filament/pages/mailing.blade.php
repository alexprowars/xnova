<x-filament-panels::page>
	<x-filament::card>
		<x-filament-panels::form>
			{{ $this->form }}

			<x-filament-panels::form.actions
				:actions="$this->getCachedFormActions()"
				:full-width="$this->hasFullWidthFormActions()"
			/>
		</x-filament-panels::form>
	</x-filament::card>
</x-filament-panels::page>