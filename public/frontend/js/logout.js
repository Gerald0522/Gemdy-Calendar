import { api } from "./api.js";

const button = document.querySelector("#cerrar-sesion");
function requireSession() {
    if (!sessionStorage.getItem("gemdy_token")) window.location.replace("login.html");
}

requireSession();
window.addEventListener("pageshow", requireSession);

button.addEventListener("click", async () => {
    button.disabled = true;
    try {
        if (api.token) {
            await api.request("/auth/logout", { method: "POST", signal: AbortSignal.timeout(5000) });
        }
    } catch {
        // El cierre local también funciona sin conexión.
    } finally {
        api.setToken(null);
        sessionStorage.removeItem("gemdy_usuario");
        sessionStorage.removeItem("gemdy_formulario");
        window.location.replace("login.html");
    }
});
