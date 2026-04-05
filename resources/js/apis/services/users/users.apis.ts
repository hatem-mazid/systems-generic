import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import { User } from "./users.type";

export const usersService = {
    getUsers: async (params?: {
        page?: number;
        per_page?: number;
        role?: string;
    }): Promise<AxiosResponse<Items<User>>> => {
        return http.get<Items<User>>("/api/users", { params });
    },
    getUser: async (id: string): Promise<AxiosResponse<User>> => {
        return http.get<User>(`/api/users/${id}`);
    },
    createUser: async (user: User): Promise<AxiosResponse<User>> => {
        return http.post<User>("/api/users", user);
    },
    updateUser: async (
        id: string,
        user: User
    ): Promise<AxiosResponse<User>> => {
        return http.put<User>(`/api/users/${id}`, user);
    },
    deleteUser: async (id: string): Promise<AxiosResponse<unknown>> => {
        return http.delete(`/api/users/${id}`);
    },
    resetPassword: async (
        id: string,
        payload: { password: string; password_confirmation: string }
    ): Promise<AxiosResponse<{ message?: string }>> => {
        return http.post(`/api/users/${id}/updatePassword`, payload);
    },
};
