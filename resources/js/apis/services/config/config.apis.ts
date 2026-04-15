import type { AxiosResponse } from "axios";
import http from "../../http";

export type DefaultConfigRange = {
    from_datetime: string;
    to_datetime: string;
    from_date: string;
    to_date: string;
};

export type AppConfigResponse = {
    opening_time: string;
    closing_time: string;
    default_config: DefaultConfigRange;
};

export const configService = {
    getConfig: async (): Promise<AxiosResponse<AppConfigResponse>> => {
        return http.get<AppConfigResponse>("/api/config");
    },

    updateConfig: async (payload: {
        opening_time: string;
        closing_time: string;
    }): Promise<AxiosResponse<AppConfigResponse>> => {
        return http.put<AppConfigResponse>("/api/config", payload);
    },
};
