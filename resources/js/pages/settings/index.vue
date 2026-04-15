<template>
    <div class="touch-manipulation p-4 text-surface-800 dark:text-surface-100 sm:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold sm:text-3xl">
                {{ $t("SettingsPage.Title") }}
            </h1>
            <p class="mt-1 text-surface-600 dark:text-surface-400">
                {{ $t("SettingsPage.Subtitle") }}
            </p>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-surface-200/80 bg-surface-0 shadow-sm dark:border-surface-700 dark:bg-surface-900"
        >
            <div class="flex min-h-[22rem] flex-col md:flex-row">
                <aside
                    class="w-full border-b border-surface-200 bg-surface-50 p-3 dark:border-surface-700 dark:bg-surface-950/30 md:w-56 md:border-b-0 md:border-r"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="mb-2 w-full rounded-xl px-4 py-3 text-start text-sm font-medium transition last:mb-0"
                        :class="
                            activeTab === tab.key
                                ? 'bg-primary/10 text-primary'
                                : 'text-surface-700 hover:bg-surface-200/70 dark:text-surface-200 dark:hover:bg-surface-800'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </aside>

                <section class="flex-1 p-5 sm:p-6">
                    <div
                        v-if="activeTab === 'business-hours'"
                        class="max-w-xl space-y-5"
                    >
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label
                                    for="opening-time"
                                    class="mb-2 block text-sm font-medium text-surface-700 dark:text-surface-200"
                                >
                                    {{ $t("SettingsPage.OpeningTime") }}
                                </label>
                                <DatePicker
                                    id="opening-time"
                                    v-model="openingTimeValue"
                                    time-only
                                    hour-format="24"
                                    show-icon
                                    fluid
                                    size="large"
                                    :manual-input="false"
                                />
                            </div>
                            <div>
                                <label
                                    for="closing-time"
                                    class="mb-2 block text-sm font-medium text-surface-700 dark:text-surface-200"
                                >
                                    {{ $t("SettingsPage.ClosingTime") }}
                                </label>
                                <DatePicker
                                    id="closing-time"
                                    v-model="closingTimeValue"
                                    time-only
                                    hour-format="24"
                                    show-icon
                                    fluid
                                    size="large"
                                    :manual-input="false"
                                />
                            </div>
                        </div>

                        <div class="rounded-xl bg-surface-100 px-4 py-3 text-sm dark:bg-surface-800/70">
                            {{ $t("SettingsPage.Hint") }}
                        </div>

                        <Button
                            type="button"
                            :label="$t('SettingsPage.Save')"
                            :loading="saving"
                            :disabled="saving || !form.opening_time || !form.closing_time"
                            @click="saveConfig"
                        />
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { configService } from "@/apis/services/config/config.apis";
import Button from "primevue/button";
import DatePicker from "primevue/datepicker";
import { useToast } from "primevue/usetoast";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const toast = useToast();

const tabs = computed(() => [
    {
        key: "business-hours",
        label: t("SettingsPage.BusinessHoursTab"),
    },
]);

const activeTab = ref("business-hours");
const saving = ref(false);
const form = ref({
    opening_time: "08:00",
    closing_time: "23:59",
});

const openingTimeValue = computed({
    get: () => parseTimeString(form.value.opening_time),
    set: (value) => {
        form.value.opening_time = formatTimeValue(value);
    },
});

const closingTimeValue = computed({
    get: () => parseTimeString(form.value.closing_time),
    set: (value) => {
        form.value.closing_time = formatTimeValue(value);
    },
});

async function loadConfig() {
    try {
        const { data } = await configService.getConfig();
        form.value.opening_time = data.opening_time || "08:00";
        form.value.closing_time = data.closing_time || "23:59";
    } catch (error) {
        console.error(error);
        toast.add({
            severity: "error",
            summary: t("SettingsPage.LoadError"),
            life: 5000,
        });
    }
}

async function saveConfig() {
    if (!form.value.opening_time || !form.value.closing_time) {
        return;
    }
    saving.value = true;
    try {
        const { data } = await configService.updateConfig({
            opening_time: form.value.opening_time,
            closing_time: form.value.closing_time,
        });
        form.value.opening_time = data.opening_time;
        form.value.closing_time = data.closing_time;
        toast.add({
            severity: "success",
            summary: t("SettingsPage.SaveSuccess"),
            life: 3500,
        });
    } catch (error) {
        console.error(error);
        toast.add({
            severity: "error",
            summary: t("SettingsPage.SaveError"),
            life: 5000,
        });
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadConfig();
});

function parseTimeString(value) {
    if (!value || !/^\d{2}:\d{2}$/.test(value)) {
        return null;
    }

    const [hours, minutes] = value.split(":").map(Number);
    const time = new Date();
    time.setHours(hours, minutes, 0, 0);
    return time;
}

function formatTimeValue(value) {
    if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
        return "";
    }

    const hours = String(value.getHours()).padStart(2, "0");
    const minutes = String(value.getMinutes()).padStart(2, "0");
    return `${hours}:${minutes}`;
}
</script>
