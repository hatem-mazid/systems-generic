<template>
    <Card
        class="group h-full overflow-hidden rounded-2xl border border-surface-200/80 bg-surface-0 shadow-sm transition-all duration-200 hover:border-primary-200/60 hover:shadow-md dark:border-surface-700 dark:bg-surface-900 dark:hover:border-primary-700/40"
    >
        <template #content>
            <div class="flex touch-manipulation flex-col gap-3 p-2 sm:p-3">
                <div
                    class="relative aspect-video w-full overflow-hidden rounded-lg bg-surface-200 dark:bg-surface-700"
                >
                    <div
                        v-if="galleryItems.length > 1"
                        class="relative h-full min-h-[10rem] w-full"
                    >
                        <div
                            ref="galleryScrollRef"
                            class="product-card-gallery-scroll flex h-full w-full snap-x snap-mandatory overflow-x-auto overflow-y-hidden overscroll-x-contain touch-pan-x"
                            @scroll.passive="onGalleryScroll"
                        >
                            <div
                                v-for="item in galleryItems"
                                :key="item.id"
                                class="relative h-full min-w-full shrink-0 snap-start"
                            >
                                <img
                                    :src="item.url"
                                    :alt="
                                        product.name ||
                                        $t('ProductsList.ImageAltFallback')
                                    "
                                    class="h-full w-full object-cover"
                                    draggable="false"
                                />
                            </div>
                        </div>
                        <ul class="product-card-gallery-dots pointer-events-auto">
                            <li
                                v-for="(item, index) in galleryItems"
                                :key="item.id"
                            >
                                <button
                                    type="button"
                                    class="product-card-gallery-dot"
                                    :class="{
                                        'product-card-gallery-dot--active':
                                            index === galleryActiveIndex,
                                    }"
                                    :aria-label="
                                        $t('ProductsList.GalleryGoToSlide', {
                                            n: index + 1,
                                        })
                                    "
                                    :aria-current="
                                        index === galleryActiveIndex
                                            ? 'true'
                                            : undefined
                                    "
                                    @click="scrollGalleryTo(index)"
                                />
                            </li>
                        </ul>
                    </div>
                    <img
                        v-else-if="galleryItems.length === 1"
                        :src="galleryItems[0].url"
                        :alt="
                            product.name || $t('ProductsList.ImageAltFallback')
                        "
                        class="h-full w-full object-cover"
                        draggable="false"
                    />
                    <div
                        v-else
                        class="flex h-full min-h-[10rem] w-full items-center justify-center text-surface-500 dark:text-surface-300"
                    >
                        <AppIcon name="pi pi-images" class="!text-3xl" />
                    </div>
                </div>

                <div class="min-h-16 space-y-3">
                    <div
                        class="flex min-w-0 items-center justify-between gap-2"
                    >
                        <h3
                            class="min-w-0 flex-1 truncate text-xl font-semibold leading-tight text-surface-900 dark:text-surface-0"
                        >
                            {{ product.name || "-" }}
                        </h3>
                        <Tag
                            v-if="activeKnown"
                            :value="activeLabel"
                            :severity="isActive ? 'success' : 'warn'"
                            class="shrink-0 !text-xs"
                            rounded
                        />
                        <span
                            v-else
                            class="shrink-0 text-xs text-surface-500 dark:text-surface-400"
                        >
                            —
                        </span>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-2 text-sm text-surface-700 dark:text-surface-200"
                    >
                        <Tag
                            v-if="product.type"
                            :value="typeLabel"
                            severity="secondary"
                            class="!text-xs"
                            rounded
                        />
                        <span class="font-semibold text-surface-900 dark:text-surface-50">
                            {{ priceLabel }}
                        </span>
                    </div>
                    <p
                        class="line-clamp-2 text-sm text-surface-600 dark:text-surface-300"
                    >
                        {{
                            product.description ||
                            $t("ProductsList.NoDescription")
                        }}
                    </p>
                </div>

                <div
                    class="flex items-center justify-end gap-3 border-t border-surface-200/80 pt-4 dark:border-surface-700"
                >
                    <Button
                        as="router-link"
                        :to="`/products/${product.id}`"
                        size="large"
                        rounded
                        outlined
                        severity="info"
                        :aria-label="$t('Edit')"
                    >
                        <template #icon>
                            <AppIcon name="pi pi-pencil" />
                        </template>
                    </Button>

                    <DeleteButton
                        :product="product"
                        @deleted="$emit('deleted')"
                    />
                </div>
            </div>
        </template>
    </Card>
</template>

<script setup>
import Card from "primevue/card";
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import DeleteButton from "./DeleteButton.vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

defineEmits(["deleted"]);

const { t } = useI18n();

/** Ordered for gallery: default image first, then others by id. */
const galleryItems = computed(() => {
    const media = props.product?.media ?? [];
    const withUrl = media.filter((m) => m?.url);
    if (!withUrl.length) {
        return [];
    }
    return [...withUrl]
        .sort((a, b) => {
            const ad = a.is_default ? 1 : 0;
            const bd = b.is_default ? 1 : 0;
            if (bd !== ad) {
                return bd - ad;
            }
            return Number(a.id) - Number(b.id);
        })
        .map((m) => ({
            id: m.id,
            url: m.url,
        }));
});

const galleryScrollRef = ref(null);
const galleryActiveIndex = ref(0);
let galleryScrollRaf = 0;

function updateGalleryActiveIndex() {
    const el = galleryScrollRef.value;
    if (!el) {
        return;
    }
    const w = el.clientWidth;
    if (w <= 0) {
        return;
    }
    const n = galleryItems.value.length;
    const idx = Math.round(el.scrollLeft / w);
    galleryActiveIndex.value = Math.min(Math.max(0, idx), Math.max(0, n - 1));
}

function onGalleryScroll() {
    if (galleryScrollRaf) {
        cancelAnimationFrame(galleryScrollRaf);
    }
    galleryScrollRaf = requestAnimationFrame(() => {
        galleryScrollRaf = 0;
        updateGalleryActiveIndex();
    });
}

function scrollGalleryTo(index) {
    const el = galleryScrollRef.value;
    if (!el) {
        return;
    }
    const w = el.clientWidth;
    el.scrollTo({ left: index * w, behavior: "auto" });
    galleryActiveIndex.value = index;
}

watch(
    () => galleryItems.value.map((x) => x.id).join(","),
    () => {
        galleryActiveIndex.value = 0;
        nextTick(() => {
            const el = galleryScrollRef.value;
            if (el) {
                el.scrollLeft = 0;
            }
        });
    }
);

onBeforeUnmount(() => {
    if (galleryScrollRaf) {
        cancelAnimationFrame(galleryScrollRaf);
    }
});

const activeKnown = computed(
    () =>
        props.product?.active !== undefined && props.product?.active !== null
);

const isActive = computed(() => Boolean(props.product?.active));

const activeLabel = computed(() =>
    isActive.value ? t("UserForm.Active") : t("UserForm.Inactive")
);

const typeLabel = computed(() => {
    const key = props.product?.type;
    if (!key) {
        return "—";
    }
    const labels = {
        physical: () => t("ProductsList.Types.physical"),
        service_fixed: () => t("ProductsList.Types.service_fixed"),
        service_timer: () => t("ProductsList.Types.service_timer"),
    };
    return labels[key]?.() ?? String(key);
});

const priceLabel = computed(() => {
    const p = props.product?.price;
    if (p === null || p === undefined || p === "") {
        return t("ProductsList.PriceOnRequest");
    }
    const n = Number(p);
    if (Number.isNaN(n)) {
        return "—";
    }
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "IQD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(n);
});
</script>

<style scoped>
/* Native horizontal scroll + scroll-snap: image tracks finger while dragging (no carousel touch-end commit). */
.product-card-gallery-scroll {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.product-card-gallery-scroll::-webkit-scrollbar {
    display: none;
}

.product-card-gallery-dots {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
    display: flex;
    justify-content: center;
    gap: 0.35rem;
    margin: 0;
    padding: 0.25rem 0.5rem 0.35rem;
    list-style: none;
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, 0.45),
        transparent
    );
    border-radius: 0 0 0.5rem 0.5rem;
}

.product-card-gallery-dot {
    width: 0.5rem;
    height: 0.5rem;
    min-width: 0.5rem;
    min-height: 0.5rem;
    padding: 0;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.45);
    transition: background 0.15s ease, transform 0.15s ease;
}

.product-card-gallery-dot--active {
    background: rgba(255, 255, 255, 0.95);
    transform: scale(1.15);
}
</style>
