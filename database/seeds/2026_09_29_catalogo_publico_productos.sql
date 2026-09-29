-- Importa el catálogo entregado. stock inicial: 15 unidades por producto, según la tarjeta de referencia.
INSERT INTO productos (marca, nombre, categoria_id, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo, precio_original, descuento, precio_oferta, url_imagen)
SELECT seed.marca, seed.nombre, c.id, c.nombre, seed.precio_oferta, 15, NULL, NULL, 'Oferta',
       CONCAT(seed.nombre, ' con descuento promocional ', seed.descuento), 1,
       seed.precio_original, seed.descuento, seed.precio_oferta, seed.url_imagen
FROM (
    SELECT 'Apple' AS marca, 'iPhone 15 Pro Max' AS nombre, 'Celulares' AS categoria, 5899.00 AS precio_original, '-20%' AS descuento, 4719.20 AS precio_oferta, '/assets/images/productos/apple-iphone-15-pro-max.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'iPhone 15' AS nombre, 'Celulares' AS categoria, 3499.00 AS precio_original, '-20%' AS descuento, 2799.20 AS precio_oferta, '/assets/images/productos/apple-iphone-15.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'iPhone 14' AS nombre, 'Celulares' AS categoria, 2799.00 AS precio_original, '-20%' AS descuento, 2239.20 AS precio_oferta, '/assets/images/productos/apple-iphone-14.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'iPhone 13' AS nombre, 'Celulares' AS categoria, 2399.00 AS precio_original, '-20%' AS descuento, 1919.20 AS precio_oferta, '/assets/images/productos/apple-iphone-13.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy S24 Ultra' AS nombre, 'Celulares' AS categoria, 4999.00 AS precio_original, '-20%' AS descuento, 3999.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-s24-ultra.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy S24 Plus' AS nombre, 'Celulares' AS categoria, 3899.00 AS precio_original, '-20%' AS descuento, 3119.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-s24-plus.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy S24' AS nombre, 'Celulares' AS categoria, 3199.00 AS precio_original, '-20%' AS descuento, 2559.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-s24.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy S23 FE' AS nombre, 'Celulares' AS categoria, 2199.00 AS precio_original, '-20%' AS descuento, 1759.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-s23-fe.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy A55' AS nombre, 'Celulares' AS categoria, 1599.00 AS precio_original, '-20%' AS descuento, 1279.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-a55.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy A35' AS nombre, 'Celulares' AS categoria, 1199.00 AS precio_original, '-20%' AS descuento, 959.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-a35.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Redmi Note 13 Pro+' AS nombre, 'Celulares' AS categoria, 1799.00 AS precio_original, '-20%' AS descuento, 1439.20 AS precio_oferta, '/assets/images/productos/xiaomi-redmi-note-13-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Redmi Note 13 Pro' AS nombre, 'Celulares' AS categoria, 1149.00 AS precio_original, '-20%' AS descuento, 919.20 AS precio_oferta, '/assets/images/productos/xiaomi-redmi-note-13-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Xiaomi 14' AS nombre, 'Celulares' AS categoria, 3599.00 AS precio_original, '-20%' AS descuento, 2879.20 AS precio_oferta, '/assets/images/productos/xiaomi-xiaomi-14.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Poco X6 Pro' AS nombre, 'Celulares' AS categoria, 1399.00 AS precio_original, '-20%' AS descuento, 1119.20 AS precio_oferta, '/assets/images/productos/xiaomi-poco-x6-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Motorola' AS marca, 'Edge 50 Ultra' AS nombre, 'Celulares' AS categoria, 3299.00 AS precio_original, '-20%' AS descuento, 2639.20 AS precio_oferta, '/assets/images/productos/motorola-edge-50-ultra.jpg' AS url_imagen
    UNION ALL
    SELECT 'Motorola' AS marca, 'Edge 50 Pro' AS nombre, 'Celulares' AS categoria, 2499.00 AS precio_original, '-20%' AS descuento, 1999.20 AS precio_oferta, '/assets/images/productos/motorola-edge-50-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Motorola' AS marca, 'Moto G85' AS nombre, 'Celulares' AS categoria, 1099.00 AS precio_original, '-20%' AS descuento, 879.20 AS precio_oferta, '/assets/images/productos/motorola-moto-g85.jpg' AS url_imagen
    UNION ALL
    SELECT 'Honor' AS marca, 'Honor 200 Pro' AS nombre, 'Celulares' AS categoria, 2699.00 AS precio_original, '-20%' AS descuento, 2159.20 AS precio_oferta, '/assets/images/productos/honor-honor-200-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Honor' AS marca, 'Honor 200 Lite' AS nombre, 'Celulares' AS categoria, 1299.00 AS precio_original, '-20%' AS descuento, 1039.20 AS precio_oferta, '/assets/images/productos/honor-honor-200-lite.jpg' AS url_imagen
    UNION ALL
    SELECT 'Honor' AS marca, 'Honor X9b' AS nombre, 'Celulares' AS categoria, 1399.00 AS precio_original, '-20%' AS descuento, 1119.20 AS precio_oferta, '/assets/images/productos/honor-honor-x9b.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'AirPods Pro (2da Gen)' AS nombre, 'Audio' AS categoria, 1099.00 AS precio_original, '-20%' AS descuento, 879.20 AS precio_oferta, '/assets/images/productos/apple-airpods-pro-2da-gen.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'AirPods (3ra Gen)' AS nombre, 'Audio' AS categoria, 799.00 AS precio_original, '-20%' AS descuento, 639.20 AS precio_oferta, '/assets/images/productos/apple-airpods-3ra-gen.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy Buds3 Pro' AS nombre, 'Audio' AS categoria, 899.00 AS precio_original, '-20%' AS descuento, 719.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-buds3-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Galaxy Buds FE' AS nombre, 'Audio' AS categoria, 349.00 AS precio_original, '-20%' AS descuento, 279.20 AS precio_oferta, '/assets/images/productos/samsung-galaxy-buds-fe.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Redmi Buds 5 Pro' AS nombre, 'Audio' AS categoria, 299.00 AS precio_original, '-20%' AS descuento, 239.20 AS precio_oferta, '/assets/images/productos/xiaomi-redmi-buds-5-pro.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Redmi Buds 5' AS nombre, 'Audio' AS categoria, 129.00 AS precio_original, '-20%' AS descuento, 103.20 AS precio_oferta, '/assets/images/productos/xiaomi-redmi-buds-5.jpg' AS url_imagen
    UNION ALL
    SELECT 'Sony' AS marca, 'WH-1000XM5 ANC' AS nombre, 'Audio' AS categoria, 1499.00 AS precio_original, '-20%' AS descuento, 1199.20 AS precio_oferta, '/assets/images/productos/sony-wh-1000xm5-anc.jpg' AS url_imagen
    UNION ALL
    SELECT 'Sony' AS marca, 'WH-CH720N' AS nombre, 'Audio' AS categoria, 449.00 AS precio_original, '-20%' AS descuento, 359.20 AS precio_oferta, '/assets/images/productos/sony-wh-ch720n.jpg' AS url_imagen
    UNION ALL
    SELECT 'Sony' AS marca, 'WF-1000XM5 In-Ear' AS nombre, 'Audio' AS categoria, 999.00 AS precio_original, '-20%' AS descuento, 799.20 AS precio_oferta, '/assets/images/productos/sony-wf-1000xm5-in-ear.jpg' AS url_imagen
    UNION ALL
    SELECT 'JBL' AS marca, 'Tune 520BT Wireless' AS nombre, 'Audio' AS categoria, 199.00 AS precio_original, '-20%' AS descuento, 159.20 AS precio_oferta, '/assets/images/productos/jbl-tune-520bt-wireless.jpg' AS url_imagen
    UNION ALL
    SELECT 'JBL' AS marca, 'Wave Flex' AS nombre, 'Audio' AS categoria, 249.00 AS precio_original, '-20%' AS descuento, 199.20 AS precio_oferta, '/assets/images/productos/jbl-wave-flex.jpg' AS url_imagen
    UNION ALL
    SELECT 'JBL' AS marca, 'Flip 6 Parlante Portátil' AS nombre, 'Audio' AS categoria, 499.00 AS precio_original, '-20%' AS descuento, 399.20 AS precio_oferta, '/assets/images/productos/jbl-flip-6-parlante-portatil.jpg' AS url_imagen
    UNION ALL
    SELECT 'JBL' AS marca, 'Charge 5 Parlante Bluetooth' AS nombre, 'Audio' AS categoria, 699.00 AS precio_original, '-20%' AS descuento, 559.20 AS precio_oferta, '/assets/images/productos/jbl-charge-5-parlante-bluetooth.jpg' AS url_imagen
    UNION ALL
    SELECT 'Bose' AS marca, 'SoundLink Flex' AS nombre, 'Audio' AS categoria, 649.00 AS precio_original, '-20%' AS descuento, 519.20 AS precio_oferta, '/assets/images/productos/bose-soundlink-flex.jpg' AS url_imagen
    UNION ALL
    SELECT 'Bose' AS marca, 'QuietComfort Ultra' AS nombre, 'Audio' AS categoria, 1699.00 AS precio_original, '-20%' AS descuento, 1359.20 AS precio_oferta, '/assets/images/productos/bose-quietcomfort-ultra.jpg' AS url_imagen
    UNION ALL
    SELECT 'Earfun' AS marca, 'Earfun Air Pro 3' AS nombre, 'Audio' AS categoria, 279.00 AS precio_original, '-20%' AS descuento, 223.20 AS precio_oferta, '/assets/images/productos/earfun-earfun-air-pro-3.jpg' AS url_imagen
    UNION ALL
    SELECT 'Anker' AS marca, 'Space One ANC' AS nombre, 'Audio' AS categoria, 399.00 AS precio_original, '-20%' AS descuento, 319.20 AS precio_oferta, '/assets/images/productos/anker-space-one-anc.jpg' AS url_imagen
    UNION ALL
    SELECT 'Anker' AS marca, 'Soundcore P20i' AS nombre, 'Audio' AS categoria, 99.00 AS precio_original, '-20%' AS descuento, 79.20 AS precio_oferta, '/assets/images/productos/anker-soundcore-p20i.jpg' AS url_imagen
    UNION ALL
    SELECT 'Sonos' AS marca, 'Move 2 Parlante Premium' AS nombre, 'Audio' AS categoria, 1999.00 AS precio_original, '-20%' AS descuento, 1599.20 AS precio_oferta, '/assets/images/productos/sonos-move-2-parlante-premium.jpg' AS url_imagen
    UNION ALL
    SELECT 'Sonos' AS marca, 'Era 100 Parlante Inteligente' AS nombre, 'Audio' AS categoria, 1199.00 AS precio_original, '-20%' AS descuento, 959.20 AS precio_oferta, '/assets/images/productos/sonos-era-100-parlante-inteligente.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'Cargador de Pared 20W USB-C' AS nombre, 'Accesorios' AS categoria, 119.00 AS precio_original, '-20%' AS descuento, 95.20 AS precio_oferta, '/assets/images/productos/apple-cargador-de-pared-20w-usb-c.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'Cable Lightning a USB-C 1m' AS nombre, 'Accesorios' AS categoria, 99.00 AS precio_original, '-20%' AS descuento, 79.20 AS precio_oferta, '/assets/images/productos/apple-cable-lightning-a-usb-c-1m.jpg' AS url_imagen
    UNION ALL
    SELECT 'Samsung' AS marca, 'Cargador Super Fast Charging 45W' AS nombre, 'Accesorios' AS categoria, 149.00 AS precio_original, '-20%' AS descuento, 119.20 AS precio_oferta, '/assets/images/productos/samsung-cargador-super-fast-charging-45w.jpg' AS url_imagen
    UNION ALL
    SELECT 'Xiaomi' AS marca, 'Cargador Carga Rápida 33W' AS nombre, 'Accesorios' AS categoria, 69.00 AS precio_original, '-20%' AS descuento, 55.20 AS precio_oferta, '/assets/images/productos/xiaomi-cargador-carga-rapida-33w.jpg' AS url_imagen
    UNION ALL
    SELECT 'Anker' AS marca, 'Power Bank 20000mAh 22.5W' AS nombre, 'Accesorios' AS categoria, 189.00 AS precio_original, '-20%' AS descuento, 151.20 AS precio_oferta, '/assets/images/productos/anker-power-bank-20000mah-22-5w.jpg' AS url_imagen
    UNION ALL
    SELECT 'Anker' AS marca, 'Power Bank MagSafe 10000mAh' AS nombre, 'Accesorios' AS categoria, 249.00 AS precio_original, '-20%' AS descuento, 199.20 AS precio_oferta, '/assets/images/productos/anker-power-bank-magsafe-10000mah.jpg' AS url_imagen
    UNION ALL
    SELECT 'Insta360' AS marca, 'Estabilizador Flow para Celular' AS nombre, 'Accesorios' AS categoria, 679.00 AS precio_original, '-20%' AS descuento, 543.20 AS precio_oferta, '/assets/images/productos/insta360-estabilizador-flow-para-celular.jpg' AS url_imagen
    UNION ALL
    SELECT 'Joby' AS marca, 'Trípode Flexible GorillaPod' AS nombre, 'Accesorios' AS categoria, 149.00 AS precio_original, '-20%' AS descuento, 119.20 AS precio_oferta, '/assets/images/productos/joby-tripode-flexible-gorillapod.jpg' AS url_imagen
    UNION ALL
    SELECT 'Generic' AS marca, 'Protector de Pantalla Vidrio Hidrogel' AS nombre, 'Accesorios' AS categoria, 39.00 AS precio_original, '-20%' AS descuento, 31.20 AS precio_oferta, '/assets/images/productos/generic-protector-de-pantalla-vidrio-hidrogel.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'Funda de Silicona con MagSafe i15' AS nombre, 'Accesorios' AS categoria, 249.00 AS precio_original, '-20%' AS descuento, 199.20 AS precio_oferta, '/assets/images/productos/apple-funda-de-silicona-con-magsafe-i15.jpg' AS url_imagen
    UNION ALL
    SELECT 'Ringke' AS marca, 'Funda Ringke Fusion Galaxy S24' AS nombre, 'Accesorios' AS categoria, 79.00 AS precio_original, '-20%' AS descuento, 63.20 AS precio_oferta, '/assets/images/productos/ringke-funda-ringke-fusion-galaxy-s24.jpg' AS url_imagen
    UNION ALL
    SELECT 'Spigen' AS marca, 'Funda Spigen Tough Armor S24U' AS nombre, 'Accesorios' AS categoria, 119.00 AS precio_original, '-20%' AS descuento, 95.20 AS precio_oferta, '/assets/images/productos/spigen-funda-spigen-tough-armor-s24u.jpg' AS url_imagen
    UNION ALL
    SELECT 'Generic' AS marca, 'Soporte Magnético para Auto AutoDrive' AS nombre, 'Accesorios' AS categoria, 49.00 AS precio_original, '-20%' AS descuento, 39.20 AS precio_oferta, '/assets/images/productos/generic-soporte-magnetico-para-auto-autodrive.jpg' AS url_imagen
    UNION ALL
    SELECT 'Belkin' AS marca, 'Cargador Inalámbrico 3 en 1 Qi' AS nombre, 'Accesorios' AS categoria, 399.00 AS precio_original, '-20%' AS descuento, 319.20 AS precio_oferta, '/assets/images/productos/belkin-cargador-inalambrico-3-en-1-qi.jpg' AS url_imagen
    UNION ALL
    SELECT 'Apple' AS marca, 'Adaptador USB-C a Jack 3.5mm' AS nombre, 'Accesorios' AS categoria, 49.00 AS precio_original, '-20%' AS descuento, 39.20 AS precio_oferta, '/assets/images/productos/apple-adaptador-usb-c-a-jack-3-5mm.jpg' AS url_imagen
    UNION ALL
    SELECT 'SanDisk' AS marca, 'Tarjeta MicroSD 128GB Extreme' AS nombre, 'Accesorios' AS categoria, 89.00 AS precio_original, '-20%' AS descuento, 71.20 AS precio_oferta, '/assets/images/productos/sandisk-tarjeta-microsd-128gb-extreme.jpg' AS url_imagen
    UNION ALL
    SELECT 'SanDisk' AS marca, 'Tarjeta MicroSD 256GB Ultra' AS nombre, 'Accesorios' AS categoria, 139.00 AS precio_original, '-20%' AS descuento, 111.20 AS precio_oferta, '/assets/images/productos/sandisk-tarjeta-microsd-256gb-ultra.jpg' AS url_imagen
    UNION ALL
    SELECT 'Generic' AS marca, 'Kit de Limpieza para Pantallas' AS nombre, 'Accesorios' AS categoria, 25.00 AS precio_original, '-20%' AS descuento, 20.00 AS precio_oferta, '/assets/images/productos/generic-kit-de-limpieza-para-pantallas.jpg' AS url_imagen
    UNION ALL
    SELECT 'Generic' AS marca, 'Soporte de Escritorio de Aluminio' AS nombre, 'Accesorios' AS categoria, 59.00 AS precio_original, '-20%' AS descuento, 47.20 AS precio_oferta, '/assets/images/productos/generic-soporte-de-escritorio-de-aluminio.jpg' AS url_imagen
    UNION ALL
    SELECT 'Generic' AS marca, 'Lente Macro/Gran Angular Clip-on' AS nombre, 'Accesorios' AS categoria, 79.00 AS precio_original, '-20%' AS descuento, 63.20 AS precio_oferta, '/assets/images/productos/generic-lente-macro-gran-angular-clip-on.jpg' AS url_imagen
) AS seed
JOIN categorias c ON LOWER(TRIM(c.nombre)) = LOWER(TRIM(seed.categoria)) AND c.activo = 1
WHERE NOT EXISTS (
    SELECT 1 FROM productos p
    WHERE LOWER(TRIM(p.marca)) = LOWER(TRIM(seed.marca))
      AND LOWER(TRIM(p.nombre)) = LOWER(TRIM(seed.nombre))
);
