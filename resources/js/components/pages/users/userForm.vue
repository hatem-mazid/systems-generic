<template>
    <Skeleton v-if="isLoading" height="20rem" />
    <Card v-else>
        <template #title>
            <div class="flex justify-between items-center gap-2 flex-wrap">
                {{ userId ? $t("UserForm.Edit") : $t("UserForm.Create") }}
                <ResetPasswordDialog v-if="userId" :user-id="userId" />
            </div>
        </template>

        <template #content>
            <div class="grid grid-cols-12 gap-x-4 gap-y-5">
                <div class="md:col-span-6 col-span-12">
                    <label for="user-name" class="block mb-2">{{
                        $t("UserForm.Name")
                    }}</label>
                    <InputGroup>
                        <InputGroupAddon>
                            <i class="pi pi-user" />
                        </InputGroupAddon>
                        <InputText
                            id="user-name"
                            v-model="item.name"
                            type="text"
                            class="w-full"
                            fluid
                            :invalid="!!validation.name"
                            :placeholder="$t('UserForm.Name')"
                        />
                    </InputGroup>
                    <Message
                        v-if="validation.name"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ validation.name[0] }}
                    </Message>
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label for="user-email" class="block mb-2">{{
                        $t("UserForm.Email")
                    }}</label>
                    <InputGroup>
                        <InputGroupAddon>
                            <i class="pi pi-envelope" />
                        </InputGroupAddon>
                        <InputText
                            id="user-email"
                            v-model="item.email"
                            type="email"
                            class="w-full"
                            fluid
                            :invalid="!!validation.email"
                            :placeholder="$t('UserForm.Email')"
                        />
                    </InputGroup>
                    <Message
                        v-if="validation.email"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ validation.email[0] }}
                    </Message>
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label for="user-role" class="block mb-2">{{
                        $t("UserForm.Role")
                    }}</label>
                    <InputGroup class="w-full">
                        <InputGroupAddon>
                            <i class="pi pi-users" />
                        </InputGroupAddon>
                        <Select
                            id="user-role"
                            v-model="item.role"
                            input-id="user-role"
                            class="w-full flex-1 min-w-0"
                            fluid
                            filter
                            :options="roleOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="$t('UserForm.Role')"
                            :invalid="!!validation.role"
                        />
                    </InputGroup>
                    <Message
                        v-if="validation.role"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ validation.role[0] }}
                    </Message>
                </div>

                <div class="md:col-span-6 col-span-12">
                    <span class="block mb-2">{{ $t("UserForm.Status") }}</span>
                    <div class="flex items-center gap-3 min-h-[2.5rem]">
                        <ToggleSwitch
                            v-model="item.active"
                            input-id="user-status"
                        />
                        <label
                            for="user-status"
                            class="text-surface-700 dark:text-surface-200 cursor-pointer m-0"
                        >
                            {{
                                item.active
                                    ? $t("UserForm.Active")
                                    : $t("UserForm.Inactive")
                            }}
                        </label>
                    </div>
                    <Message
                        v-if="validation.status"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ validation.status[0] }}
                    </Message>
                </div>

                <template v-if="!userId">
                    <div class="md:col-span-6 col-span-12">
                        <label for="user-password" class="block mb-2">{{
                            $t("UserForm.Password")
                        }}</label>
                        <Password
                            id="user-password"
                            v-model="item.password"
                            class="w-full"
                            fluid
                            toggle-mask
                            :invalid="!!validation.password"
                            :placeholder="$t('UserForm.Password')"
                        />
                        <Message
                            v-if="validation.password"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ validation.password[0] }}
                        </Message>
                    </div>

                    <div class="md:col-span-6 col-span-12">
                        <label for="user-confirm-password" class="block mb-2">{{
                            $t("UserForm.ConfirmPassword")
                        }}</label>
                        <Password
                            id="user-confirm-password"
                            v-model="item.password_confirmation"
                            class="w-full"
                            fluid
                            toggle-mask
                            :invalid="!!validation.confirm_password"
                            :placeholder="$t('UserForm.ConfirmPassword')"
                        />
                        <Message
                            v-if="validation.confirm_password"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ validation.confirm_password[0] }}
                        </Message>
                    </div>
                </template>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-end mt-4 gap-2">
                <Button
                    v-if="userId"
                    type="button"
                    :label="$t('UserForm.SubmitUpdate')"
                    icon="pi pi-check"
                    :loading="submitLoading"
                    @click="onSubmit"
                />
                <Button
                    v-else
                    type="button"
                    :label="$t('UserForm.SubmitCreate')"
                    icon="pi pi-plus"
                    :loading="submitLoading"
                    @click="onSubmit"
                />
            </div>
        </template>
    </Card>
</template>

<script setup lang="ts">
import { isAxiosError } from "axios";
import { useToast } from "primevue/usetoast";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { usersService } from "../../../apis/services/users.apis";
import ResetPasswordDialog from "./ResetPasswordDialog.vue";
import type { User } from "../../../types/users";
import { UserRole } from "../../../types/users";

interface UserFormProps {
    userId?: string;
}
const toast = useToast();
const props = defineProps<UserFormProps>();
const router = useRouter();
const { t } = useI18n();

const isLoading = ref(!!props.userId);
const submitLoading = ref(false);

const item = ref<User & { password_confirmation?: string }>({
    name: "",
    email: "",
    role: UserRole.Waiter,
    active: true,
    password: "",
    password_confirmation: "",
});

const validation = ref<Record<string, string[]>>({});

const roleOptions = computed(() => [
    { label: t("UserForm.Roles.admin"), value: UserRole.ADMIN },
    { label: t("UserForm.Roles.accounting"), value: UserRole.Accountant },
    { label: t("UserForm.Roles.waiter"), value: UserRole.Waiter },
]);

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};
    try {
        if (props.userId) {
            const res = await usersService.updateUser(props.userId, item.value);
            const ok = (res?.status ?? 0) >= 200 && (res?.status ?? 0) < 300;
            if (ok) {
                toast.add({
                    severity: "success",
                    summary: t("UserForm.UpdatedSuccess"),
                    life: 3000,
                });
                router.push("/users");
            }
        } else {
            await usersService.createUser(item.value);
            toast.add({
                severity: "success",
                summary: t("UserForm.CreatedSuccess"),
                life: 3000,
            });
            router.push("/users");
        }
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            toast.add({
                severity: "error",
                summary: data?.message ?? t("UserForm.ValidationError"),
                life: 4000,
            });
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("UserForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onMounted(async () => {
    if (!props.userId) {
        return;
    }
    try {
        const { data } = await usersService.getUser(props.userId);
        item.value = {
            ...item.value,
            ...data,
            active: Boolean(data.active),
            role: data.role as UserRole,
        };
    } finally {
        isLoading.value = false;
    }
});
</script>
