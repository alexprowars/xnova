<template>
	<form ref="form" action="" class="page-galaxy-select" @submit.prevent="send">
		<input type="hidden" name="direction" v-model="direction">

		<div class="row">
			<div class="col-12 d-sm-none">
				<GalaxySelectorShortcut :items="shortcuts"></GalaxySelectorShortcut>
			</div>
			<div class="separator d-sm-none"></div>
			<div class="col-sm-4 col-6">
				<table style="margin: 0 auto">
					<tbody>
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
								<input name="galaxy" v-model.number="inputGalaxy" maxlength="3" tabindex="1" min="1" type="number">
							</th>
							<th>
								<input value="-&gt;" type="button" @click.prevent="direction = 'galaxyRight'">
							</th>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-sm-4 d-none d-sm-block">
				<GalaxySelectorShortcut :items="shortcuts"></GalaxySelectorShortcut>
			</div>
			<div class="col-sm-4 col-6">
				<table style="margin: 0 auto">
					<tbody>
						<tr>
							<td class="c" colspan="3">
								Солнечная система
							</td>
						</tr>
						<tr>
							<th>
								<input value="&lt;-" type="button" @click.prevent="direction = 'systemLeft'">
							</th>
							<th>
								<input name="system" v-model.number="inputSystem" maxlength="3" tabindex="2" min="1" type="number">
							</th>
							<th>
								<input value="-&gt;" type="button" @click.prevent="direction = 'systemRight'">
							</th>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</form>
</template>

<script>
	import GalaxySelectorShortcut from './selector-shortcut.vue'

	export default {
		name: "galaxy-selector",
		props: {
			galaxy: {
				type: Number,
				default: 1
			},
			system: {
				type: Number,
				default: 1
			},
			shortcuts: {
				type: Array,
				default: () => {
					return []
				}
			}
		},
		data () {
			return {
				direction: '',
				inputGalaxy: this.galaxy,
				inputSystem: this.system,
			}
		},
		watch: {
			galaxy (val) {
				this.inputGalaxy = val
			},
			system (val) {
				this.inputSystem = val
			},
			direction (val)
			{
				if (val !== '')
					this.send()
			}
		},
		components: {
			GalaxySelectorShortcut
		},
		methods: {
			send () 
			{
				this.$post('/galaxy/', {
					galaxy: this.inputGalaxy,
					system: this.inputSystem,
					direction: this.direction
				})
				.then((result) => {
					this.direction = ''
					this.$store.commit('PAGE_LOAD', result)
				})
			}
		}
	}
</script>