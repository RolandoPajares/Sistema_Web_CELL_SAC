ALTER TABLE productos
    ADD COLUMN categoria_id INT NULL AFTER nombre;

UPDATE productos p
JOIN categorias c ON LOWER(TRIM(c.nombre)) = LOWER(TRIM(p.categoria))
SET p.categoria_id = c.id
WHERE p.categoria_id IS NULL;

UPDATE productos p
JOIN categorias c ON LOWER(TRIM(c.nombre)) = LOWER(CONCAT(TRIM(p.categoria), 's'))
SET p.categoria_id = c.id,
    p.categoria = c.nombre
WHERE p.categoria_id IS NULL;

INSERT INTO categorias(nombre, descripcion, activo)
SELECT DISTINCT TRIM(p.categoria), 'Categoría migrada desde productos', 1
FROM productos p
LEFT JOIN categorias c ON LOWER(TRIM(c.nombre)) = LOWER(TRIM(p.categoria))
WHERE p.categoria_id IS NULL AND c.id IS NULL;

UPDATE productos p
JOIN categorias c ON LOWER(TRIM(c.nombre)) = LOWER(TRIM(p.categoria))
SET p.categoria_id = c.id
WHERE p.categoria_id IS NULL;

ALTER TABLE productos
    MODIFY categoria_id INT NOT NULL,
    ADD KEY indice_productos_categoria_id (categoria_id),
    ADD CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id);
