import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import { UnitGroup, UnitGroupWritePayload } from "./unitGroups.type";

export const unitGroupsService = {
    getUnitGroups: async (params?: {
        page?: number;
        per_page?: number;
    }): Promise<AxiosResponse<Items<UnitGroup>>> => {
        return http.get<Items<UnitGroup>>("/api/unit-groups", { params });
    },
    getUnitGroup: async (
        id: string | number
    ): Promise<AxiosResponse<UnitGroup>> => {
        return http.get<UnitGroup>(`/api/unit-groups/${id}`);
    },
    createUnitGroup: async (
        payload: UnitGroupWritePayload
    ): Promise<AxiosResponse<UnitGroup>> => {
        return http.post<UnitGroup>("/api/unit-groups", payload);
    },
    updateUnitGroup: async (
        id: string | number,
        payload: UnitGroupWritePayload
    ): Promise<AxiosResponse<UnitGroup>> => {
        return http.put<UnitGroup>(`/api/unit-groups/${id}`, payload);
    },
    deleteUnitGroup: async (
        id: string | number
    ): Promise<AxiosResponse<UnitGroup>> => {
        return http.delete(`/api/unit-groups/${id}`);
    },
};
