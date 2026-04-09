import type { AxiosResponse } from "axios";
import http from "../../http";

export interface SectionOption {
    id: number | string;
    name: string;
    code: string;
}

export const sectionsService = {
    getSections: async (): Promise<
        AxiosResponse<{ items: SectionOption[] }>
    > => {
        return http.get<{ items: SectionOption[] }>("/api/sections");
    },
};
