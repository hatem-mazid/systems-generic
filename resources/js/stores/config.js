import { defineStore } from "pinia";
import { ref } from "vue";

const VIEW_MODE_STORAGE_KEY = "config.units.viewMode";
const DEFAULT_UNITS_VIEW_MODE = "comfortable";
const ALLOWED_UNITS_VIEW_MODES = ["comfortable", "compact"];

export const useConfigStore = defineStore("config", () => {
    const unitsViewMode = ref(DEFAULT_UNITS_VIEW_MODE);

    const normalizeUnitsViewMode = (value) =>
        ALLOWED_UNITS_VIEW_MODES.includes(value)
            ? value
            : DEFAULT_UNITS_VIEW_MODE;

    const loadUnitsViewMode = () => {
        if (typeof window === "undefined") {
            return;
        }
        const stored = window.localStorage.getItem(VIEW_MODE_STORAGE_KEY);
        unitsViewMode.value = normalizeUnitsViewMode(stored);
    };

    const setUnitsViewMode = (mode) => {
        const normalized = normalizeUnitsViewMode(mode);
        unitsViewMode.value = normalized;
        if (typeof window !== "undefined") {
            window.localStorage.setItem(VIEW_MODE_STORAGE_KEY, normalized);
        }
    };

    return {
        unitsViewMode,
        loadUnitsViewMode,
        setUnitsViewMode,
    };
});
