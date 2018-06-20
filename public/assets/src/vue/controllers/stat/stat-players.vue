<template>
	<div class="block page-stat-players">
		<div class="content border-0">
			<div class="table middle">
				<div class="row">
					<div class="c col-sm-1 col-2 middle">Место</div>
					<div class="c col-sm-1 d-none d-sm-block middle">+/-</div>
					<div class="c col-sm-4 col-5 middle">Игрок</div>
					<div class="c col-sm-1 col-2 middle">&nbsp;</div>
					<div class="c col-sm-3 d-none d-sm-block middle">Альянс</div>
					<div class="c col-sm-2 col-3 middle">Очки</div>
				</div>
				<div v-for="item in items" class="row page-stat-players-row">
					<div class="th col-sm-1 col-2">
						{{ item['position'] }}
						<div class="d-sm-none">
							<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
							<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
							<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
						</div>
					</div>
					<div class="th col-sm-1 d-none d-sm-block">
						<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
						<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
						<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
					</div>
					<div class="th col-sm-4 col-5">
						<a :href="$root.getUrl('players/'+item['id']+'/')" class="window popup-user">
							<span :class="{neutral: item['name_marked']}">{{ item['name'] }}</span>
						</a>
						<div v-if="item['alliance']" class="d-sm-none">
							<a :class="{neutral: item['alliance']['marked']}" :href="$root.getUrl('alliance/info/'+item['alliance']['id']+'/')">
								{{ item['alliance']['name'] }}
							</a>
						</div>
						<div v-else class="d-sm-none">
							&nbsp;
						</div>
					</div>
					<div class="th col-sm-1 col-2">
						<img v-if="item['race']" :src="$root.getUrl('assets/images/skin/race'+item['race']+'.gif')" width="16" height="16" style="margin-right:7px;">

						<a v-if="$store.state.user" @click="$root.openPopup(item['name']+': отправить сообщение', $root.getUrl('messages/write/'+item['id']+'/'), 680)" title="Сообщение">
							<span class="sprite skin_m"></span>
						</a>
					</div>
					<div class="th col-sm-3 d-none d-sm-block row-alliance">
						<a v-if="item['alliance']" :class="{neutral: item['alliance']['marked']}" :href="$root.getUrl('alliance/info/'+item['alliance']['id']+'/')">
							{{ item['alliance']['name'] }}
						</a>
						<div v-else>
							&nbsp;
						</div>
					</div>
					<div class="th col-sm-2 col-3 middle">
						<a :href="$root.getUrl('players/stat/'+item['id']+'/')">
							{{ item['points']|number }}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "stat-players",
		props: {
			items: Array
		}
	}
</script>