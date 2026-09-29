(() => {
  const app = document.querySelector("[data-admin-app]");
  const sidebar = document.getElementById("adminSidebar");
  const backdrop = document.querySelector("[data-admin-sidebar-close]");
  const toggle = document.querySelector("[data-admin-sidebar-toggle]");
  const mobile = () => matchMedia("(max-width: 800px)").matches;

  const closeMobileSidebar = () => {
    sidebar?.classList.remove("is-open");
    backdrop?.classList.remove("is-visible");
    toggle?.setAttribute("aria-expanded", "false");
  };
  toggle?.addEventListener("click", () => {
    if (mobile()) {
      const open = sidebar?.classList.toggle("is-open") ?? false;
      backdrop?.classList.toggle("is-visible", open);
      toggle.setAttribute("aria-expanded", String(open));
      return;
    }
    const collapsed = app?.classList.toggle("is-sidebar-collapsed") ?? false;
    localStorage.setItem("md-admin-sidebar", collapsed ? "collapsed" : "expanded");
    toggle.setAttribute("aria-expanded", String(!collapsed));
  });
  backdrop?.addEventListener("click", closeMobileSidebar);
  sidebar?.querySelectorAll("a").forEach((link) => link.addEventListener("click", closeMobileSidebar));
  if (!mobile() && localStorage.getItem("md-admin-sidebar") === "collapsed") {
    app?.classList.add("is-sidebar-collapsed");
    toggle?.setAttribute("aria-expanded", "false");
  }

  document.querySelectorAll("[data-confirm]").forEach((element) => {
    element.addEventListener("click", (event) => {
      if (!confirm(element.dataset.confirm || "¿Confirmas esta acción?")) event.preventDefault();
    });
  });

  const filterRows = () => {
    const term = (document.querySelector("[data-admin-global-search]")?.value || "").trim().toLocaleLowerCase("es");
    document.querySelectorAll("[data-admin-table] tbody tr:not([data-product-filter-empty]):not([data-product-empty]):not([data-category-filter-empty]):not([data-category-empty]):not([data-inventory-filter-empty]):not([data-inventory-empty]):not([data-orders-filter-empty]):not([data-orders-empty]):not([data-client-filter-empty]):not([data-client-empty]):not([data-supplier-filter-empty]):not([data-supplier-empty]):not([data-campaign-filter-empty]):not([data-campaign-empty])").forEach((row) => {
      const container = row.closest("[data-admin-table-container]");
      const localInput = container?.querySelector("[data-table-search]");
      const categoryFilter = container?.querySelector("[data-product-category-filter]");
      const stateFilter = container?.querySelector("[data-product-state-filter]");
      const categoryStatusFilter = container?.querySelector("[data-category-status-filter]");
      const inventoryCategoryFilter = container?.querySelector("[data-inventory-category-filter]");
      const inventoryStateFilter = container?.querySelector("[data-inventory-state-filter]");
      const inventoryPeriodFilter = container?.querySelector("[data-inventory-period-filter]");
      const orderStatusFilter = container?.querySelector("[data-order-status-filter]");
      const orderPeriodFilter = container?.querySelector("[data-order-period-filter]");
      const clientTypeFilter = container?.querySelector("[data-client-type-filter]");
      const clientStateFilter = container?.querySelector("[data-client-state-filter]");
      const supplierStateFilter = container?.querySelector("[data-supplier-state-filter]");
      const supplierCityFilter = container?.querySelector("[data-supplier-city-filter]");
      const campaignStatusFilter = container?.querySelector("[data-campaign-status-filter]");
      const campaignLocationFilter = container?.querySelector("[data-campaign-location-filter]");
      const campaignPeriodFilter = container?.querySelector("[data-campaign-period-filter]");
      const local = (localInput?.value || "").trim().toLocaleLowerCase("es");
      const text = row.textContent.toLocaleLowerCase("es");
      const category = (categoryFilter?.value || "").toLocaleLowerCase("es");
      const state = stateFilter?.value || "";
      const categoryState = categoryStatusFilter?.value || "";
      const inventoryCategory = (inventoryCategoryFilter?.value || "").toLocaleLowerCase("es");
      const inventoryState = inventoryStateFilter?.value || "";
      const periodDays = inventoryPeriodFilter?.value || "all";
      const movementAge = Math.floor(Date.now() / 1000) - Number(row.dataset.inventoryTime || 0);
      const orderPeriodDays = orderPeriodFilter?.value || "all";
      const orderAge = Math.floor(Date.now() / 1000) - Number(row.dataset.orderTime || 0);
      const clientType = clientTypeFilter?.value || "";
      const clientState = clientStateFilter?.value || "";
      const supplierState = supplierStateFilter?.value || "";
      const supplierCity = supplierCityFilter?.value || "";
      const campaignStatus = campaignStatusFilter?.value || "";
      const campaignLocation = campaignLocationFilter?.value || "";
      const campaignPeriodDays = campaignPeriodFilter?.value || "all";
      const campaignAge = Math.floor(Date.now() / 1000) - Number(row.dataset.campaignTime || 0);
      row.hidden = (term !== "" && !text.includes(term))
        || (local !== "" && !text.includes(local))
        || (category !== "" && (row.dataset.productCategory || "").toLocaleLowerCase("es") !== category)
        || (state !== "" && row.dataset.productState !== state)
        || (categoryState !== "" && row.dataset.categoryStatus !== categoryState)
        || (inventoryCategory !== "" && (row.dataset.inventoryCategory || "").toLocaleLowerCase("es") !== inventoryCategory)
        || (inventoryState !== "" && row.dataset.inventoryState !== inventoryState)
        || (inventoryPeriodFilter && periodDays !== "all" && (movementAge < 0 || movementAge > Number(periodDays) * 86400))
        || (orderStatusFilter && orderStatusFilter.value !== "" && row.dataset.orderStatus !== orderStatusFilter.value)
        || (orderPeriodFilter && orderPeriodDays !== "all" && (orderAge < 0 || orderAge > Number(orderPeriodDays) * 86400))
        || (clientType !== "" && row.dataset.clientType !== clientType)
        || (clientState !== "" && row.dataset.clientState !== clientState)
        || (supplierState !== "" && row.dataset.supplierState !== supplierState)
        || (supplierCity !== "" && row.dataset.supplierCity !== supplierCity)
        || (campaignStatus !== "" && row.dataset.campaignStatus !== campaignStatus)
        || (campaignLocation !== "" && row.dataset.campaignLocation !== campaignLocation)
        || (campaignPeriodFilter && campaignPeriodDays !== "all" && (campaignAge < 0 || campaignAge > Number(campaignPeriodDays) * 86400));
    });
    document.querySelectorAll("[data-admin-table-container]").forEach((container) => {
      const emptyState = container.querySelector("[data-product-filter-empty], [data-category-filter-empty], [data-inventory-filter-empty], [data-orders-filter-empty], [data-client-filter-empty], [data-supplier-filter-empty], [data-campaign-filter-empty]");
      if (!emptyState) return;
      const productRows = [...container.querySelectorAll("[data-admin-table] tbody tr[data-data-row]")];
      emptyState.hidden = productRows.length === 0 || productRows.some((row) => !row.hidden);
      const order = container.querySelector("[data-category-order-filter]")?.value;
      if (order) {
        const body = container.querySelector("[data-admin-table] tbody");
        const rows = [...(body?.querySelectorAll("tr[data-data-row]") || [])];
        rows.sort((a, b) => order === "name-asc"
          ? (a.dataset.categoryName || "").localeCompare(b.dataset.categoryName || "", "es", { sensitivity: "base" })
          : (b.dataset.categoryCreated || "").localeCompare(a.dataset.categoryCreated || ""));
        rows.forEach((row) => body.appendChild(row));
      }
    });
    document.dispatchEvent(new CustomEvent("admin:table-filtered"));
  };
  document.querySelector("[data-admin-global-search]")?.addEventListener("input", filterRows);
  document.querySelectorAll("[data-table-search]").forEach((input) => input.addEventListener("input", filterRows));
  document.querySelectorAll("[data-product-category-filter], [data-product-state-filter]").forEach((filter) => filter.addEventListener("change", filterRows));
  document.querySelectorAll("[data-category-status-filter], [data-category-order-filter], [data-inventory-category-filter], [data-inventory-state-filter], [data-inventory-period-filter], [data-order-status-filter], [data-order-period-filter], [data-client-type-filter], [data-client-state-filter], [data-supplier-state-filter], [data-supplier-city-filter], [data-campaign-status-filter], [data-campaign-location-filter], [data-campaign-period-filter]").forEach((filter) => filter.addEventListener("change", filterRows));
  if (document.querySelector("[data-category-order-filter], [data-inventory-period-filter], [data-campaign-period-filter]")) filterRows();

  document.querySelectorAll("[data-campaign-preview-select]").forEach((button) => button.addEventListener("click", () => {
    const textTargets = [
      ["[data-campaign-preview-name]", "previewName"],
      ["[data-campaign-preview-description]", "previewDescription"],
      ["[data-campaign-preview-location]", "previewLocation"],
      ["[data-campaign-preview-status]", "previewStatus"],
    ];
    textTargets.forEach(([selector, key]) => {
      const target = document.querySelector(selector);
      if (target) target.textContent = button.dataset[key] || "—";
    });
    const start = document.querySelector("[data-campaign-preview-start]");
    const end = document.querySelector("[data-campaign-preview-end]");
    if (start) { start.textContent = button.dataset.previewStart || "—"; start.dateTime = button.dataset.previewStartIso || ""; }
    if (end) { end.textContent = button.dataset.previewEnd || "—"; end.dateTime = button.dataset.previewEndIso || ""; }
    const status = document.querySelector("[data-campaign-preview-status]");
    if (status) {
      ["active", "scheduled", "finished", "inactive"].forEach((key) => status.classList.remove(`admin-campaign-status--${key}`));
      status.classList.add(`admin-campaign-status--${button.dataset.previewStatusKey || "inactive"}`);
    }
    const image = document.querySelector("[data-campaign-preview-image]");
    const placeholder = document.querySelector("[data-campaign-image-placeholder]");
    if (image) {
      const source = button.dataset.previewImage || "";
      image.hidden = source === "";
      if (source !== "") { image.src = source; image.alt = `Imagen de ${button.dataset.previewTitle || button.dataset.previewName || "la campaña"}`; }
      else image.removeAttribute("src");
      if (placeholder) placeholder.hidden = source !== "";
    }
    const editLink = document.querySelector("[data-campaign-edit-link]");
    if (editLink && button.dataset.previewEdit) editLink.href = button.dataset.previewEdit;
  }));

  document.querySelectorAll("[data-admin-dialog-open]").forEach((button) => {
    button.addEventListener("click", () => document.getElementById(button.dataset.adminDialogOpen)?.showModal());
  });
  document.querySelectorAll("[data-audit-id]").forEach((button) => button.addEventListener("click", () => {
    const values = {
      id: `#${button.dataset.auditId || "—"}`,
      date: button.dataset.auditDate || "—",
      user: button.dataset.auditUser || "—",
      action: button.dataset.auditAction || "—",
      entity: button.dataset.auditEntity || "—",
      ip: button.dataset.auditIp || "—",
    };
    Object.entries(values).forEach(([key, value]) => {
      const target = document.querySelector(`[data-audit-detail="${key}"]`);
      if (target) target.textContent = value;
    });
    ["old", "new"].forEach((key) => {
      const target = document.querySelector(`[data-audit-detail="${key}"]`);
      if (!target) return;
      try {
        const parsed = JSON.parse(button.dataset[`audit${key[0].toUpperCase()}${key.slice(1)}`] || "{}");
        target.textContent = parsed && Object.keys(parsed).length ? JSON.stringify(parsed, null, 2) : "Sin datos";
      } catch (_) {
        target.textContent = "Sin datos disponibles";
      }
    });
  }));
  document.querySelectorAll("[data-admin-dialog-close]").forEach((button) => {
    button.addEventListener("click", () => button.closest("dialog")?.close());
  });
  document.querySelectorAll("dialog[data-auto-open='1']").forEach((dialog) => dialog.showModal());
  document.querySelectorAll("dialog").forEach((dialog) => dialog.addEventListener("click", (event) => {
    if (event.target === dialog) dialog.close();
  }));

  document.querySelectorAll("[data-admin-table]").forEach((table) => {
    const rows = [...table.querySelectorAll("tbody tr[data-data-row]")];
    const container = table.closest("[data-admin-table-container]");
    const pager = container?.querySelector("[data-admin-pagination]");
    if (!pager || rows.length <= 10) return;
    let page = 1;
    const perPage = 10;
    const orderedRows = () => {
      const order = container?.querySelector("[data-category-order-filter]")?.value;
      if (!order) return rows;
      return [...rows].sort((a, b) => order === "name-asc"
        ? (a.dataset.categoryName || "").localeCompare(b.dataset.categoryName || "", "es", { sensitivity: "base" })
        : (b.dataset.categoryCreated || "").localeCompare(a.dataset.categoryCreated || ""));
    };
    const render = () => {
      const visible = orderedRows().filter((row) => !row.hidden);
      const pages = Math.max(1, Math.ceil(visible.length / perPage));
      page = Math.min(page, pages);
      rows.forEach((row) => { if (!row.hidden) row.style.display = "none"; });
      visible.slice((page - 1) * perPage, page * perPage).forEach((row) => { row.style.display = ""; });
      pager.innerHTML = `<span>Mostrando ${visible.length ? (page - 1) * perPage + 1 : 0}-${Math.min(page * perPage, visible.length)} de ${visible.length}</span><div><button type="button" data-prev aria-label="Página anterior">‹</button><b>${page} / ${pages}</b><button type="button" data-next aria-label="Página siguiente">›</button></div>`;
      pager.querySelector("[data-prev]").disabled = page === 1;
      pager.querySelector("[data-next]").disabled = page === pages;
      pager.querySelector("[data-prev]").onclick = () => { page -= 1; render(); };
      pager.querySelector("[data-next]").onclick = () => { page += 1; render(); };
    };
    document.addEventListener("admin:table-filtered", () => { page = 1; render(); });
    render();
  });

  const assistant = document.querySelector("[data-admin-assistant]");
  if (assistant) {
    const panel = assistant.querySelector("[data-admin-assistant-panel]");
    const messages = assistant.querySelector("[data-admin-assistant-messages]");
    const form = assistant.querySelector("[data-admin-assistant-form]");
    const input = form?.querySelector("input");
    const setOpen = (open) => { panel.hidden = !open; };
    assistant.querySelector("[data-admin-assistant-toggle]")?.addEventListener("click", () => setOpen(panel.hidden));
    assistant.querySelector("[data-admin-assistant-close]")?.addEventListener("click", () => setOpen(false));
    const append = (message, own = false) => {
      const article = document.createElement("article");
      article.className = own ? "is-user" : "";
      const icon = own ? "bi-person" : "bi-robot";
      const safe = document.createElement("p");
      safe.textContent = message;
      article.innerHTML = `<span><i class="bi ${icon}"></i></span>`;
      article.appendChild(safe);
      messages.appendChild(article);
      messages.scrollTop = messages.scrollHeight;
    };
    const ask = async (question) => {
      const query = question.trim();
      if (!query) return;
      setOpen(true);
      append(query, true);
      input.value = "";
      input.disabled = true;
      try {
        const response = await fetch(assistant.dataset.endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8", "Accept": "application/json" },
          body: new URLSearchParams({ consulta: query, csrf: document.querySelector('meta[name="csrf-token"]')?.content || "" }),
        });
        const data = await response.json();
        append(data.ok ? data.respuesta.mensaje : data.mensaje);
      } catch (_) {
        append("No fue posible consultar la información en este momento.");
      } finally {
        input.disabled = false;
        input.focus();
      }
    };
    form?.addEventListener("submit", (event) => { event.preventDefault(); ask(input.value); });
    assistant.querySelectorAll("[data-admin-assistant-prompt]").forEach((button) => button.addEventListener("click", () => ask(button.dataset.adminAssistantPrompt || "")));
  }

  document.querySelectorAll("[data-password-toggle]").forEach((button) => {
    const input = button.closest(".admin-password-input")?.querySelector("input");
    if (!input) return;
    button.addEventListener("click", () => {
      const show = input.type === "password";
      input.type = show ? "text" : "password";
      button.setAttribute("aria-label", show ? "Ocultar contraseña" : "Mostrar contraseña");
      button.innerHTML = `<i class="bi ${show ? "bi-eye-slash" : "bi-eye"}" aria-hidden="true"></i>`;
    });
  });
})();
