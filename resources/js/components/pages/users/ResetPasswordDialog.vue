<template>
    <div class="shrink-0">
        <Button
            type="button"
            :label="$t('UserForm.ResetPassword')"
            icon="pi pi-key"
            severity="info"
            outlined
            size="small"
            @click="open"
        />

        <Dialog
            v-model:visible="visible"
            modal
            :header="$t('UserForm.ResetPasswordTitle')"
            :style="{ width: 'min(100%, 28rem)' }"
            :breakpoints="{ '575px': '90vw' }"
            :draggable="false"
            @hide="onHide"
        >
            <p class="mb-4 text-sm text-surface-600 dark:text-surface-400">
                {{ $t("UserForm.ResetPasswordHint") }}
            </p>

            <div class="flex flex-col gap-4">
                <div>
                    <label for="reset-password" class="mb-2 block">{{
                        $t("UserForm.Password")
                    }}</label>
                    <Password
                        id="reset-password"
                        v-model="password"
                        class="w-full"
                        fluid
                        toggle-mask
                        autocomplete="new-password"
                        :invalid="!!validation.password"
                        :placeholder="$t('UserForm.Password')"
                    />
                    <Message
                        v-if="validation.password"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.password[0] }}
                    </Message>
                </div>
                <div>
                    <label for="reset-password-confirm" class="mb-2 block">{{
                        $t("UserForm.ConfirmPassword")
                    }}</label>
                    <Password
                        id="reset-password-confirm"
                        v-model="passwordConfirmation"
                        class="w-full"
                        fluid
                        toggle-mask
                        autocomplete="new-password"
                        :invalid="!!validation.password_confirmation"
                        :placeholder="$t('UserForm.ConfirmPassword')"
                    />
                    <Message
                        v-if="validation.password_confirmation"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.password_confirmation[0] }}
                    </Message>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        :label="$t('Cancel')"
                        severity="secondary"
                        text
                        @click="visible = false"
                    />
                    <Button
                        type="button"
                        :label="$t('UserForm.ResetPasswordSubmit')"
                        icon="pi pi-check"
                        :loading="submitting"
                        @click="submit"
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { isAxiosError } from "axios";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { usersService } from "../../../apis/services/users/users.apis";

const props = defineProps<{
    userId: string;
}>();

const toast = useToast();
const { t } = useI18n();

const visible = ref(false);
const submitting = ref(false);
const password = ref("");
const passwordConfirmation = ref("");
const validation = ref<Record<string, string[]>>({});

function resetFields() {
    password.value = "";
    passwordConfirmation.value = "";
    validation.value = {};
}

function open() {
    resetFields();
    visible.value = true;
}

function onHide() {
    resetFields();
}

async function submit() {
    submitting.value = true;
    validation.value = {};
    try {
        const res = await usersService.resetPassword(props.userId, {
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        const ok = (res?.status ?? 0) >= 200 && (res?.status ?? 0) < 300;
        if (ok) {
            toast.add({
                severity: "success",
                summary: t("UserForm.ResetPasswordSuccess"),
                life: 3000,
            });
            visible.value = false;
            resetFields();
        }
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            if (data?.message) {
                toast.add({
                    severity: "error",
                    summary: data.message,
                    life: 4000,
                });
            }
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("UserForm.ResetPasswordError"),
                life: 4000,
            });
        }
    } finally {
        submitting.value = false;
    }
}
</script>
