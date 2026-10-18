
import { api } from "./api.js";

const $ = (id) => document.getElementById(id);

const hoy = new Date();

const fechaSegura = (anio, mes, dia) => {
    return new Date(anio, mes, dia, 12);
};

const claveFecha = (fecha) => {
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, "0");
    const dia = String(fecha.getDate()).padStart(2, "0");

    return `${anio}-${mes}-${dia}`;
};

const formatearFecha = (fecha) => {
    return new Intl.DateTimeFormat("es-CR", {
        day: "numeric",
        month: "long",
        year: "numeric"
    }).format(fecha);
};

const estado = {
    vista: fechaSegura(
        hoy.getFullYear(),
        hoy.getMonth(),
        1
    ),
    seleccionada: fechaSegura(
        hoy.getFullYear(),
        hoy.getMonth(),
        hoy.getDate()
    ),
    tareas: [],
    cargando: false,
    error: ""
};

const tareasDelDia = (fecha) => {
    const clave = claveFecha(fecha);

    return estado.tareas.filter((tarea) => {
        return String(tarea.fecha_limite || "").slice(0, 10) === clave;
    });
};

const mostrarEstado = () => {
    const mensaje = $("calendario-estado");
    const reintentar = $("calendario-reintentar");

    if (estado.cargando) {
        mensaje.textContent = "Cargando tareas del calendario...";
    } else if (estado.error) {
        mensaje.textContent = estado.error;
    } else if (estado.tareas.length === 0) {
        mensaje.textContent = "No hay tareas registradas.";
    } else {
        mensaje.textContent = "";
    }

    reintentar.hidden = !estado.error;
};

const renderizarDetalles = () => {
    const contenedor = $("detalle-dia");

    contenedor.replaceChildren();

    const fecha = document.createElement("p");
    fecha.textContent = formatearFecha(estado.seleccionada);
    contenedor.append(fecha);

    const tareas = tareasDelDia(estado.seleccionada);

    if (tareas.length === 0) {
        const vacio = document.createElement("p");
        vacio.className = "muted";
        vacio.textContent = "No hay tareas para este día.";

        contenedor.append(vacio);
        return;
    }

    const lista = document.createElement("ul");

    tareas.forEach((tarea) => {
        const elemento = document.createElement("li");

        elemento.textContent = tarea.titulo;

        lista.append(elemento);
    });

    contenedor.append(lista);
};

const renderizarCalendario = (restaurarFoco = false) => {
    const anio = estado.vista.getFullYear();
    const mes = estado.vista.getMonth();

    const desplazamiento = (
        new Date(anio, mes, 1).getDay() + 6
    ) % 7;

    const totalDias = new Date(
        anio,
        mes + 1,
        0
    ).getDate();

    const cantidadCeldas = Math.ceil(
        (desplazamiento + totalDias) / 7
    ) * 7;

    const cuadricula = $("calendario-grid");

    $("titulo-cal").textContent = new Intl.DateTimeFormat(
        "es-CR",
        {
            month: "long",
            year: "numeric"
        }
    ).format(estado.vista);

    cuadricula.replaceChildren();

    const diasSemana = [
        "Lun",
        "Mar",
        "Mié",
        "Jue",
        "Vie",
        "Sáb",
        "Dom"
    ];

    diasSemana.forEach((nombre) => {
        const encabezado = document.createElement("div");

        encabezado.className = "weekday";
        encabezado.textContent = nombre;

        cuadricula.append(encabezado);
    });

    for (let indice = 0; indice < cantidadCeldas; indice++) {
        const fecha = fechaSegura(
            anio,
            mes,
            indice - desplazamiento + 1
        );

        const mesActual = fecha.getMonth() === mes;
        const esHoy = claveFecha(fecha) === claveFecha(hoy);
        const seleccionada = (
            claveFecha(fecha) === claveFecha(estado.seleccionada)
        );

        const tareas = tareasDelDia(fecha);

        const boton = document.createElement("button");

        boton.type = "button";
        boton.className = "day";

        if (!mesActual) {
            boton.classList.add("outside");
        }

        if (esHoy) {
            boton.classList.add("today");
            boton.setAttribute("aria-current", "date");
        }

        if (seleccionada) {
            boton.classList.add("selected");
        }

        boton.dataset.date = claveFecha(fecha);

        boton.setAttribute(
            "aria-pressed",
            String(seleccionada)
        );

        const numero = document.createElement("span");

        numero.className = "day-num";
        numero.textContent = fecha.getDate();

        boton.append(numero);

        if (tareas.length > 0) {
            const puntos = document.createElement("span");

            puntos.className = "day-dots";
            puntos.setAttribute("aria-hidden", "true");

            tareas.forEach((tarea) => {
                const punto = document.createElement("span");

                punto.className = "dot tarea";
                punto.title = tarea.titulo;

                puntos.append(punto);
            });

            boton.append(puntos);
        }

        const descripcion = tareas.length > 0
            ? `${formatearFecha(fecha)}: ${tareas.map(
                (tarea) => tarea.titulo
            ).join(", ")}`
            : formatearFecha(fecha);

        boton.setAttribute("aria-label", descripcion);

        boton.addEventListener("click", () => {
            estado.seleccionada = fecha;

            estado.vista = fechaSegura(
                fecha.getFullYear(),
                fecha.getMonth(),
                1
            );

            renderizarCalendario(true);
            renderizarDetalles();
        });

        cuadricula.append(boton);
    }

    if (restaurarFoco) {
        const botonActivo = [
            ...cuadricula.querySelectorAll("button.day")
        ].find((boton) => {
            return boton.dataset.date === claveFecha(
                estado.seleccionada
            );
        });

        botonActivo?.focus();
    }
};

const renderizar = () => {
    mostrarEstado();
    renderizarCalendario();
    renderizarDetalles();
};

const cargarTareas = async () => {
    estado.cargando = true;
    estado.error = "";

    mostrarEstado();

    try {
        const tareas = [];
        let pagina = 1;
        let ultimaPagina = 1;

        do {
            const respuesta = await api.pendientes(pagina);

            if (!Array.isArray(respuesta.data)) {
                throw new Error(
                    "La API no devolvió un listado válido."
                );
            }

            tareas.push(...respuesta.data);

          ultimaPagina = Number(
    respuesta.meta?.last_page ?? 1
);

            if (!Number.isInteger(ultimaPagina) ||
                ultimaPagina < 1) {
                throw new Error(
                    "La API devolvió una paginación inválida."
                );
            }

            pagina++;
        } while (pagina <= ultimaPagina);

        estado.tareas = tareas;
    } catch (error) {
        estado.error = error.message ||
            "No se pudieron cargar las tareas.";
    } finally {
        estado.cargando = false;
        renderizar();
    }
};

const cambiarMes = (cantidad) => {
    estado.vista = fechaSegura(
        estado.vista.getFullYear(),
        estado.vista.getMonth() + cantidad,
        1
    );

    estado.seleccionada = fechaSegura(
        estado.vista.getFullYear(),
        estado.vista.getMonth(),
        1
    );

    renderizar();
};

const iniciarCalendario = async () => {
    if (!api.token) {
        window.location.replace("login.html");
        return;
    }

    window.addEventListener("gemdy:unauthorized", () => {
        window.location.replace("login.html");
    });

    $("anterior").addEventListener("click", () => {
        cambiarMes(-1);
    });

    $("siguiente").addEventListener("click", () => {
        cambiarMes(1);
    });

    $("hoy").addEventListener("click", () => {
        estado.vista = fechaSegura(
            hoy.getFullYear(),
            hoy.getMonth(),
            1
        );

        estado.seleccionada = fechaSegura(
            hoy.getFullYear(),
            hoy.getMonth(),
            hoy.getDate()
        );

        renderizar();
    });

    $("calendario-reintentar").addEventListener(
        "click",
        cargarTareas
    );

    renderizar();
    await cargarTareas();
};

iniciarCalendario();
