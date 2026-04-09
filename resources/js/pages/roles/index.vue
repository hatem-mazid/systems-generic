<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("Roles") }}
            </h1>

            <Button
                v-if="canCreateRole"
                to="/roles/create"
                as="router-link"
                size="large"
                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                :label="$t('Add Role')"
            >
                <template #icon>
                    <AppIcon name="pi pi-plus" />
                </template>
            </Button>
        </div>

        <div class="mt-8 min-w-0">
            <DataTable
                :value="roles"
                :loading="isLoading"
                striped-rows
                table-style="min-width: 40rem"
            >
                <Column field="name" :header="$t('RoleForm.Name')" />
                <Column :header="$t('RoleForm.Permissions')">
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-1">
                            <Tag
                                v-for="permission in data.permissions"
                                :key="permission"
                                :value="permission"
                            />
                            <span
                                v-if="!data.permissions?.length"
                                class="text-surface-500"
                            >
                                -
                            </span>
                        </div>
                    </template>
                </Column>
                <Column :header="$t('OrdersList.ColumnActions')" style="width: 180px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                v-if="canEditRole"
                                as="router-link"
                                :to="`/roles/${data.id}`"
                                severity="secondary"
                                size="small"
                                :label="$t('Edit')"
                            />
                            <Button
                                v-if="canDeleteRole"
                                severity="danger"
                                size="small"
                                :label="$t('Delete')"
                                :loading="deletingId === data.id"
                                :disabled="deletingId !== null && deletingId !== data.id"
                                @click="confirmDelete(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Paginator
                class="pagination-touch mt-6 bg-transparent"
                :rows="paginator.per_page"
                :total-records="paginator.total"
                :first="(paginator.current_page - 1) * paginator.per_page"
                @page="onPageChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { rolesService } from "../../apis/services/roles/roles.apis";
import type { Role } from "../../apis/services/roles/roles.type";
import { useUserStore } from "../../stores/user";

const { t } = useI18n();
const confirm = useConfirm();
const toast = useToast();
const { hasPermission } = useUserStore();

const canCreateRole = hasPermission("roles create");
const canEditRole = hasPermission("roles edit");
const canDeleteRole = hasPermission("roles delete");

const isLoading = ref(true);
const deletingId = ref<string | null>(null);
const roles = ref<Role[]>([]);
const paginator = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
});

const fetchRoles = (page = 1, showFullLoading = true) => {
    if (showFullLoading) {
        isLoading.value = true;
    }

    rolesService
        .getRoles({ page, per_page: paginator.value.per_page })
        .then((response) => {
            const data = response.data;
            roles.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .finally(() => {
            if (showFullLoading) {
                isLoading.value = false;
            }
        });
};

const confirmDelete = (role: Role) => {
    confirm.require({
        message: t("RolesList.DeleteMessage", { name: role.name }),
        header: t("RolesList.DeleteTitle"),
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: t("Cancel"),
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: t("Delete"),
            severity: "danger",
        },
        accept: () => {
            const roleId = String(role.id);
            deletingId.value = roleId;
            rolesService
                .deleteRole(roleId)
                .then(() => {
                    toast.add({
                        severity: "success",
                        summary: t("RolesList.DeleteSuccess"),
                        life: 3000,
                    });
                    return fetchRoles(paginator.value.current_page, false);
                })
                .catch(() => {
                    toast.add({
                        severity: "error",
                        summary: t("RolesList.DeleteError"),
                        life: 4000,
                    });
                })
                .finally(() => {
                    deletingId.value = null;
                });
        },
    });
};

const onPageChange = (event: { page: number; rows: number }) => {
    paginator.value.per_page = event.rows;
    fetchRoles(event.page + 1);
};

onMounted(() => {
    fetchRoles();
});
</script>
