import { api } from "./api.js";

const form = document.querySelector("#login-form");
const message = document.querySelector("#login-error");
const title = document.querySelector("#auth-title");
const description = document.querySelector("#auth-description");
const prompt = document.querySelector("#auth-prompt");
const toggle = document.querySelector("#auth-toggle");
const registerFields = document.querySelector("#register-fields");
const confirmationField = document.querySelector("#confirmation-field");
const submit = form.querySelector("[type=submit]");
const password = form.elements.contrasena;
const confirmation = form.elements.contrasena_confirmation;
let registering = false;

toggle.addEventListener("click", () => {
    registering = !registering;
    registerFields.hidden = confirmationField.hidden = !registering;
    registerFields.disabled = confirmation.disabled = !registering;
    title.textContent = registering ? "Crear cuenta" : "Iniciar sesión";
    submit.textContent = registering ? "Crear cuenta" : "Ingresar";
    description.textContent = registering ? "Regístrate en GEMDY Calendar." : "Accede a tus tareas de GEMDY Calendar.";
    prompt.textContent = registering ? "¿Ya tienes una cuenta?" : "¿Eres nuevo?";
    toggle.textContent = registering ? "Iniciar sesión" : "Crear cuenta";
    password.autocomplete = registering ? "new-password" : "current-password";
    if (registering) password.setAttribute("aria-describedby", "password-hint");
    else password.removeAttribute("aria-describedby");
    password.value = confirmation.value = message.textContent = "";
    (registering ? form.elements.nombre1 : form.elements.correo).focus();
});

form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (submit.disabled) return;
    const fields = Object.fromEntries(new FormData(form));
    fields.correo = fields.correo.trim();
    submit.disabled = toggle.disabled = true;
    message.textContent = registering ? "Creando cuenta..." : "Iniciando sesión...";
    try {
        const data = registering
            ? await api.request("/auth/register", { method: "POST", body: fields })
            : await api.login(fields.correo, fields.contrasena);
        api.setToken(data.token);
        sessionStorage.setItem("gemdy_usuario", JSON.stringify(data.usuario));
        window.location.assign("tareas.html");
    } catch (error) {
        const details = Object.values(error.errors || {}).flat();
        message.textContent = details.length ? details.join(" ") : error.message;
    } finally {
        submit.disabled = toggle.disabled = false;
    }
});
