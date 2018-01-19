<template>
	<form v-bind:action="$root.getUrl('galaxy/r/1/')" method="post" class="galaxy-select">
		<input type="hidden" class="auto" value="dr">

		<div class="row">
			<div class="col-xs-12 hidden-sm-up">
				<game-page-galaxy-selector-shortcut v-bind:items="shortcuts"></game-page-galaxy-selector-shortcut>
			</div>
			<div class="separator hidden-sm-up"></div>
			<div class="col-sm-4 col-xs-6">
				<table style="margin: 0 auto">
					<tr>
						<td class="c" colspan="3">
							Галактика
						</td>
					</tr>
					<tr>
						<th>
							<input name="galaxyLeft" value="&lt;-" type="button" v-on:click.prevent="goTo">
						</th>
						<th>
							<input name="galaxy" v-bind:value="$parent.page.galaxy" size="5" maxlength="3" tabindex="1" type="text">
						</th>
						<th>
							<input name="galaxyRight" value="-&gt;" type="button" v-on:click.prevent="goTo">
						</th>
					</tr>
				</table>
			</div>
			<div class="col-sm-4 hidden-xs-down">
				<game-page-galaxy-selector-shortcut v-bind:items="shortcuts"></game-page-galaxy-selector-shortcut>
			</div>
			<div class="col-sm-4 col-xs-6">
				<table style="margin: 0 auto">
					<tr>
						<td class="c" colspan="3">
							Солнечная система
						</td>
					</tr>
					<tr>
						<th>
							<input name="systemLeft" value="&lt;-" type="button" v-on:click.prevent="goTo">
						</th>
						<th>
							<input name="system" v-bind:value="$parent.page.system" size="5" maxlength="3" tabindex="2" type="text">
						</th>
						<th>
							<input name="systemRight" value="-&gt;" type="button" v-on:click.prevent="goTo">
						</th>
					</tr>
				</table>
			</div>
		</div>
	</form>
</template>

<script>
	export default {
		name: "galaxy-selector",
		props: [
			"shortcuts"
		],
		data: function() {
			return {}
		},
		components: {
			'game-page-galaxy-selector-shortcut': require('./galaxy-selector-shortcut.vue'),
		},
		methods: {
			goTo: function ($event)
			{
				$('form.galaxy-select').find('.auto').attr('name', $($event.target).attr('name'));
				$('form.galaxy-select').submit();
			}
		}
	}
</script>