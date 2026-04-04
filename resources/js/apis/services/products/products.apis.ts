import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import {
    Product,
    ProductMediaPayload,
    ProductWritePayload,
    DeleteProductMediaPayload,
    SetDefaultProductMediaPayload,
} from "./product.type";

export const productsService = {
    getProducts: async (params?: {
        page?: number;
        per_page?: number;
        category_id?: number | string | null;
        type?: string | null;
        active?: boolean | string | number | null;
        search?: string | null;
    }): Promise<AxiosResponse<Items<Product>>> => {
        return http.get<Items<Product>>("/api/products", { params });
    },
    getProduct: async (
        id: string | number
    ): Promise<AxiosResponse<Product>> => {
        return http.get<Product>(`/api/products/${id}`);
    },
    createProduct: async (
        product: ProductWritePayload
    ): Promise<AxiosResponse<Product>> => {
        return http.post<Product>("/api/products", product);
    },
    updateProduct: async (
        id: string | number,
        product: ProductWritePayload
    ): Promise<AxiosResponse<Product>> => {
        return http.put<Product>(`/api/products/${id}`, product);
    },
    deleteProduct: async (
        id: string | number
    ): Promise<AxiosResponse<Product>> => {
        return http.delete(`/api/products/${id}`);
    },
    uploadProductMedia: async (
        id: string | number,
        payload: ProductMediaPayload
    ): Promise<AxiosResponse<Product>> => {
        const formData = new FormData();
        formData.append("file", payload.file);

        if (payload.collection) {
            formData.append("collection", payload.collection);
        }

        return http.post<Product>(`/api/products/${id}/media`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
    },
    deleteProductMedia: async (
        id: string | number,
        payload: DeleteProductMediaPayload
    ): Promise<AxiosResponse<Product>> => {
        return http.delete(`/api/products/${id}/media`, {
            data: payload,
        });
    },
    setDefaultProductMedia: async (
        id: string | number,
        payload: SetDefaultProductMediaPayload
    ): Promise<AxiosResponse<Product>> => {
        return http.post<Product>(
            `/api/products/${id}/media/default`,
            payload
        );
    },
};
