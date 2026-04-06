import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import { Unit, UnitWritePayload } from "./units.type";

export const unitsService = {
    getUnits: async (params?: {
        page?: number;
        per_page?: number;
        unit_group_id?: number;
    }): Promise<AxiosResponse<Items<Unit>>> => {
        return http.get<Items<Unit>>("/api/units", { params });
    },
    getUnit: async (id: string | number): Promise<AxiosResponse<Unit>> => {
        return http.get<Unit>(`/api/units/${id}`);
    },
    createUnit: async (
        payload: UnitWritePayload
    ): Promise<AxiosResponse<Unit>> => {
        return http.post<Unit>("/api/units", payload);
    },
    updateUnit: async (
        id: string | number,
        payload: Partial<UnitWritePayload>
    ): Promise<AxiosResponse<Unit>> => {
        return http.put<Unit>(`/api/units/${id}`, payload);
    },
    deleteUnit: async (id: string | number): Promise<AxiosResponse<void>> => {
        return http.delete(`/api/units/${id}`);
    },
    startOrder: async (id: string | number): Promise<AxiosResponse<unknown>> => {
        return http.post(`/api/units/${id}/start-order`);
    },
    reserveUnit: async (
        id: string | number,
        payload?: {
            reserved_at?: string | null;
            reserved_by?: string | null;
        }
    ): Promise<AxiosResponse<unknown>> => {
        return http.post(`/api/units/${id}/reserve`, payload);
    },
    closeUnit: async (id: string | number): Promise<AxiosResponse<unknown>> => {
        return http.post(`/api/units/${id}/close`);
    },
    cancelUnitReservation: async (
        id: string | number
    ): Promise<AxiosResponse<unknown>> => {
        return http.post(`/api/units/${id}/cancel-reservation`);
    },
};
