<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("Sidebar.UnitsManagement") }}
            </h1>

            <Button
                v-if="isAdmin"
                to="/unit-groups-setup/create"
                as="router-link"
                size="large"
                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                :label="$t('Add Unit Group')"
                icon="pi pi-plus"
            />
        </div>

        <div class="mt-8 min-w-0">
            <Skeleton
                v-if="isLoading"
                v-for="n in 4"
                :key="'sk-' + n"
                width="100%"
                height="220px"
                class="rounded-2xl"
            />

            <template v-else-if="unitGroups.length">
                <Tabs
                    v-model:value="activeTab"
                    class="units-management-tabs touch-manipulation"
                >
                    <TabList class="flex flex-wrap gap-2">
                        <Tab
                            v-for="g in unitGroups"
                            :key="g.id"
                            :value="String(g.id)"
                            class="max-w-[min(100%,16rem)]"
                        >
                            <span class="block truncate" :title="g.name">{{
                                g.name || "—"
                            }}</span>
                        </Tab>
                    </TabList>
                    <TabPanels
                        class="mt-4 rounded-xl border border-surface-200 bg-surface-0 p-4 sm:p-5 dark:border-surface-700 dark:bg-surface-900"
                    >
                        <TabPanel
                            v-for="g in unitGroups"
                            :key="'p-' + g.id"
                            :value="String(g.id)"
                        >
                            <UnitGroupPanelContent
                                :unit-group="g"
                                management-mode
                                hide-group-heading
                                @deleted="handleUnitGroupDeleted"
                            />
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </template>

            <div
                v-else
                class="rounded-xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-600 dark:text-surface-300"
            >
                {{ $t("UnitGroupsList.Empty") }}
            </div>
        </div>

        <Paginator
            class="pagination-touch mt-6 bg-transparent"
            :rows="paginator.per_page"
            :total-records="paginator.total"
            :first="(paginator.current_page - 1) * paginator.per_page"
            @page="onPageChange"
        />
    </div>
</template>

<script setup>
import { Button } from "primevue";
import Tab from "primevue/tab";
import TabList from "primevue/tablist";
import TabPanel from "primevue/tabpanel";
import TabPanels from "primevue/tabpanels";
import Tabs from "primevue/tabs";
import { computed, onMounted, ref, watch } from "vue";
import { unitGroupsService } from "../../apis/services/unitGroups/unitGroups.apis";
import { UserRole } from "../../apis/services/users/users.type";
import UnitGroupPanelContent from "../../components/pages/unitGroups/UnitGroupPanelContent.vue";
import { useUserStore } from "../../stores/user";

const userStore = useUserStore();

const isAdmin = computed(() => {
    const r = userStore.info?.role;
    return r === UserRole.ADMIN || r === "admin";
});

const isLoading = ref(true);
const unitGroups = ref([]);
const activeTab = ref("");
const paginator = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
});

watch(
    unitGroups,
    (groups) => {
        if (!groups.length) {
            activeTab.value = "";
            return;
        }
        const ids = groups.map((g) => String(g.id));
        if (!activeTab.value || !ids.includes(activeTab.value)) {
            activeTab.value = ids[0];
        }
    },
    { immediate: true }
);

const fetchUnitGroups = (page = 1, showFullLoading = true) => {
    if (showFullLoading) {
        isLoading.value = true;
    }

    unitGroupsService
        .getUnitGroups({ page, per_page: paginator.value.per_page })
        .then((response) => {
            const data = response.data;
            unitGroups.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .catch((error) => {
            console.error("Error fetching unit groups:", error);
        })
        .finally(() => {
            if (showFullLoading) {
                isLoading.value = false;
            }
        });
};

const handleUnitGroupDeleted = () => {
    fetchUnitGroups(paginator.value.current_page, false);
};

const onPageChange = (event) => {
    paginator.value.per_page = event.rows;
    fetchUnitGroups(event.page + 1);
};

onMounted(() => {
    fetchUnitGroups();
});
</script>

<style scoped>
.units-management-tabs :deep(.p-tablist-tab-list) {
    flex-wrap: wrap;
    gap: 0.5rem;
}

.units-management-tabs :deep(.p-tab) {
    min-height: 3rem;
    padding-inline: 1rem;
}
</style>
