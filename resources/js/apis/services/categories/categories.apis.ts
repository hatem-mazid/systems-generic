import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import {
    Category,
    CategoryMediaPayload,
    CategoryWritePayload,
    DeleteCategoryMediaPayload,
} from "./categories.type";

export const categoriesService = {
    getCategories: async (params?: {
        page?: number;
        per_page?: number;
    }): Promise<AxiosResponse<Items<Category>>> => {
        return http.get<Items<Category>>("/api/categories", { params });
    },
    getCategory: async (
        id: string | number
    ): Promise<AxiosResponse<Category>> => {
        return http.get<Category>(`/api/categories/${id}`);
    },
    createCategory: async (
        category: CategoryWritePayload
    ): Promise<AxiosResponse<Category>> => {
        return http.post<Category>("/api/categories", category);
    },
    updateCategory: async (
        id: string | number,
        category: CategoryWritePayload
    ): Promise<AxiosResponse<Category>> => {
        return http.put<Category>(`/api/categories/${id}`, category);
    },
    deleteCategory: async (
        id: string | number
    ): Promise<AxiosResponse<Category>> => {
        return http.delete(`/api/categories/${id}`);
    },
    uploadCategoryMedia: async (
        id: string | number,
        payload: CategoryMediaPayload
    ): Promise<AxiosResponse<Category>> => {
        const formData = new FormData();
        formData.append("file", payload.file);

        if (payload.collection) {
            formData.append("collection", payload.collection);
        }

        if (typeof payload.replace === "boolean") {
            formData.append("replace", payload.replace ? "1" : "0");
        }

        return http.post<Category>(`/api/categories/${id}/media`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
    },
    deleteCategoryMedia: async (
        id: string | number,
        payload?: DeleteCategoryMediaPayload
    ): Promise<AxiosResponse<Category>> => {
        return http.delete(`/api/categories/${id}/media`, {
            data: payload,
        });
    },
};
