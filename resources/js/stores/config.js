import { defineStore } from "pinia";
import { ref } from "vue";

const VIEW_MODE_STORAGE_KEYS = {
    units: "config.units.viewMode",
    unitGroups: "config.unitGroups.viewMode",
    products: "config.products.viewMode",
    categories: "config.categories.viewMode",
};
const DEFAULT_VIEW_MODE = "comfortable";
const ALLOWED_VIEW_MODES = ["comfortable", "compact"];

export const useConfigStore = defineStore("config", () => {
    const unitsViewMode = ref(DEFAULT_VIEW_MODE);
    const unitGroupsViewMode = ref(DEFAULT_VIEW_MODE);
    const productsViewMode = ref(DEFAULT_VIEW_MODE);
    const categoriesViewMode = ref(DEFAULT_VIEW_MODE);

    const normalizeViewMode = (value) =>
        ALLOWED_VIEW_MODES.includes(value) ? value : DEFAULT_VIEW_MODE;

    const loadViewMode = (storageKey, targetRef) => {
        if (typeof window === "undefined") {
            return;
        }
        const stored = window.localStorage.getItem(storageKey);
        targetRef.value = normalizeViewMode(stored);
    };

    const setViewMode = (storageKey, targetRef, mode) => {
        const normalized = normalizeViewMode(mode);
        targetRef.value = normalized;
        if (typeof window !== "undefined") {
            window.localStorage.setItem(storageKey, normalized);
        }
    };

    const loadUnitsViewMode = () =>
        loadViewMode(VIEW_MODE_STORAGE_KEYS.units, unitsViewMode);
    const setUnitsViewMode = (mode) =>
        setViewMode(VIEW_MODE_STORAGE_KEYS.units, unitsViewMode, mode);

    const loadUnitGroupsViewMode = () =>
        loadViewMode(VIEW_MODE_STORAGE_KEYS.unitGroups, unitGroupsViewMode);
    const setUnitGroupsViewMode = (mode) =>
        setViewMode(VIEW_MODE_STORAGE_KEYS.unitGroups, unitGroupsViewMode, mode);

    const loadProductsViewMode = () =>
        loadViewMode(VIEW_MODE_STORAGE_KEYS.products, productsViewMode);
    const setProductsViewMode = (mode) =>
        setViewMode(VIEW_MODE_STORAGE_KEYS.products, productsViewMode, mode);

    const loadCategoriesViewMode = () =>
        loadViewMode(VIEW_MODE_STORAGE_KEYS.categories, categoriesViewMode);
    const setCategoriesViewMode = (mode) =>
        setViewMode(VIEW_MODE_STORAGE_KEYS.categories, categoriesViewMode, mode);

    return {
        unitsViewMode,
        unitGroupsViewMode,
        productsViewMode,
        categoriesViewMode,
        loadUnitsViewMode,
        setUnitsViewMode,
        loadUnitGroupsViewMode,
        setUnitGroupsViewMode,
        loadProductsViewMode,
        setProductsViewMode,
        loadCategoriesViewMode,
        setCategoriesViewMode,
    };
});
