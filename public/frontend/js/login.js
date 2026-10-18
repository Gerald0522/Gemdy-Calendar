import { api } from "./api.js";

const form = document.querySelector("#login-form");
const message = document.querySelector("#login-error");
form.addEventListener("submit", async (event) => {
    event.preventDefault();
    message.textContent = "Iniciando sesión...";
    try {
        const data = await api.login(form.correo.value, form.contrasena.value);
        api.setToken(data.token);
        sessionStorage.setItem("gemdy_usuario", JSON.stringify(data.usuario));
        window.location.assign("tareas.html");
    } catch (error) {
        message.textContent = error.message;
    }
});
