import type { AxiosResponse } from "axios";
import http from "../../http";
import type { Items } from "../../types";
import type { Role } from "./roles.type";

export const rolesService = {
    getRoles: async (params?: {
        page?: number;
        per_page?: number;
    }): Promise<AxiosResponse<Items<Role>>> => {
        return http.get<Items<Role>>("/api/roles", { params });
    },
    getRole: async (id: string): Promise<AxiosResponse<Role>> => {
        return http.get<Role>(`/api/roles/${id}`);
    },
    createRole: async (role: Role): Promise<AxiosResponse<Role>> => {
        return http.post<Role>("/api/roles", role);
    },
    updateRole: async (id: string, role: Role): Promise<AxiosResponse<Role>> => {
        return http.put<Role>(`/api/roles/${id}`, role);
    },
    deleteRole: async (id: string): Promise<AxiosResponse<unknown>> => {
        return http.delete(`/api/roles/${id}`);
    },
    getPermissions: async (): Promise<AxiosResponse<{ items: string[] }>> => {
        return http.get<{ items: string[] }>("/api/permissions");
    },
};
