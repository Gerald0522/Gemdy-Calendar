export class ApiError extends Error {
    constructor(message, status, errors = {}) {
        super(message);
        this.name = "ApiError";
        this.status = status;
        this.errors = errors;
    }
}

export class ApiClient {
    constructor(baseUrl = "/api", token = sessionStorage.getItem("gemdy_token")) {
        this.baseUrl = baseUrl;
        this.token = token;
    }

    setToken(token) {
        this.token = token;
        if (token) sessionStorage.setItem("gemdy_token", token);
        else sessionStorage.removeItem("gemdy_token");
    }

    async request(path, { method = "GET", body, signal } = {}) {
        let response;
        try {
            response = await fetch(`${this.baseUrl}${path}`, {
                method,
                signal,
                headers: {
                    Accept: "application/json",
                    ...(body !== undefined ? { "Content-Type": "application/json" } : {}),
                    ...(this.token ? { Authorization: `Bearer ${this.token}` } : {})
                },
                ...(body !== undefined ? { body: JSON.stringify(body) } : {})
            });
        } catch (error) {
            if (error.name === "AbortError") throw error;
            throw new ApiError("No se pudo conectar con el servidor.", 0);
        }
        const payload = response.status === 204 ? null : await response.json().catch(() => null);
        if (!response.ok) {
            if (response.status === 401 && path !== "/auth/login") {
                this.setToken(null);
                window.dispatchEvent(new CustomEvent("gemdy:unauthorized"));
            }
            throw new ApiError(payload?.message || `Error HTTP ${response.status}`, response.status, payload?.errors || {});
        }
        return payload;
    }

    login(correo, contrasena) { return this.request("/auth/login", { method: "POST", body: { correo, contrasena } }); }
    me() { return this.request("/auth/me"); }
    pendientes(page = 1, query = "", signal) { return this.request(`/pendientes?page=${page}&por_pagina=10&buscar=${encodeURIComponent(query)}`, { signal }); }
    crear(data) { return this.request("/pendientes", { method: "POST", body: data }); }
    editar(id, data) { return this.request(`/pendientes/${id}`, { method: "PUT", body: data }); }
    eliminar(id) { return this.request(`/pendientes/${id}`, { method: "DELETE" }); }
}

export const api = new ApiClient();
