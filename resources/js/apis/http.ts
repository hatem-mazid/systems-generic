import axios from "axios";

// function readCookie(name) {
//     const value = `; ${document.cookie}`;
//     const parts = value.split(`; ${name}=`);
//     if (parts.length === 2) {
//         return parts.pop()?.split(";").shift();
//     }
//     return undefined;
// }

/** Shared Axios client for Laravel (session + JSON + CSRF cookie). */
const http = axios.create({
    baseURL: "",
    headers: {
        Accept: "application/json",
    },
});

// http.interceptors.request.use((config) => {
//     const token = readCookie("XSRF-TOKEN");
//     if (token) {
//         config.headers["X-XSRF-TOKEN"] = decodeURIComponent(token);
//     }
//     return config;
// });

http.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;

        if (status === 401) {
            window.location.assign("/login");
            return Promise.reject(error);
        }

        if (status === 419) {
            window.location.reload();
            return Promise.reject(error);
        }

        return Promise.reject(error);
    }
);

export default http;
