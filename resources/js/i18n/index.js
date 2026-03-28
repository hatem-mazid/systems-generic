import { createI18n } from 'vue-i18n'

const messages = {
    en: {
        start: 'Start',
        units: 'Units',
        Users: 'Users',
    },
    ar: {
        start: 'ابدأ',
        units: 'الوحدات',
        Users: 'المستخدمين',
    }
}

const locale = document.documentElement.lang || 'en';

const i18n = createI18n({
    legacy: false,
    locale: locale,
    fallbackLocale: 'en',
    messages,
})

export default i18n
