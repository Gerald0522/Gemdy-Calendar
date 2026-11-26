const nav = document.querySelector(".sidebar nav");

const leerUsuario = () => {
    const guardado = sessionStorage.getItem("gemdy_usuario") ?? localStorage.getItem("gemdy_usuario");
    try {
        return guardado ? JSON.parse(guardado) : null;
    } catch {
        return null;
    }
};

const unir = (...partes) => partes.filter(Boolean).join(" ");

if (nav) {
    const boton = document.createElement("button");
    boton.type = "button";
    boton.className = "nav-link perfil-boton";
    boton.setAttribute("aria-haspopup", "dialog");
    const icono = document.createElement("span");
    icono.setAttribute("aria-hidden", "true");
    icono.textContent = "◉";
    boton.append(icono, "Perfil");

    const dialogo = document.createElement("dialog");
    dialogo.className = "perfil-dialogo";
    dialogo.setAttribute("aria-labelledby", "perfil-titulo");

    const titulo = document.createElement("h2");
    titulo.id = "perfil-titulo";
    titulo.textContent = "Mi perfil";

    const datos = document.createElement("dl");

    const mensaje = document.createElement("p");
    mensaje.className = "muted";

    const cerrar = document.createElement("button");
    cerrar.type = "button";
    cerrar.className = "button secondary";
    cerrar.textContent = "Cerrar";
    cerrar.addEventListener("click", () => dialogo.close());

    dialogo.append(titulo, datos, mensaje, cerrar);

    const agregarDato = (etiqueta, valor) => {
        if (!valor) return;
        const dt = document.createElement("dt");
        dt.textContent = etiqueta;
        const dd = document.createElement("dd");
        dd.textContent = valor;
        datos.append(dt, dd);
    };

    boton.addEventListener("click", () => {
        const usuario = leerUsuario();
        datos.replaceChildren();
        mensaje.textContent = "";

        if (usuario) {
            agregarDato("Nombre", unir(usuario.nombre1, usuario.nombre2, usuario.apellido1, usuario.apellido2) || usuario.nombre || usuario.name);
            agregarDato("Correo", usuario.correo || usuario.email);
            agregarDato("Teléfono", usuario.telefono);
            agregarDato("Rol", usuario.rol);
        }
        if (!datos.children.length) {
            mensaje.textContent = "No se encontraron datos del usuario. Vuelve a iniciar sesión.";
        }

        dialogo.showModal();
    });

    dialogo.addEventListener("click", (event) => {
        if (event.target === dialogo) dialogo.close();
    });

    nav.append(boton);
    document.body.append(dialogo);
}