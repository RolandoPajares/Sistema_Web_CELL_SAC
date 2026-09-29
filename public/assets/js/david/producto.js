

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

