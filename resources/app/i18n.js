import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import ru from './locales/ru.json';

const messages = {
	en,
	ru
};

export const resolveLocale = (locale) => Object.hasOwn(messages, locale) ? locale : 'en';

export const createLocalization = (locale) => createI18n({
	legacy: false,
	locale: resolveLocale(locale),
	fallbackLocale: 'en',
	warnHtmlMessage: false,
	messages,
});

const i18n = createLocalization();

export const setLocale = (locale) => {
	i18n.global.locale.value = resolveLocale(locale);
};

export default i18n;