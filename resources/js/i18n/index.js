import { createI18n } from "vue-i18n";
import adminAr from "./admin/ar.json";
import adminEn from "./admin/en.json";

const messages = {
    ar: adminAr,
    en: adminEn,
};

const locale = document.documentElement.lang || "en";

const i18n = createI18n({
    legacy: false,
    locale: locale,
    fallbackLocale: "en",
    messages,
});

export default i18n;
