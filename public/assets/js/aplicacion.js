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
addEventListener("scroll", () =>
  botonSubir?.classList.toggle("show", scrollY > 500),
);
botonSubir?.addEventListener("click", () =>
  scrollTo({ top: 0, behavior: "smooth" }),
);
document.querySelector(".menu-toggle")?.addEventListener("click", (evento) => {
  const navegacion = document.querySelector(".navlinks");
  const abierta = navegacion?.classList.toggle("open") ?? false;
  evento.currentTarget.setAttribute(
    "aria-expanded",
    abierta ? "true" : "false",
  );
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
document
  .querySelectorAll(".sidebar-nav a")
  .forEach((enlace) => enlace.addEventListener("click", cerrarBarraLateral));

document.querySelectorAll("[data-table-search]").forEach((entrada) => {
  entrada.addEventListener("input", () => {
    const tabla = entrada.closest(".panel")?.querySelector("tbody");
    const termino = entrada.value.trim().toLocaleLowerCase("es");
    tabla?.querySelectorAll("tr").forEach((fila) => {
      fila.hidden =
        termino !== "" &&
        !fila.textContent.toLocaleLowerCase("es").includes(termino);
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
  document.getElementById("compareIds").value = [...new Set(valores)]
    .slice(0, 3)
    .join(",");
});

// Publicidad dinámica: una entrada grande y un impacto por sección, con límite por sesión.
const sistemaPublicidad = document.getElementById("publicidadDinamica");
if (sistemaPublicidad) {
  const modal = sistemaPublicidad.querySelector(".js-publicidad-modal");
  const tarjetaContextual = sistemaPublicidad.querySelector(
    ".js-publicidad-contextual",
  );
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

  const guardarEstado = () =>
    sessionStorage.setItem(claveEstado, JSON.stringify(estado));
  const registrarCampania = (elemento, evento) => {
    const urlSeguimiento = elemento?.dataset.trackUrl;
    if (!urlSeguimiento) return;
    const csrf =
      document.querySelector('meta[name="csrf-token"]')?.content || "";
    fetch(urlSeguimiento, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      },
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
    if (estado.impactos >= limite || estado.contextos.includes(contexto))
      return;
    tarjetaContextual.hidden = false;
    estado.impactos += 1;
    estado.ultimoImpacto = Date.now();
    estado.contextos.push(contexto);
    guardarEstado();
  };

  modal
    .querySelectorAll(".js-publicidad-cerrar")
    .forEach((boton) => boton.addEventListener("click", cerrarModal));
  modal.addEventListener("click", (evento) => {
    if (evento.target === modal) cerrarModal();
  });
  modal
    .querySelector(".js-publicidad-accion")
    ?.addEventListener("click", () => {
      registrarCampania(modal.querySelector(".publicidad-modal"), "click");
    });
  tarjetaContextual
    .querySelector(".js-contextual-cerrar")
    ?.addEventListener("click", () => {
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
    const restante = Math.max(
      1200,
      espera - (Date.now() - estado.ultimoImpacto),
    );
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

  const escaparHtml = (valor) =>
    String(valor).replace(
      /[&<>'"]/g,
      (caracter) =>
        ({
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
    const entradas = [
      ...mensajes.querySelectorAll(".assistant-message[data-history='1']"),
    ].map((elemento) => ({
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
      const historial = JSON.parse(
        sessionStorage.getItem(claveHistorial) || "[]",
      );
      if (!Array.isArray(historial) || historial.length === 0) return;
      historial.forEach((entradaHistorial) =>
        agregarMensaje(
          (entradaHistorial.rol || entradaHistorial.role) === "user"
            ? "user"
            : "bot",
          entradaHistorial.contenido || entradaHistorial.html,
          false,
        ),
      );
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
      const respuestaHttp = await fetch(
        `${urlAsistente}?message=${encodeURIComponent(textoLimpio)}`,
        {
          headers: { Accept: "application/json" },
        },
      );
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
    boton.addEventListener("click", () =>
      preguntarAsistente(boton.dataset.assistantPrompt || ""),
    );
  });
  document.getElementById("assistantClear")?.addEventListener("click", () => {
    sessionStorage.removeItem(claveHistorial);
    mensajes.innerHTML =
      '<article class="assistant-message assistant-message-bot"><div class="assistant-message-avatar">MD</div><div class="assistant-bubble"><b>MD Assistant</b><p>Conversación reiniciada. ¿Qué celular, presupuesto o característica quieres consultar?</p></div></article>';
    entrada.focus();
  });
}


// Detalle de producto: variantes por color y galeria (archivo externo por CSP).
(() => {
  const root = document.querySelector('[data-product-detail]');
  if (!root) return;
  const main = root.querySelector('[data-main-product-image]');
  const placeholder = root.querySelector('[data-image-placeholder]');
  const thumbsBox = root.querySelector('[data-gallery-thumbs]');
  let visible = [];

  const setMain = (src, btn) => {
    if (src && main) { main.src = src; main.hidden = false; }
    if (src && placeholder) placeholder.hidden = true;
    root.querySelectorAll('.gallery-thumb').forEach(x => x.classList.remove('active'));
    if (btn) btn.classList.add('active');
  };

  const choose = (button) => {
    if (!button) return;
    const id = String(button.dataset.variantId || '');
    root.querySelectorAll('.color-option').forEach(x => x.classList.toggle('active', x === button));
    const colorLabel = root.querySelector('[data-selected-color]');
    const stockLabel = root.querySelector('[data-variant-stock]');
    const selectedVariant = root.querySelector('[data-selected-variant]');
    if (colorLabel) colorLabel.textContent = button.dataset.colorName || '';
    if (stockLabel) stockLabel.textContent = button.dataset.stock || '0';
    if (selectedVariant) selectedVariant.value = id;

    root.querySelectorAll('.gallery-thumb').forEach(x => {
      x.hidden = String(x.dataset.variant || '') !== id;
      x.classList.remove('active');
    });
    visible = [...root.querySelectorAll('.gallery-thumb')].filter(x => !x.hidden);
    if (visible.length) setMain(visible[0].dataset.src, visible[0]);
    else { if (main) main.hidden = true; if (placeholder) placeholder.hidden = false; }
  };

  root.querySelectorAll('.color-option').forEach(button => button.addEventListener('click', e => {
    e.preventDefault(); e.stopPropagation(); choose(button);
  }));
  thumbsBox?.addEventListener('click', e => {
    const b = e.target.closest('.gallery-thumb');
    if (b && !b.hidden) { e.preventDefault(); setMain(b.dataset.src, b); }
  });
  const step = n => {
    if (!visible.length) return;
    let i = visible.findIndex(x => x.classList.contains('active'));
    i = (i + n + visible.length) % visible.length;
    setMain(visible[i].dataset.src, visible[i]);
  };
  root.querySelector('[data-gallery-prev]')?.addEventListener('click', e => { e.preventDefault(); step(-1); });
  root.querySelector('[data-gallery-next]')?.addEventListener('click', e => { e.preventDefault(); step(1); });
  choose(root.querySelector('.color-option.active') || root.querySelector('.color-option'));
})();

// Dashboard interno: calendario y periodo de compras.
(() => {
  const date = document.querySelector('[data-dashboard-date]');
  const dateLabel = document.querySelector('[data-dashboard-date-label]');
  if (date) {
    const open = () => { if (typeof date.showPicker === 'function') date.showPicker(); else date.click(); };
    date.closest('.boton-fecha')?.addEventListener('click', (e) => { if (e.target !== date) { e.preventDefault(); open(); } });
    date.addEventListener('change', () => {
      if (!date.value || !dateLabel) return;
      const d = new Date(date.value + 'T12:00:00');
      dateLabel.textContent = d.toLocaleDateString('es-PE', { day:'2-digit', month:'short', year:'numeric' });
    });
  }
  document.querySelector('[data-dashboard-period]')?.addEventListener('change', (e) => {
    const u = new URL(window.location.href); u.searchParams.set('months', e.target.value); window.location.href = u.toString();
  });
})();

// Modulos internos: calendario y filtros funcionales.
(() => {
  document.querySelectorAll('[data-module-date]').forEach(input => {
    const label=input.closest('.boton-fecha'); const text=label?.querySelector('[data-module-date-label]');
    label?.addEventListener('click', e=>{ if(e.target!==input){e.preventDefault(); if(typeof input.showPicker==='function') input.showPicker(); else input.click();} });
    input.addEventListener('change',()=>{if(!input.value||!text)return; const d=new Date(input.value+'T12:00:00'); text.textContent=d.toLocaleDateString('es-PE',{day:'2-digit',month:'short',year:'numeric'});});
  });
  const filters=document.querySelector('[data-module-filters]'); const table=document.querySelector('[data-filter-table]');
  if(!filters||!table) return;
  const search=filters.querySelector('[data-filter-search]'), status=filters.querySelector('[data-filter-status]'), date=filters.querySelector('[data-filter-date]'), dateText=filters.querySelector('[data-filter-date-label]');
  filters.querySelector('.module-filter-date')?.addEventListener('click',e=>{if(e.target!==date){e.preventDefault(); if(typeof date.showPicker==='function')date.showPicker(); else date.click();}});
  date?.addEventListener('change',()=>{if(date.value&&dateText){const d=new Date(date.value+'T12:00:00');dateText.textContent=d.toLocaleDateString('es-PE',{day:'2-digit',month:'short',year:'numeric'});}});
  const apply=()=>{const q=(search?.value||'').trim().toLowerCase(), st=(status?.value||'').toLowerCase(); let shown=0; table.querySelectorAll('[data-filter-row]').forEach(row=>{const t=row.textContent.toLowerCase(); const ok=(!q||t.includes(q))&&(!st||t.includes(st)); row.hidden=!ok;if(ok)shown++;}); const empty=table.querySelector('[data-filter-empty]');if(empty)empty.hidden=shown!==0;};
  filters.querySelector('[data-apply-filters]')?.addEventListener('click',apply); search?.addEventListener('input',apply); status?.addEventListener('change',apply);
  table.querySelector('[data-close-detail]')?.addEventListener('click',()=>{const d=table.querySelector('.detalle-mockup');if(d)d.hidden=true;});
  table.querySelector('[data-export-table]')?.addEventListener('click',()=>{const rows=[...table.querySelectorAll('table tr')].filter(r=>!r.hidden).map(r=>[...r.children].map(c=>'"'+c.textContent.trim().replaceAll('"','""')+'"').join(',')); const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([rows.join('\n')],{type:'text/csv;charset=utf-8'}));a.download='reporte.csv';a.click();URL.revokeObjectURL(a.href);});
})();

// Alertas de reposición: muestra el proveedor vinculado al producto crítico.
(() => {
  const root=document.querySelector('.modulo-listado-real');
  const panel=root?.querySelector('[data-provider-panel]');
  if(!root||!panel) return;
  root.querySelectorAll('[data-provider-detail]').forEach(btn=>btn.addEventListener('click',()=>{
    const row=btn.closest('[data-record]'); let data={};
    try{data=JSON.parse(row?.dataset.record||'{}')}catch(_){data={}}
    panel.querySelector('[data-p-product]').textContent=data.producto||'—';
    panel.querySelector('[data-p-name]').textContent=data.proveedor||'Sin proveedor asignado';
    panel.querySelector('[data-p-email]').textContent=data.correo_proveedor||'—';
    panel.querySelector('[data-p-phone]').textContent=data.telefono_proveedor||'—';
    panel.hidden=false; panel.scrollIntoView({behavior:'smooth',block:'nearest'});
  }));
  panel.querySelector('[data-close-provider]')?.addEventListener('click',()=>panel.hidden=true);
})();
