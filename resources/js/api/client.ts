import axios from "axios";

const client = axios.create({
    baseURL: "/api",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
});

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
if (token) {
    client.defaults.headers.common["X-CSRF-TOKEN"] = token;
}

export default client;
