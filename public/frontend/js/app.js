"use strict";

const $ = (id) => document.getElementById(id);

const formatDate = (date) => new Intl.DateTimeFormat("es-CR", {
    day: "numeric",
    month: "long",
    year: "numeric"
}).format(date);

const safeDate = (year, month, day) => new Date(year, month, day, 12);
const dateKey = (date) => [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, "0"),
    String(date.getDate()).padStart(2, "0")
].join("-");

function showMessage(id, text) {
    const element = $(id);
    if (!element) return;
    element.textContent = text;
    element.hidden = !text;
}

function addCell(row, text) {
    const cell = document.createElement("td");
    cell.textContent = text;
    row.append(cell);
    return cell;
}




if ($("calendario-grid")) {
    const today = new Date();
    let selected = safeDate(today.getFullYear(), today.getMonth(), today.getDate());
    let view = safeDate(today.getFullYear(), today.getMonth(), 1);


    const exampleEvents = [
        { day: 3, type: "clase", title: "Matemática Discreta" },
        { day: 7, type: "tarea", title: "Entrega de laboratorio" },
        { day: 12, type: "examen", title: "Examen de Bases de Datos" },
        { day: 18, type: "actividad", title: "Reunión de grupo" },
        { day: 24, type: "estudio", title: "Sesión de estudio" }
    ].map((event) => ({
        ...event,
        date: dateKey(safeDate(today.getFullYear(), today.getMonth(), event.day))
    }));

    function eventsFor(date) {
        return exampleEvents.filter((event) => event.date === dateKey(date));
    }

    function renderCalendar(restoreFocus = false) {
        const year = view.getFullYear();
        const month = view.getMonth();
        const offset = (new Date(year, month, 1).getDay() + 6) % 7;
        const total = new Date(year, month + 1, 0).getDate();
        const cells = Math.ceil((offset + total) / 7) * 7;
        const grid = $("calendario-grid");

        $("titulo-cal").textContent = new Intl.DateTimeFormat("es-CR", {
            month: "long",
            year: "numeric"
        }).format(view);

        grid.replaceChildren();
        ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"].forEach((name) => {
            const heading = document.createElement("div");
            heading.className = "weekday";
            heading.textContent = name;
            grid.append(heading);
        });

        for (let index = 0; index < cells; index++) {
            const date = safeDate(year, month, index - offset + 1);
            const currentMonth = date.getMonth() === month;
            const isToday = dateKey(date) === dateKey(today);
            const isSelected = dateKey(date) === dateKey(selected);
            const button = document.createElement("button");

            button.type = "button";
            button.className = "day" +
                (currentMonth ? "" : " outside") +
                (isToday ? " today" : "") +
                (isSelected ? " selected" : "");
            button.dataset.date = dateKey(date);
            button.setAttribute("aria-label", formatDate(date));
            button.setAttribute("aria-pressed", String(isSelected));
            if (isToday) button.setAttribute("aria-current", "date");

            const number = document.createElement("span");
            number.className = "day-num";
            number.textContent = date.getDate();
            button.append(number);

            const events = eventsFor(date);
            if (events.length) {
                const dots = document.createElement("span");
                dots.className = "day-dots";
                dots.setAttribute("aria-hidden", "true");
                events.forEach((event) => {
                    const dot = document.createElement("span");
                    dot.className = "dot " + event.type;
                    dot.title = event.title;
                    dots.append(dot);
                });
                button.setAttribute("aria-label", formatDate(date) + ": " +
                    events.map((event) => event.title).join(", "));
                button.append(dots);
            }

            button.addEventListener("click", () => {
                selected = date;
                view = safeDate(date.getFullYear(), date.getMonth(), 1);
                renderCalendar(true);
                renderDetails();
            });
            grid.append(button);
        }

        if (restoreFocus) {
            const active = [...grid.querySelectorAll("button.day")].find(
                (button) => button.dataset.date === dateKey(selected)
            );
            if (active) active.focus();
        }
    }

    function renderDetails() {
        const container = $("detalle-dia");
        container.replaceChildren();
        const heading = document.createElement("p");
        heading.textContent = formatDate(selected);
        container.append(heading);
        const events = eventsFor(selected);
        if (!events.length) {
            const empty = document.createElement("p");
            empty.className = "muted";
            empty.textContent = "No hay actividades de ejemplo para este día.";
            container.append(empty);
            return;
        }
        const list = document.createElement("ul");
        events.forEach((event) => {
            const item = document.createElement("li");
            item.textContent = event.title;
            list.append(item);
        });
        container.append(list);
    }

    function changeMonth(delta) {
        view = safeDate(view.getFullYear(), view.getMonth() + delta, 1);
        selected = safeDate(view.getFullYear(), view.getMonth(), 1);
        renderCalendar();
        renderDetails();
    }

    $("anterior").addEventListener("click", () => changeMonth(-1));
    $("siguiente").addEventListener("click", () => changeMonth(1));
    $("hoy").addEventListener("click", () => {
        selected = safeDate(today.getFullYear(), today.getMonth(), today.getDate());
        view = safeDate(today.getFullYear(), today.getMonth(), 1);
        renderCalendar();
        renderDetails();
    });
    renderCalendar();
    renderDetails();
}


// Horario: validación nativa y validaciones complementarias.

if ($("horario-form")) {
    const form = $("horario-form");
    const blocks = [];
    const names = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

    function renderSchedule() {
        const body = $("horario-lista");
        body.replaceChildren();
        $("horario-vacio").hidden = blocks.length > 0;

        blocks.forEach((block, index) => {
            const row = document.createElement("tr");
            addCell(row, block.days.map((day) => names[day]).join(", "));
            addCell(row, block.start);
            addCell(row, block.end);
            addCell(row, block.name);
            addCell(row, block.type);
            const actions = document.createElement("td");
            const remove = document.createElement("button");
            remove.type = "button";
            remove.className = "button secondary";
            remove.textContent = "Eliminar";
            remove.setAttribute("aria-label", "Eliminar " + block.name);
            remove.addEventListener("click", () => {
                blocks.splice(index, 1);
                renderSchedule();
                form.querySelector("button[type=submit]").focus();
            });
            actions.append(remove);
            row.append(actions);
            body.append(row);
        });
    }

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        showMessage("horario-error", "");
        if (!form.reportValidity()) return;

        const data = new FormData(form);
        const days = data.getAll("dias").map(Number);
        if (!days.length) {
            showMessage("horario-error", "Selecciona al menos un día de la semana.");
            form.querySelector('input[name="dias"]').focus();
            return;
        }
        if (data.get("fin") <= data.get("inicio")) {
            showMessage("horario-error", "La hora final debe ser posterior a la inicial.");
            $("fin").focus();
            return;
        }
        blocks.push({
            name: String(data.get("nombre")).trim(),
            type: String(data.get("tipo")),
            start: String(data.get("inicio")),
            end: String(data.get("fin")),
            days
        });
        form.reset();
        renderSchedule();
        $("nombre-bloque").focus();
    });
    form.addEventListener("reset", () => showMessage("horario-error", ""));
    renderSchedule();
}

// Tareas: registro, estado y eliminación en memoria.

if ($("tarea-form")) {
    const form = $("tarea-form");
    const tasks = [];

    function renderTasks() {
        const body = $("tareas-lista");
        body.replaceChildren();
        $("tareas-vacio").hidden = tasks.length > 0;
        $("tarea-contador").textContent = tasks.length +
            (tasks.length === 1 ? " tarea" : " tareas");

        tasks.forEach((task, index) => {
            const row = document.createElement("tr");
            const title = addCell(row, task.name);
            if (task.done) title.className = "done";
            addCell(row, task.course);
            addCell(row, task.date);
            addCell(row, task.priority).className = "priority";

            const actions = document.createElement("td");
            const toggle = document.createElement("button");
            const remove = document.createElement("button");
            toggle.type = remove.type = "button";
            toggle.className = remove.className = "button secondary";
            toggle.textContent = task.done ? "Reabrir" : "Completar";
            remove.textContent = "Eliminar";
            toggle.setAttribute("aria-label", toggle.textContent + " " + task.name);
            remove.setAttribute("aria-label", "Eliminar " + task.name);
            toggle.addEventListener("click", () => {
                task.done = !task.done;
                renderTasks();
                const updated = body.rows[index]?.querySelector("button");
                if (updated) updated.focus();
            });
            remove.addEventListener("click", () => {
                tasks.splice(index, 1);
                renderTasks();
                $("tarea-nombre").focus();
            });
            actions.append(toggle, document.createTextNode(" "), remove);
            row.append(actions);
            body.append(row);
        });
    }

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        showMessage("tarea-error", "");
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const name = String(data.get("nombre")).trim();
        const course = String(data.get("curso")).trim();
        if (name.length < 3 || !course) {
            showMessage("tarea-error", "Completa el nombre y el curso de la tarea.");
            $("tarea-nombre").focus();
            return;
        }
        tasks.push({
            name,
            course,
            date: String(data.get("fecha")),
            priority: String(data.get("prioridad")),
            description: String(data.get("descripcion") || ""),
            done: false
        });
        form.reset();
        renderTasks();
        $("tarea-nombre").focus();
    });
    renderTasks();
}
