const cuerpoPagina = document.body;
const temaGuardado = localStorage.getItem("md-theme");

if (temaGuardado === "dark") {
    cuerpoPagina.classList.add("dark");
}

document.getElementById("themeToggle")?.addEventListener("click", () => {
    cuerpoPagina.classList.toggle("dark");
    localStorage.setItem(
        "md-theme",
        cuerpoPagina.classList.contains("dark") ? "dark" : "light",
    );
});

const botonSubir = document.getElementById("toTop");
addEventListener("scroll", () => botonSubir?.classList.toggle("show", scrollY > 500));
botonSubir?.addEventListener("click", () => scrollTo({ top: 0, behavior: "smooth" }));
document.querySelector(".menu-toggle")?.addEventListener("click", (evento) => {
    const navegacion = document.querySelector(".navlinks");
    const abierta = navegacion?.classList.toggle("open") ?? false;
    evento.currentTarget.setAttribute("aria-expanded", abierta ? "true" : "false");
});
document.querySelectorAll("[data-confirm]").forEach((elemento) => {
    elemento.addEventListener("click", (evento) => {
        if (!confirm(elemento.dataset.confirm)) {
            evento.preventDefault();
        }
    });
});

const barraLateral = document.getElementById("adminSidebar");
const fondoBarraLateral = document.querySelector("[data-sidebar-close]");
const botonMenuPanel = document.querySelector(".admin-menu-toggle");
const cerrarBarraLateral = () => {
    barraLateral?.classList.remove("open");
    fondoBarraLateral?.classList.remove("show");
    botonMenuPanel?.setAttribute("aria-expanded", "false");
};
botonMenuPanel?.addEventListener("click", () => {
    const abierta = barraLateral?.classList.toggle("open") ?? false;
    fondoBarraLateral?.classList.toggle("show", abierta);
    botonMenuPanel.setAttribute("aria-expanded", abierta ? "true" : "false");
});
fondoBarraLateral?.addEventListener("click", cerrarBarraLateral);
document.querySelectorAll(".sidebar-nav a").forEach((enlace) => enlace.addEventListener("click", cerrarBarraLateral));

document.querySelectorAll("[data-table-search]").forEach((entrada) => {
    entrada.addEventListener("input", () => {
        const tabla = entrada.closest(".panel")?.querySelector("tbody");
        const termino = entrada.value.trim().toLocaleLowerCase("es");
        tabla?.querySelectorAll("tr").forEach((fila) => {
            fila.hidden = termino !== "" && !fila.textContent.toLocaleLowerCase("es").includes(termino);
        });
    });
});

const rutaBase = (() => {
    const rutaActual = location.pathname;
    const indicePublico = rutaActual.indexOf("/public/");
    if (indicePublico >= 0) return rutaActual.slice(0, indicePublico + 8);
    return rutaActual.endsWith("/public") ? rutaActual + "/" : "/";
})();

document.getElementById("compareForm")?.addEventListener("submit", () => {
    const valores = [...document.querySelectorAll(".compare-select")]
        .map((elemento) => elemento.value)
        .filter(Boolean);
    document.getElementById("compareIds").value = [...new Set(valores)].slice(0, 3).join(",");
});

// Publicidad dinámica: una entrada grande y un impacto por sección, con límite por sesión.
const sistemaPublicidad = document.getElementById("publicidadDinamica");
if (sistemaPublicidad) {
    const modal = sistemaPublicidad.querySelector(".js-publicidad-modal");
    const tarjetaContextual = sistemaPublicidad.querySelector(".js-publicidad-contextual");
    const lanzador = sistemaPublicidad.querySelector(".js-publicidad-lanzador");
    const contexto = sistemaPublicidad.dataset.contexto || "general";
    const perfil = sistemaPublicidad.dataset.perfil || "visitante";
    const limite = Number(sistemaPublicidad.dataset.limite || 4);
    const espera = Number(sistemaPublicidad.dataset.espera || 12000);
    const claveEstado = `md-publicidad-v4-${perfil}`;
    let estado;

    try {
        estado = JSON.parse(sessionStorage.getItem(claveEstado) || "{}");
    } catch {
        estado = {};
    }
    estado = {
        entradaMostrada: Boolean(estado.entradaMostrada),
        impactos: Number(estado.impactos || 0),
        contextos: Array.isArray(estado.contextos) ? estado.contextos : [],
        ultimoImpacto: Number(estado.ultimoImpacto || 0),
    };

    const guardarEstado = () => sessionStorage.setItem(claveEstado, JSON.stringify(estado));
    const registrarCampania = (elemento, evento) => {
        const urlSeguimiento = elemento?.dataset.trackUrl;
        if (!urlSeguimiento) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
        fetch(urlSeguimiento, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
            body: new URLSearchParams({ event: evento, csrf }),
            keepalive: true,
        }).catch(() => {});
    };
    const cerrarModal = () => {
        modal.hidden = true;
        document.body.classList.remove("body-publicidad-abierta");
    };
    const mostrarModal = (contabilizar = true) => {
        tarjetaContextual.hidden = true;
        modal.hidden = false;
        document.body.classList.add("body-publicidad-abierta");
        if (contabilizar) {
            estado.entradaMostrada = true;
            estado.impactos += 1;
            estado.ultimoImpacto = Date.now();
            estado.contextos = [...new Set([...estado.contextos, contexto])];
            guardarEstado();
            registrarCampania(modal.querySelector(".publicidad-modal"), "view");
        }
    };
    const mostrarContextual = () => {
        if (estado.impactos >= limite || estado.contextos.includes(contexto)) return;
        tarjetaContextual.hidden = false;
        estado.impactos += 1;
        estado.ultimoImpacto = Date.now();
        estado.contextos.push(contexto);
        guardarEstado();
    };

    modal.querySelectorAll(".js-publicidad-cerrar").forEach((boton) => boton.addEventListener("click", cerrarModal));
    modal.addEventListener("click", (evento) => {
        if (evento.target === modal) cerrarModal();
    });
    modal.querySelector(".js-publicidad-accion")?.addEventListener("click", () => {
        registrarCampania(modal.querySelector(".publicidad-modal"), "click");
    });
    tarjetaContextual.querySelector(".js-contextual-cerrar")?.addEventListener("click", () => {
        tarjetaContextual.hidden = true;
    });
    lanzador?.addEventListener("click", () => mostrarModal(false));
    document.addEventListener("keydown", (evento) => {
        if (evento.key === "Escape" && !modal.hidden) cerrarModal();
    });

    if (contexto === "inicio") {
        tarjetaContextual.hidden = true;
        modal.hidden = true;
    } else if (!estado.entradaMostrada && estado.impactos < limite) {
        window.setTimeout(() => mostrarModal(true), 900);
    } else if (!estado.contextos.includes(contexto) && estado.impactos < limite) {
        const restante = Math.max(1200, espera - (Date.now() - estado.ultimoImpacto));
        window.setTimeout(mostrarContextual, restante);
    }
}

// Mantiene la conversación con MD Assistant sin recargar la página.
const chatAsistente = document.getElementById("assistantChat");
if (chatAsistente) {
    const formulario = document.getElementById("assistantForm");
    const entrada = document.getElementById("assistantInput");
    const mensajes = document.getElementById("assistantMessages");
    const botonEnviar = document.getElementById("assistantSend");
    const urlAsistente = chatAsistente.dataset.endpoint;
    const claveHistorial = "md-assistant-history-v1";

    const escaparHtml = (valor) => String(valor).replace(
        /[&<>'"]/g,
        (caracter) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            "'": "&#39;",
            '"': "&quot;",
        })[caracter],
    );
    const desplazarChat = () => {
        mensajes.scrollTop = mensajes.scrollHeight;
    };
    const guardarHistorial = () => {
        const entradas = [...mensajes.querySelectorAll(".assistant-message[data-history='1']")].map((elemento) => ({
            rol: elemento.dataset.role,
            contenido: elemento.querySelector(".assistant-bubble")?.innerHTML || "",
        }));
        sessionStorage.setItem(claveHistorial, JSON.stringify(entradas.slice(-20)));
    };
    const agregarMensaje = (rol, contenidoHtml, guardar = true) => {
        const articulo = document.createElement("article");
        articulo.className = `assistant-message assistant-message-${rol}`;
        articulo.dataset.history = guardar ? "1" : "0";
        articulo.dataset.role = rol;
        articulo.innerHTML = `<div class="assistant-message-avatar">${rol === "user" ? "TÚ" : "MD"}</div><div class="assistant-bubble">${contenidoHtml}</div>`;
        mensajes.appendChild(articulo);
        desplazarChat();
        if (guardar) guardarHistorial();
        return articulo;
    };
    const renderizarProductos = (productos) => {
        if (!Array.isArray(productos) || productos.length === 0) return "";
        return `<div class="assistant-chat-products">${productos.map((producto) => `<a class="assistant-chat-product" href="${rutaBase}products/${Number(producto.id)}"><strong>${escaparHtml((producto.marca || "") + " " + (producto.nombre || ""))}</strong><span>S/ ${Number(producto.precio || 0).toFixed(2)} · ${Number(producto.coincidencia || 0)}% coincidencia</span></a>`).join("")}</div>`;
    };
    const restaurarHistorial = () => {
        try {
            const historial = JSON.parse(sessionStorage.getItem(claveHistorial) || "[]");
            if (!Array.isArray(historial) || historial.length === 0) return;
            historial.forEach((entradaHistorial) => agregarMensaje(
                (entradaHistorial.rol || entradaHistorial.role) === "user" ? "user" : "bot",
                entradaHistorial.contenido || entradaHistorial.html,
                false,
            ));
        } catch (_) {}
    };
    restaurarHistorial();

    const preguntarAsistente = async (pregunta) => {
        const textoLimpio = pregunta.trim();
        if (!textoLimpio) return;
        agregarMensaje("user", `<p>${escaparHtml(textoLimpio)}</p>`);
        entrada.value = "";
        entrada.disabled = true;
        botonEnviar.disabled = true;
        const indicadorEscritura = agregarMensaje(
            "bot",
            '<div class="assistant-typing"><i></i><i></i><i></i></div>',
            false,
        );
        try {
            const respuestaHttp = await fetch(`${urlAsistente}?message=${encodeURIComponent(textoLimpio)}`, {
                headers: { "Accept": "application/json" },
            });
            const datos = await respuestaHttp.json();
            indicadorEscritura.remove();
            if (!respuestaHttp.ok || !datos.ok) {
                throw new Error(datos.mensaje || "No pude procesar la consulta.");
            }
            const respuesta = datos.respuesta || {};
            agregarMensaje(
                "bot",
                `<b>MD Assistant</b><p>${escaparHtml(respuesta.mensaje || "Aquí estoy para ayudarte.")}</p>${renderizarProductos(respuesta.productos || [])}`,
            );
        } catch (error) {
            indicadorEscritura.remove();
            agregarMensaje(
                "bot",
                '<b>MD Assistant</b><p class="assistant-error">No pude responder en este momento. Verifica que Apache y la aplicación estén activos e inténtalo nuevamente.</p>',
            );
        } finally {
            entrada.disabled = false;
            botonEnviar.disabled = false;
            entrada.focus();
        }
    };

    formulario?.addEventListener("submit", (evento) => {
        evento.preventDefault();
        preguntarAsistente(entrada.value);
    });
    document.querySelectorAll("[data-assistant-prompt]").forEach((boton) => {
        boton.addEventListener("click", () => preguntarAsistente(boton.dataset.assistantPrompt || ""));
    });
    document.getElementById("assistantClear")?.addEventListener("click", () => {
        sessionStorage.removeItem(claveHistorial);
        mensajes.innerHTML = '<article class="assistant-message assistant-message-bot"><div class="assistant-message-avatar">MD</div><div class="assistant-bubble"><b>MD Assistant</b><p>Conversación reiniciada. ¿Qué celular, presupuesto o característica quieres consultar?</p></div></article>';
        entrada.focus();
    });
}
