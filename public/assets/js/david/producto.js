

// Galería del detalle individual; usa únicamente las imágenes registradas en el producto.
(() => {
  const root = document.querySelector('[data-product-detail]');
  if (!root) return;
  const main = root.querySelector('[data-main-product-image]');
  const placeholder = root.querySelector('[data-image-placeholder]');
  const thumbsBox = root.querySelector('[data-gallery-thumbs]');
  let visible = [...root.querySelectorAll('.gallery-thumb')].filter(button => !button.hidden);

  const setMain = (src, btn) => {
    if (src && main) {
      main.src = src;
      main.hidden = false;
      if (placeholder) placeholder.hidden = true;
    }
    root.querySelectorAll('.gallery-thumb').forEach(x => x.classList.remove('active'));
    if (btn) btn.classList.add('active');
  };

  const updateArrows = () => {
    const show = visible.length > 1;
    const previous = root.querySelector('[data-gallery-prev]');
    const next = root.querySelector('[data-gallery-next]');
    if (previous) previous.hidden = !show;
    if (next) next.hidden = !show;
  };

  thumbsBox?.addEventListener('click', e => {
    const b = e.target.closest('.gallery-thumb');
    if (b && !b.hidden) {
      e.preventDefault();
      visible = [...root.querySelectorAll('.gallery-thumb')].filter(button => !button.hidden);
      setMain(b.dataset.src, b);
    }
  });
  const step = n => {
    if (!visible.length) return;
    let i = visible.findIndex(x => x.classList.contains('active'));
    i = (i + n + visible.length) % visible.length;
    setMain(visible[i].dataset.src, visible[i]);
  };
  root.querySelector('[data-gallery-prev]')?.addEventListener('click', e => { e.preventDefault(); step(-1); });
  root.querySelector('[data-gallery-next]')?.addEventListener('click', e => { e.preventDefault(); step(1); });

  const hideUnavailableThumb = image => {
    image.closest('.gallery-thumb')?.remove();
    visible = [...root.querySelectorAll('.gallery-thumb')].filter(button => !button.hidden);
    updateArrows();
  };
  thumbsBox?.querySelectorAll('.gallery-thumb img').forEach(image => {
    image.addEventListener('error', () => hideUnavailableThumb(image), { once: true });
    if (image.complete && image.naturalWidth === 0) hideUnavailableThumb(image);
  });
  const showPlaceholder = () => {
    main.hidden = true;
    if (placeholder) placeholder.hidden = false;
    if (thumbsBox) thumbsBox.hidden = true;
  };
  main?.addEventListener('error', showPlaceholder, { once: true });
  if (main?.getAttribute('src') && main.complete && main.naturalWidth === 0) showPlaceholder();

  if (visible.length && main) setMain(visible[0].dataset.src, visible[0]);
  else if (main) main.hidden = true;
  if (!visible.length && placeholder) placeholder.hidden = false;
  updateArrows();
})();

// Modal y galería del catálogo público. Se activa solo en /catalog.
(() => {
  const modal = document.querySelector('[data-catalog-modal]');
  if (!modal || typeof modal.showModal !== 'function') return;

  const mainImage = modal.querySelector('[data-modal-main-image]');
  const imagePlaceholder = modal.querySelector('[data-modal-image-placeholder]');
  const thumbnails = modal.querySelector('[data-modal-thumbnails]');
  const previous = modal.querySelector('[data-modal-prev]');
  const next = modal.querySelector('[data-modal-next]');
  const zoomButton = modal.querySelector('[data-modal-zoom]');
  const openers = document.querySelectorAll('[data-open-product]');
  let images = [];
  let selectedImage = 0;
  let activeOpener = null;

  const text = (selector, value) => {
    const element = modal.querySelector(selector);
    if (element) element.textContent = value || '';
    return element;
  };

  const setImage = (index) => {
    if (!images.length || !mainImage) return;
    selectedImage = (index + images.length) % images.length;
    mainImage.src = images[selectedImage];
    const brand = modal.querySelector('[data-modal-brand]')?.textContent || '';
    const name = modal.querySelector('[data-modal-name]')?.textContent || '';
    mainImage.alt = `${brand} ${name}`.trim();
    mainImage.hidden = false;
    if (imagePlaceholder) imagePlaceholder.hidden = true;
    if (zoomButton) zoomButton.classList.remove('is-zoomed');

    if (thumbnails) {
      thumbnails.querySelectorAll('button').forEach((button, index) => {
        button.classList.toggle('is-active', index === selectedImage);
        button.setAttribute('aria-current', index === selectedImage ? 'true' : 'false');
      });
    }
  };

  const buildGallery = (values) => {
    images = Array.isArray(values)
      ? values.map((value) => String(value || '').trim()).filter((value) => {
          if (!value) return false;
          try {
            const protocol = new URL(value, document.baseURI).protocol;
            return protocol === 'http:' || protocol === 'https:';
          } catch {
            return false;
          }
        })
      : [];
    selectedImage = 0;
    if (zoomButton) zoomButton.classList.remove('is-zoomed');

    if (thumbnails) {
      thumbnails.replaceChildren();
      thumbnails.hidden = images.length < 2;
      images.forEach((source, index) => {
        const button = document.createElement('button');
        const image = document.createElement('img');
        button.type = 'button';
        button.className = 'catalog-modal-thumb';
        button.setAttribute('aria-label', `Ver imagen ${index + 1}`);
        button.addEventListener('click', () => setImage(index));
        image.src = source;
        image.alt = '';
        button.append(image);
        thumbnails.append(button);
      });
    }

    const hasGallery = images.length > 1;
    if (previous) previous.hidden = !hasGallery;
    if (next) next.hidden = !hasGallery;
    if (images.length) {
      setImage(0);
    } else {
      if (mainImage) {
        mainImage.removeAttribute('src');
        mainImage.hidden = true;
      }
      if (imagePlaceholder) imagePlaceholder.hidden = false;
    }
  };

  openers.forEach((opener) => opener.addEventListener('click', () => {
    let product;
    try {
      product = JSON.parse(opener.dataset.productDetails || '{}');
    } catch {
      return;
    }
    if (!product || !product.id) return;

    activeOpener = opener;
    text('[data-modal-brand]', product.marca);
    text('[data-modal-name]', product.nombre);
    text('[data-modal-price]', product.precio);

    const description = text('[data-modal-description]', product.descripcion);
    if (description) description.hidden = !String(product.descripcion || '').trim();

    const colorSection = modal.querySelector('[data-modal-color-section]');
    text('[data-modal-color]', product.color);
    if (colorSection) colorSection.hidden = !String(product.color || '').trim();

    const storageSection = modal.querySelector('[data-modal-storage-section]');
    text('[data-modal-storage]', product.almacenamiento);
    if (storageSection) storageSection.hidden = !String(product.almacenamiento || '').trim();

    const cartId = modal.querySelector('[data-modal-cart-id]');
    if (cartId) cartId.value = String(product.id);
    buildGallery(product.imagenes);
    modal.showModal();
  }));

  modal.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => modal.close());
  });
  modal.addEventListener('click', (event) => {
    if (event.target === modal) modal.close();
  });
  modal.addEventListener('close', () => {
    if (activeOpener) activeOpener.focus({ preventScroll: true });
  });
  previous?.addEventListener('click', () => setImage(selectedImage - 1));
  next?.addEventListener('click', () => setImage(selectedImage + 1));
  zoomButton?.addEventListener('click', () => {
    if (mainImage && !mainImage.hidden) zoomButton.classList.toggle('is-zoomed');
  });
  modal.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft' && images.length > 1) setImage(selectedImage - 1);
    if (event.key === 'ArrowRight' && images.length > 1) setImage(selectedImage + 1);
  });

  const sort = document.querySelector('[data-catalog-sort]');
  sort?.addEventListener('change', () => sort.form?.requestSubmit());
})();

