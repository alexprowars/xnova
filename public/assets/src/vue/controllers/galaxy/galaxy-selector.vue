<template>
	<router-form ref="form" action="/galaxy/r/1/" class="page-galaxy-select">
		<input type="hidden" name="direction" v-model="direction">

		<div class="row">
			<div class="col-12 d-sm-none">
				<galaxy-selector-shortcut :items="shortcuts"></galaxy-selector-shortcut>
			</div>
			<div class="separator d-sm-none"></div>
			<div class="col-sm-4 col-6">
				<table style="margin: 0 auto">
					<tr>
						<td class="c" colspan="3">
							Галактика
						</td>
					</tr>
					<tr>
						<th>
							<input value="&lt;-" type="button" @click.prevent="direction = 'galaxyLeft'">
						</th>
						<th>
							<input name="galaxy" :value="$parent.page.galaxy" maxlength="3" tabindex="1" min="1" type="number">
						</th>
						<th>
							<input value="-&gt;" type="button" v-on:click.prevent="direction = 'galaxyRight'">
						</th>
					</tr>
				</table>
			</div>
			<div class="col-sm-4 d-none d-sm-block">
				<galaxy-selector-shortcut :items="shortcuts"></galaxy-selector-shortcut>
			</div>
			<div class="col-sm-4 col-6">
				<table style="margin: 0 auto">
					<tr>
						<td class="c" colspan="3">
							Солнечная система
						</td>
					</tr>
					<tr>
						<th>
							<input value="&lt;-" type="button" v-on:click.prevent="direction = 'systemLeft'">
						</th>
						<th>
							<input name="system" :value="$parent.page.system" maxlength="3" tabindex="2" min="1" type="number">
						</th>
						<th>
							<input value="-&gt;" type="button" v-on:click.prevent="direction = 'systemRight'">
						</th>
					</tr>
				</table>
			</div>
		</div>
	</router-form>
</template>

<script>
	import GalaxySelectorShortcut from './galaxy-selector-shortcut.vue'

	export default {
		name: "galaxy-selector",
		props: [
			"shortcuts"
		],
		data: function() {
			return {
				direction: ''
			}
		},
		watch: {
			direction (val)
			{
				if (val !== '')
				{
					this.$nextTick(() => {
						this.$refs['form'].send();
						setTimeout(() => this.direction = '', 100);
					});
				}
			}
		},
		components: {
			GalaxySelectorShortcut
		}
	}
</script>