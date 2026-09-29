ALTER TABLE productos
    ADD COLUMN precio_original DECIMAL(10,2) NULL AFTER precio,
    ADD COLUMN descuento VARCHAR(20) NULL AFTER precio_original,
    ADD COLUMN precio_oferta DECIMAL(10,2) NULL AFTER descuento,
    ADD COLUMN url_imagen VARCHAR(255) NULL AFTER precio_oferta;
