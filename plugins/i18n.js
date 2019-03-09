import Vue from 'vue'
import VueI18n from 'vue-i18n'
import ru from '~/locales/ru'

Vue.use(VueI18n)

export default ({app, store}) =>
{
	app.i18n = new VueI18n({
		locale: 'ru',
		fallbackLocale: 'ru',
		messages: {
			ru
		},
	})

	app.i18n.path = (link) =>
	{
		if (app.i18n.locale === app.i18n.fallbackLocale)
			return `/${link}`

		return `/${app.i18n.locale}/${link}`
	}
}