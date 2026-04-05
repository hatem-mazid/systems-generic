<template>
    <div class="touch-manipulation">
        <ProductForm :product-id="productId" />
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import ProductForm from '../../components/pages/products/Form.vue';

const route = useRoute();
const productId = route.params.id;
</script>
