INSERT INTO products (name, slug, short_description, description, price, volume_ml, is_active, main_image)
VALUES
    ('Noir Ambre', 'noir-ambre', 'Amber • Oud • Vanille • Soft Smoke', 'Signature avondgeur: warm, elegant, langdurig.', 89.00, 50, 1, NULL),
    ('Or de Rosé', 'or-de-rose', 'Rose • Saffraan • Musk • Clean Woods', 'Clean opening met rosé warmte en een luxe dry down.', 94.00, 50, 1, NULL),
    ('Citrus Noir', 'citrus-noir', 'Bergamot • Neroli • Cedar • Dark Tea', 'Fresh & dark: citrus helderheid met noir karakter.', 84.00, 50, 1, NULL);

-- Voorbeeldrelaties
INSERT INTO product_relations (product_id, related_product_id)
VALUES
    (1,2),
    (1,3),
    (2,1);

-- Voorbeeld extra images (paden later via admin uploads)
-- INSERT INTO product_images (product_id, image_path, sort_order) VALUES (1, 'uploads/products/1/extra1.jpg', 1);
