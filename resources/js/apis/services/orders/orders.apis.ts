import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import { Order, OrderPrintResponse } from "./orders.type";

export const ordersService = {
    getOrders: async (params?: {
        page?: number;
        per_page?: number;
        status?: string | null;
        user_id?: number | string | null;
        /** ISO date string `YYYY-MM-DD` */
        date_from?: string | null;
        /** ISO date string `YYYY-MM-DD` */
        date_to?: string | null;
    }): Promise<AxiosResponse<Items<Order>>> => {
        return http.get<Items<Order>>("/api/orders", { params });
    },
    getOrder: async (
        id: string | number
    ): Promise<AxiosResponse<Order>> => {
        return http.get<Order>(`/api/orders/${id}`);
    },
    createOrder: async (): Promise<AxiosResponse<Order>> => {
        return http.post<Order>("/api/orders/takeaway");
    },
    closeOrder: async (
        id: string | number
    ): Promise<AxiosResponse<Order>> => {
        return http.post<Order>(`/api/orders/${id}/close`);
    },
    submitTakeawayOrder: async (
        id: string | number
    ): Promise<AxiosResponse<Order>> => {
        return http.post<Order>(`/api/orders/${id}/submit-takeaway`);
    },
    addOrderItem: async (
        orderId: string | number,
        payload: {
            product_id: number;
            quantity?: number;
            notes?: string | null;
        }
    ): Promise<AxiosResponse<Order>> => {
        return http.post<Order>(`/api/orders/${orderId}/items`, payload);
    },
    removeOrderItem: async (
        orderId: string | number,
        itemId: string | number
    ): Promise<AxiosResponse<Order>> => {
        return http.delete<Order>(`/api/orders/${orderId}/items/${itemId}`);
    },
    updateOrderItem: async (
        orderId: string | number,
        itemId: string | number,
        payload: { quantity: number }
    ): Promise<AxiosResponse<Order>> => {
        return http.patch<Order>(
            `/api/orders/${orderId}/items/${itemId}`,
            payload
        );
    },
    printOrder: async (
        orderId: string | number
    ): Promise<AxiosResponse<OrderPrintResponse>> => {
        return http.post<OrderPrintResponse>(`/api/orders/${orderId}/print`);
    },
};
