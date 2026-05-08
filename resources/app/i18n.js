import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import ru from './locales/ru.json';

const messages = {
	en,
	ru
};

const i18n = createI18n({
	legacy: false,
	locale: 'en',
	fallbackLocale: 'en',
	warnHtmlMessage: false,
	messages,
});

export const setLocale = (locale) => {
    if (messages[locale]) {
        i18n.global.locale.value = locale;
    } else {
        console.error(`Locale ${locale} not found.`);
    }
};

export default i18n;