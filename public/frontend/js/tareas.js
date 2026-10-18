import { api } from "./api.js";
import { Estado } from "./estado.js";

const $ = (id) => document.getElementById(id);
const form = $("tarea-form");
const tbody = $("tareas-lista");
const status = $("lab9-estado");
const pagination = $("lab9-paginacion");
const search = $("buscar");
const user = JSON.parse(sessionStorage.getItem("gemdy_usuario") || "null");
const state = new Estado({ status: "loading", rows: [], page: 1, lastPage: 1, total: 0, query: "", error: "", editing: null, editingStatus: null, errors: {} });
let controller = null;
let debounceTimer;

function guardarBorrador() {
    const borrador = {
        campos: Object.fromEntries(new FormData(form)),
        editing: state.value.editing,
        editingStatus: state.value.editingStatus
    };
    sessionStorage.setItem("gemdy_formulario", JSON.stringify(borrador));
}

window.addEventListener("gemdy:unauthorized", () => {
    guardarBorrador();
    window.location.replace("login.html?sesion=expirada");
});

const saved = sessionStorage.getItem("gemdy_formulario");
if (saved) {
    try {
        const borrador = JSON.parse(saved);
        const campos = borrador.campos || borrador;
        for (const [key, value] of Object.entries(campos)) {
            const input = form.elements.namedItem(key);
            if (input && typeof value === "string") input.value = value;
        }
        if (borrador.editing != null) {
            state.set({
                editing: borrador.editing,
                editingStatus: borrador.editingStatus || "pendiente"
            });
        }
        sessionStorage.removeItem("gemdy_formulario");
    } catch (error) {
        console.error("No se pudo recuperar el borrador:", error);
    }
}

if (!api.token || !user?.id) {
    guardarBorrador();
    window.location.replace("login.html?sesion=expirada");
}

const cell = (row, value) => {
    const td = document.createElement("td");
    td.textContent = value ?? "—";
    row.append(td);
};
const action = (label, fn) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "button secondary";
    button.textContent = label;
    button.addEventListener("click", fn);
    return button;
};

function render(s) {
    tbody.replaceChildren();
    status.textContent = s.status === "loading" ? "Cargando tareas..." : s.status === "error" ? `Error: ${s.error}` : s.status === "empty" ? "No hay tareas para mostrar." : `Mostrando ${s.rows.length} de ${s.total} tareas.`;
    $("tareas-vacio").hidden = true;
    $("tarea-contador").textContent = `${s.total} tareas`;
    $("lab9-reintentar").hidden = s.status !== "error";
    if (s.status === "data") {
        for (const task of s.rows) {
            const row = document.createElement("tr");
            cell(row, task.titulo);
            cell(row, task.curso_id || "Sin curso");
            cell(row, task.fecha_limite);
            cell(row, task.estado);
            const td = document.createElement("td");
            td.append(action("Editar", () => edit(task)), " ", action("Eliminar", () => remove(task.id)));
            row.append(td);
            tbody.append(row);
        }
    }
    pagination.replaceChildren();
    if (s.lastPage > 1) {
        const previous = action("Anterior", () => load(s.page - 1));
        previous.disabled = s.page <= 1;
        const next = action("Siguiente", () => load(s.page + 1));
        next.disabled = s.page >= s.lastPage;
        const label = document.createElement("span");
        label.textContent = ` Página ${s.page} de ${s.lastPage} `;
        pagination.append(previous, label, next);
    }
    $("tarea-form-titulo").textContent = s.editing ? "Editar tarea" : "Ingresar tarea";
    $("lab9-guardar").textContent = s.editing ? "Guardar cambios" : "Registrar tarea";
    for (const key of ["titulo", "fecha_limite", "descripcion", "curso_id", "estado", "usuario_id"]) {
        const el = document.querySelector(`[data-error="${key}"]`);
        if (el) el.textContent = (s.errors[key] || []).join(" ");
    }
}
state.subscribe(render);

async function load(page = 1) {
    controller?.abort();
    controller = new AbortController();
    const current = controller;
    state.set({ status: "loading", page, error: "" });
    try {
        const result = await api.pendientes(page, state.value.query, current.signal);
        if (current !== controller) return;
        const rows = result.data;
        state.set({ status: rows.length ? "data" : "empty", rows, total: result.meta.total, lastPage: result.meta.last_page });
    } catch (error) {
        if (error.name !== "AbortError" && current === controller) state.set({ status: "error", error: error.message, rows: [] });
    }
}

function edit(task) {
    state.set({ editing: task.id, editingStatus: task.estado, errors: {} });
    form.elements.nombre.value = task.titulo;
    form.elements.fecha.value = task.fecha_limite || "";
    form.elements.descripcion.value = task.descripcion || "";
    form.elements.curso.value = task.curso_id || "";
    form.elements.prioridad.value = "Media";
    form.scrollIntoView({ behavior: "smooth" });
}
async function remove(id) {
    if (!confirm("¿Eliminar esta tarea?")) return;
    try { await api.eliminar(id); await load(state.value.page); }
    catch (error) { state.set({ status: "error", error: error.message }); }
}

form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const data = {
        usuario_id: user.id,
        titulo: form.elements.nombre.value.trim(),
        descripcion: form.elements.descripcion.value.trim() || null,
        fecha_limite: form.elements.fecha.value || null,
        curso_id: form.elements.curso.value.trim() || null,
        estado: state.value.editing ? state.value.editingStatus : "pendiente"
    };
    state.set({ errors: {} });
    try {
        if (state.value.editing) await api.editar(state.value.editing, data);
        else await api.crear(data);
        form.reset();
        state.set({ editing: null, editingStatus: null });
        await load(state.value.page);
    } catch (error) {
        if (error.status === 422) state.set({ errors: error.errors });
        else $("tarea-error").textContent = error.message;
        $("tarea-error").hidden = error.status === 422;
    }
});

search.placeholder = "Buscar tareas...";
search.addEventListener("input", () => {
    clearTimeout(debounceTimer);
    controller?.abort();
    debounceTimer = setTimeout(() => {
        state.set({ query: search.value.trim() });
        load(1);
    }, 350);
});
$("lab9-reintentar").addEventListener("click", () => load(state.value.page));
$("lab9-cancelar").addEventListener("click", () => { form.reset(); state.set({ editing: null, editingStatus: null, errors: {} }); });
load();
