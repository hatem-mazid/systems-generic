import { createI18n } from 'vue-i18n'

const messages = {
    en: {
        start: 'Start',
        units: 'Units',
    },
    ar: {
        start: 'ابدأ',
        units: 'الوحدات',
    }
}

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages,
})

export default i18n
