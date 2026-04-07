<template>
    <Skeleton v-if="isLoading" height="28rem" />
    <Card v-else>
        <template #title>
            {{
                productId ? $t("ProductForm.Edit") : $t("ProductForm.Create")
            }}
        </template>

        <template #content>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 space-y-6">
                    <Tabs
                        v-model:value="activeLocale"
                        class="product-locale-tabs"
                    >
                        <TabList>
                            <Tab value="en">{{
                                $t("ProductForm.LocaleEnglish")
                            }}</Tab>
                            <Tab value="ar" dir="rtl">
                                {{ $t("ProductForm.LocaleArabic") }}
                            </Tab>
                        </TabList>
                        <TabPanels class="pt-4">
                            <TabPanel value="en">
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            for="product-name-en"
                                            class="mb-2 block font-medium"
                                            >{{ $t("ProductForm.Name") }}</label
                                        >
                                        <InputText
                                            id="product-name-en"
                                            v-model="form.name"
                                            type="text"
                                            class="w-full"
                                            fluid
                                            :invalid="!!validation.name"
                                            :placeholder="$t('ProductForm.Name')"
                                        />
                                        <Message
                                            v-if="validation.name"
                                            severity="error"
                                            size="small"
                                            variant="simple"
                                            class="mt-1"
                                        >
                                            {{ validation.name[0] }}
                                        </Message>
                                    </div>
                                    <div>
                                        <label
                                            for="product-desc-en"
                                            class="mb-2 block font-medium"
                                            >{{
                                                $t("ProductForm.Description")
                                            }}</label
                                        >
                                        <Textarea
                                            id="product-desc-en"
                                            v-model="form.description"
                                            class="w-full min-h-[7rem]"
                                            fluid
                                            auto-resize
                                            :invalid="
                                                !!validation.description
                                            "
                                            :placeholder="
                                                $t('ProductForm.Description')
                                            "
                                        />
                                        <Message
                                            v-if="validation.description"
                                            severity="error"
                                            size="small"
                                            variant="simple"
                                            class="mt-1"
                                        >
                                            {{ validation.description[0] }}
                                        </Message>
                                    </div>
                                </div>
                            </TabPanel>
                            <TabPanel value="ar">
                                <div class="space-y-5" dir="rtl">
                                    <div>
                                        <label
                                            for="product-name-ar"
                                            class="mb-2 block font-medium"
                                            >{{ $t("ProductForm.Name") }}</label
                                        >
                                        <InputText
                                            id="product-name-ar"
                                            v-model="form.arName"
                                            type="text"
                                            class="w-full"
                                            fluid
                                            :invalid="
                                                !!validation[
                                                    'translations.ar.name'
                                                ]
                                            "
                                            :placeholder="$t('ProductForm.Name')"
                                        />
                                        <Message
                                            v-if="
                                                validation[
                                                    'translations.ar.name'
                                                ]
                                            "
                                            severity="error"
                                            size="small"
                                            variant="simple"
                                            class="mt-1"
                                        >
                                            {{
                                                validation[
                                                    "translations.ar.name"
                                                ][0]
                                            }}
                                        </Message>
                                    </div>
                                    <div>
                                        <label
                                            for="product-desc-ar"
                                            class="mb-2 block font-medium"
                                            >{{
                                                $t("ProductForm.Description")
                                            }}</label
                                        >
                                        <Textarea
                                            id="product-desc-ar"
                                            v-model="form.arDescription"
                                            class="w-full min-h-[7rem]"
                                            fluid
                                            auto-resize
                                            :invalid="
                                                !!validation[
                                                    'translations.ar.description'
                                                ]
                                            "
                                            :placeholder="
                                                $t('ProductForm.Description')
                                            "
                                        />
                                        <Message
                                            v-if="
                                                validation[
                                                    'translations.ar.description'
                                                ]
                                            "
                                            severity="error"
                                            size="small"
                                            variant="simple"
                                            class="mt-1"
                                        >
                                            {{
                                                validation[
                                                    "translations.ar.description"
                                                ][0]
                                            }}
                                        </Message>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>

                    <Divider />

                    <div
                        class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:items-end"
                    >
                        <div>
                            <label
                                for="product-type"
                                class="mb-2 block font-medium"
                                >{{ $t("ProductForm.Type") }}</label
                            >
                            <Select
                                id="product-type"
                                v-model="form.type"
                                :options="typeOptions"
                                option-label="label"
                                option-value="value"
                                class="w-full"
                                fluid
                                :invalid="!!validation.type"
                                :placeholder="$t('ProductForm.Type')"
                            />
                            <Message
                                v-if="validation.type"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.type[0] }}
                            </Message>
                        </div>
                        <div>
                            <label
                                for="product-price"
                                class="mb-2 block font-medium"
                                >{{ $t("ProductForm.Price") }}</label
                            >
                            <InputNumber
                                id="product-price"
                                v-model="form.price"
                                class="w-full"
                                input-id="product-price"
                                fluid
                                mode="decimal"
                                :min-fraction-digits="0"
                                :max-fraction-digits="2"
                                :min="0"
                                :invalid="!!validation.price"
                                :placeholder="$t('ProductForm.PriceOptional')"
                            />
                            <Message
                                v-if="validation.price"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.price[0] }}
                            </Message>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:items-end"
                    >
                        <div>
                            <span class="mb-2 block font-medium">{{
                                $t("ProductForm.LimitedStock")
                            }}</span>
                            <div
                                class="flex min-h-[2.75rem] items-center gap-3"
                            >
                                <ToggleSwitch
                                    v-model="form.is_limited"
                                    input-id="product-limited"
                                />
                                <label
                                    for="product-limited"
                                    class="m-0 cursor-pointer text-surface-700 dark:text-surface-200"
                                >
                                    {{
                                        form.is_limited
                                            ? $t("ProductForm.LimitedYes")
                                            : $t("ProductForm.LimitedNo")
                                    }}
                                </label>
                            </div>
                            <Message
                                v-if="validation.is_limited"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.is_limited[0] }}
                            </Message>
                        </div>
                        <div v-if="form.is_limited">
                            <label
                                for="product-stock"
                                class="mb-2 block font-medium"
                                >{{ $t("ProductForm.StockQuantity") }}</label
                            >
                            <InputNumber
                                id="product-stock"
                                v-model="form.stock_quantity"
                                class="w-full"
                                input-id="product-stock"
                                fluid
                                :min="0"
                                show-buttons
                                :invalid="!!validation.stock_quantity"
                            />
                            <Message
                                v-if="validation.stock_quantity"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.stock_quantity[0] }}
                            </Message>
                        </div>
                    </div>

                    <div>
                        <span class="mb-2 block font-medium">{{
                            $t("ProductForm.Status")
                        }}</span>
                        <div class="flex min-h-[2.75rem] items-center gap-3">
                            <ToggleSwitch
                                v-model="form.active"
                                input-id="product-active"
                            />
                            <label
                                for="product-active"
                                class="m-0 cursor-pointer text-surface-700 dark:text-surface-200"
                            >
                                {{
                                    form.active
                                        ? $t("ProductForm.Active")
                                        : $t("ProductForm.Inactive")
                                }}
                            </label>
                        </div>
                        <Message
                            v-if="validation.active"
                            severity="error"
                            size="small"
                            variant="simple"
                            class="mt-1"
                        >
                            {{ validation.active[0] }}
                        </Message>
                    </div>
                </div>

                <div class="col-span-12">
                    <aside
                        class="flex w-full flex-col rounded-xl border border-surface-200 bg-surface-50/80 p-4 dark:border-surface-700 dark:bg-surface-800/40"
                    >
                        <p
                            class="mb-3 text-sm font-semibold text-surface-800 dark:text-surface-100"
                        >
                            {{ $t("ProductForm.Media") }}
                        </p>
                        <p class="mb-3 text-xs text-surface-600 dark:text-surface-400">
                            {{ $t("ProductForm.MediaHint") }}
                        </p>

                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="item in displayMediaItems"
                                :key="item.key"
                                class="group relative overflow-hidden rounded-lg border border-surface-200 bg-surface-0 dark:border-surface-600 dark:bg-surface-900"
                            >
                                <div
                                    class="relative aspect-square w-full bg-surface-200 dark:bg-surface-700"
                                >
                                    <img
                                        :src="item.url"
                                        alt=""
                                        class="h-full w-full object-cover"
                                    />
                                    <Tag
                                        v-if="item.isDefault"
                                        :value="$t('ProductForm.DefaultImage')"
                                        severity="success"
                                        class="absolute left-2 top-2 !text-xs"
                                        rounded
                                    />
                                </div>
                                <div
                                    class="flex flex-col gap-1 border-t border-surface-200 p-2 dark:border-surface-700"
                                >
                                    <Button
                                        v-if="
                                            !item.isDefault &&
                                            productId &&
                                            item.serverId != null
                                        "
                                        type="button"
                                        size="small"
                                        outlined
                                        severity="secondary"
                                        class="w-full"
                                        :label="$t('ProductForm.SetDefault')"
                                        :loading="
                                            defaultLoadingId === item.serverId
                                        "
                                        :disabled="
                                            !!defaultLoadingId ||
                                            !!deletingMediaId ||
                                            uploadingMedia
                                        "
                                        @click="onSetDefault(item.serverId)"
                                    >
                                        <template #icon>
                                            <AppIcon name="pi pi-star" />
                                        </template>
                                    </Button>
                                    <Button
                                        type="button"
                                        size="small"
                                        outlined
                                        severity="danger"
                                        class="w-full"
                                        :label="$t('ProductForm.RemoveImage')"
                                        :loading="
                                            deletingMediaId === item.serverId
                                        "
                                        :disabled="
                                            !!deletingMediaId ||
                                            !!defaultLoadingId ||
                                            uploadingMedia
                                        "
                                        @click="onRemoveMedia(item)"
                                    >
                                        <template #icon>
                                            <AppIcon name="pi pi-trash" />
                                        </template>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <Button
                                type="button"
                                outlined
                                severity="secondary"
                                class="w-full"
                                :label="$t('ProductForm.AddImages')"
                                :loading="uploadingMedia"
                                :disabled="uploadingMedia"
                                @click="openFileDialog"
                            >
                                <template #icon>
                                    <AppIcon name="pi pi-plus" />
                                </template>
                            </Button>
                            <input
                                ref="fileInputRef"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                multiple
                                @change="onFileChange"
                            />
                        </div>
                    </aside>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="mt-2 flex justify-end gap-2">
                <Button
                    v-if="productId"
                    type="button"
                    :label="$t('ProductForm.SubmitUpdate')"
                    :loading="submitLoading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <AppIcon name="pi pi-check" />
                    </template>
                </Button>
                <Button
                    v-else
                    type="button"
                    :label="$t('ProductForm.SubmitCreate')"
                    :loading="submitLoading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <AppIcon name="pi pi-plus" />
                    </template>
                </Button>
            </div>
        </template>
    </Card>
</template>

<script setup lang="ts">
import { isAxiosError } from "axios";
import Tab from "primevue/tab";
import TabList from "primevue/tablist";
import TabPanel from "primevue/tabpanel";
import TabPanels from "primevue/tabpanels";
import Tabs from "primevue/tabs";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { productsService } from "../../../apis/services/products/products.apis";
import type {
    Product,
    ProductTranslation,
    ProductWritePayload,
    ProductTypeValue,
} from "../../../apis/services/products/product.type";

interface ProductFormProps {
    productId?: string;
}

const props = defineProps<ProductFormProps>();
const toast = useToast();
const router = useRouter();
const { t } = useI18n();

const isLoading = ref(!!props.productId);
const submitLoading = ref(false);
const activeLocale = ref("en");
const fileInputRef = ref<HTMLInputElement | null>(null);
const loadedProduct = ref<Product | null>(null);
const uploadingMedia = ref(false);
const deletingMediaId = ref<string | number | null>(null);
const defaultLoadingId = ref<string | number | null>(null);

interface PendingUpload {
    file: File;
    preview: string;
    key: string;
}

const pendingUploads = ref<PendingUpload[]>([]);

const form = ref({
    name: "",
    description: "",
    arName: "",
    arDescription: "",
    type: "physical" as ProductTypeValue,
    price: null as number | null,
    is_limited: false,
    stock_quantity: null as number | null,
    active: true,
});

const validation = ref<Record<string, string[]>>({});

const typeOptions = computed(() => [
    { label: t("ProductsList.Types.physical"), value: "physical" },
    { label: t("ProductsList.Types.service_fixed"), value: "service_fixed" },
    { label: t("ProductsList.Types.service_timer"), value: "service_timer" },
]);

function pickTranslation(
    translations: ProductTranslation[] | undefined,
    locale: string,
    key: string
): string {
    const row = translations?.find(
        (tr) => tr.locale === locale && tr.key === key
    );
    return row?.value ?? "";
}

function applyProduct(data: Product) {
    loadedProduct.value = data;
    form.value = {
        name: data.name ?? "",
        description: data.description ?? "",
        arName: pickTranslation(data.translations, "ar", "name"),
        arDescription: pickTranslation(
            data.translations,
            "ar",
            "description"
        ),
        type: (data.type as ProductTypeValue) ?? "physical",
        price:
            data.price === null || data.price === undefined
                ? null
                : Number(data.price),
        is_limited: Boolean(data.is_limited),
        stock_quantity:
            data.stock_quantity === null || data.stock_quantity === undefined
                ? null
                : Number(data.stock_quantity),
        active: Boolean(data.active),
    };
}

type DisplayMedia = {
    key: string;
    url: string;
    isDefault: boolean;
    serverId?: string | number;
    pendingIndex?: number;
};

const displayMediaItems = computed((): DisplayMedia[] => {
    const server =
        loadedProduct.value?.media?.map((m) => ({
            key: `s-${m.id}`,
            url: m.url ?? "",
            isDefault: Boolean(m.is_default),
            serverId: m.id,
        })) ?? [];

    const pending = pendingUploads.value.map((p, i) => ({
        key: p.key,
        url: p.preview,
        isDefault: false,
        pendingIndex: i,
    }));

    return [...server, ...pending];
});

watch(
    () => form.value.is_limited,
    (limited) => {
        if (!limited) {
            form.value.stock_quantity = null;
        }
    }
);

function openFileDialog() {
    fileInputRef.value?.click();
}

function pushPendingFiles(files: File[]) {
    for (const file of files) {
        pendingUploads.value.push({
            file,
            preview: URL.createObjectURL(file),
            key: `p-${Date.now()}-${Math.random().toString(36).slice(2)}`,
        });
    }
}

async function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);
    input.value = "";
    if (!files.length) {
        return;
    }

    if (!props.productId) {
        pushPendingFiles(files);
        return;
    }

    uploadingMedia.value = true;
    try {
        for (const file of files) {
            const { data } = await productsService.uploadProductMedia(
                props.productId,
                { file, collection: "default" }
            );
            applyProduct(data);
        }
        toast.add({
            severity: "success",
            summary: t("ProductForm.MediaUploaded"),
            life: 2500,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t("ProductForm.MediaUploadError"),
            life: 4000,
        });
    } finally {
        uploadingMedia.value = false;
    }
}

async function onSetDefault(mediaId: string | number | undefined) {
    if (!props.productId || mediaId == null) {
        return;
    }
    defaultLoadingId.value = mediaId;
    try {
        const { data } = await productsService.setDefaultProductMedia(
            props.productId,
            { media_id: mediaId }
        );
        applyProduct(data);
        toast.add({
            severity: "success",
            summary: t("ProductForm.DefaultUpdated"),
            life: 2500,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t("ProductForm.DefaultUpdateError"),
            life: 4000,
        });
    } finally {
        defaultLoadingId.value = null;
    }
}

async function onRemoveMedia(item: DisplayMedia) {
    if (item.pendingIndex !== undefined) {
        const i = item.pendingIndex;
        const pending = pendingUploads.value[i];
        if (pending) {
            URL.revokeObjectURL(pending.preview);
            pendingUploads.value.splice(i, 1);
        }
        return;
    }
    if (!props.productId || item.serverId == null) {
        return;
    }
    deletingMediaId.value = item.serverId;
    try {
        const { data } = await productsService.deleteProductMedia(
            props.productId,
            { media_id: item.serverId }
        );
        applyProduct(data);
        toast.add({
            severity: "success",
            summary: t("ProductForm.ImageRemoved"),
            life: 2500,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t("ProductForm.ImageRemoveError"),
            life: 4000,
        });
    } finally {
        deletingMediaId.value = null;
    }
}

function buildPayload(): ProductWritePayload {
    return {
        name: form.value.name.trim(),
        description: form.value.description.trim() || null,
        type: form.value.type,
        price: form.value.price,
        is_limited: form.value.is_limited,
        stock_quantity: form.value.is_limited
            ? form.value.stock_quantity ?? 0
            : null,
        active: form.value.active,
        translations: {
            ar: {
                name: form.value.arName.trim(),
                description: form.value.arDescription.trim() || null,
            },
        },
    };
}

async function uploadPendingAfterCreate(productId: string | number) {
    for (const p of pendingUploads.value) {
        await productsService.uploadProductMedia(productId, {
            file: p.file,
            collection: "default",
        });
    }
    for (const p of pendingUploads.value) {
        URL.revokeObjectURL(p.preview);
    }
    pendingUploads.value = [];
}

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};

    const payload = buildPayload();

    try {
        if (props.productId) {
            const res = await productsService.updateProduct(
                props.productId,
                payload
            );
            applyProduct(res.data);

            toast.add({
                severity: "success",
                summary: t("ProductForm.UpdatedSuccess"),
                life: 3000,
            });
            router.push("/products");
        } else {
            const res = await productsService.createProduct(payload);
            const newId = res.data.id;
            if (newId != null) {
                await uploadPendingAfterCreate(newId);
            }
            toast.add({
                severity: "success",
                summary: t("ProductForm.CreatedSuccess"),
                life: 3000,
            });
            router.push("/products");
        }
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            toast.add({
                severity: "error",
                summary: data?.message ?? t("ProductForm.ValidationError"),
                life: 4000,
            });
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("ProductForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onBeforeUnmount(() => {
    pendingUploads.value.forEach((p) => URL.revokeObjectURL(p.preview));
});

onMounted(async () => {
    if (!props.productId) {
        isLoading.value = false;
        return;
    }
    try {
        const { data } = await productsService.getProduct(props.productId);
        applyProduct(data);
    } catch {
        toast.add({
            severity: "error",
            summary: t("ProductForm.LoadError"),
            life: 4000,
        });
        router.push("/products");
    } finally {
        isLoading.value = false;
    }
});
</script>

<style scoped>
.product-locale-tabs :deep(.p-tablist-tab-list) {
    flex-wrap: wrap;
}
</style>
