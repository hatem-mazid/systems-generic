<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("Users") }}
            </h1>

            <Button
                v-if="canCreateUser"
                to="/users/create"
                as="router-link"
                size="large"
                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                :label="$t('Add User')"
            >
                <template #icon>
                    <AppIcon name="pi pi-plus" />
                </template>
            </Button>
        </div>

        <div class="mt-8 min-w-0">
            <div
                class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4"
            >
                <Skeleton
                    v-if="isLoading"
                    v-for="n in paginator.per_page"
                    :key="'sk-' + n"
                    width="100%"
                    height="220px"
                    class="rounded-2xl"
                />

                <UserCard
                    v-else
                    v-for="user in users"
                    :key="user.id"
                    :user="user"
                    :deleting="deletingId === user.id"
                    :disable-delete="
                        deletingId !== null && deletingId !== user.id
                    "
                    :can-edit="canEditUser"
                    :can-delete="canDeleteUser"
                    @delete="confirmDelete"
                />
            </div>

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

<script setup>
import { Button } from "primevue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { usersService } from "../../apis/services/users/users.apis";
import UserCard from "../../components/pages/users/UserCard.vue";
import { useUserStore } from "../../stores/user";

const { t } = useI18n();
const confirm = useConfirm();
const toast = useToast();
const { hasPermission } = useUserStore();
const canCreateUser = hasPermission("users create");
const canEditUser = hasPermission("users edit");
const canDeleteUser = hasPermission("users delete");

const isLoading = ref(true);
const deletingId = ref(null);
const users = ref([]);
const paginator = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
});

const fetchUsers = (page = 1, showFullLoading = true) => {
    if (showFullLoading) {
        isLoading.value = true;
    }
    usersService
        .getUsers({ page, per_page: paginator.value.per_page })
        .then((response) => {
            const data = response.data;
            users.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .catch((error) => {
            console.error("Error fetching users:", error);
        })
        .finally(() => {
            if (showFullLoading) {
                isLoading.value = false;
            }
        });
};

const confirmDelete = (user) => {
    confirm.require({
        message: t("UsersList.DeleteMessage", { name: user.name }),
        header: t("UsersList.DeleteTitle"),
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
            deletingId.value = user.id;
            usersService
                .deleteUser(String(user.id))
                .then(() => {
                    toast.add({
                        severity: "success",
                        summary: t("UsersList.DeleteSuccess"),
                        life: 3000,
                    });
                    return fetchUsers(paginator.value.current_page, false);
                })
                .catch(() => {
                    toast.add({
                        severity: "error",
                        summary: t("UsersList.DeleteError"),
                        life: 4000,
                    });
                })
                .finally(() => {
                    deletingId.value = null;
                });
        },
    });
};

const onPageChange = (event) => {
    paginator.value.per_page = event.rows;
    fetchUsers(event.page + 1);
};

onMounted(() => {
    fetchUsers();
});
</script>
