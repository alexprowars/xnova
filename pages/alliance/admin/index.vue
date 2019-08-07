<template>
	<div>
	<table class="table">
		<tr>
			<td class="c">Управление альянсом</td>
		</tr>
		<tr>
			<th><nuxt-link to="/alliance/admin/edit/rights/">Установить ранги</nuxt-link></th>
		</tr>
		<tr v-if="page['can_view_members']">
			<th><nuxt-link to="/alliance/admin/edit/members/">Члены альянса</nuxt-link></th>
		</tr>
		<tr>
			<th><nuxt-link to="/alliance/admin/edit/tag/">Изменить аббревиатуру альянса</nuxt-link></th>
		</tr>
		<tr>
			<th><nuxt-link to="/alliance/admin/edit/name/">Изменить название альянса</nuxt-link></th>
		</tr>
	</table>
	
	<router-form :action="'/alliance/admin/edit/ally/'">
		<input type="hidden" name="t" :value="page['t']">
		<table class="table">
			<tr>
				<td class="c" colspan="3">Редактировать текст</td>
			</tr>
			<tr>
				<th><nuxt-link to="/alliance/admin/edit/ally/t/1/">Внешний текст</nuxt-link></th>
				<th><nuxt-link to="/alliance/admin/edit/ally/t/2/">Внутренний текст</nuxt-link></th>
				<th><nuxt-link to="/alliance/admin/edit/ally/t/3/">Текст заявки</nuxt-link></th>
			</tr>
			<tr>
				<td class="c" colspan="3">Текст альянса</td>
			</tr>
			<tr>
				<th colspan="3" class="p-a-0">
					<text-editor :text="page['text']"></text-editor>
				</th>
			</tr>
			<tr>
				<th colspan="3">
					<input type="reset" value="Очистить"><input type="submit" value="Сохранить">
				</th>
			</tr>
		</table>
	</router-form>
	<div class="separator"></div>
	<router-form action="/alliance/admin/edit/ally/">
		<table class="table">
			<tr>
				<td class="c" colspan="2">Дополнительные настройки</td>
			</tr>
			<tr>
				<th width="200">Домашняя страница</th>
				<th><input type="text" name="web" :value="page['web']" style="width:98%;" title=""></th>
			</tr>
			<tr>
				<th>Логотип</th>
				<th>
					<input type="file" name="image" value="" style="width:98%;" title="">
					<template v-if="page['image'] !== ''">
						<img :src="page['image']" style="max-width: 98%;max-height: 400px;" alt="">
						<label>
							<input type="checkbox" name="delete_image" value="Y"> Удалить
						</label>
					</template>
				</th>
			</tr>
			<tr>
				<th>Ранг основателя</th>
				<th><input type="text" name="owner_range" :value="page['owner_range']" style="width:98%;" title=""></th>
			</tr>
			<tr>
				<th>Заявки</th>
				<th>
					<select style="width:98%;" name="request_notallow" title="" v-model="page['request_allow']">
						<option value="1">Закрытый альянс</option>
						<option value="0">Открытый альянс</option>
					</select>
				</th>
			</tr>
			<tr>
				<th colspan="2">
					<input type="submit" name="options" value="Сохранить">
				</th>
			</tr>
		</table>
	</router-form>
	
	<div class="separator"></div>
	<div class="row">
		<div class="col-6" v-html="page['Disolve_alliance']">
			{{ page['Disolve_alliance'] }}
		</div>
		<div class="col-6" v-html="page['Transfer_alliance']">
			{{ page['Transfer_alliance'] }}
		</div>
	</div>
	</div>
</template>

<script>
	export default {
		name: 'alliance-admin',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>