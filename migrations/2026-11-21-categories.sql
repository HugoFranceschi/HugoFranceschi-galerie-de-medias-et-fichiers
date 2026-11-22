-- Migration des catégories de médias.
-- À exécuter une seule fois sur une base existante, après une sauvegarde.
-- Ne pas réimporter galeriedephoto.sql sur une base déjà utilisée.

CREATE TABLE IF NOT EXISTS categories (
    id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_categories_user_name (id_user, name),
    KEY idx_categories_user (id_user),
    CONSTRAINT fk_categories_user FOREIGN KEY (id_user)
        REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE images
    ADD COLUMN id_category INT NULL DEFAULT NULL AFTER id_user,
    ADD KEY idx_images_category (id_category),
    ADD CONSTRAINT fk_images_category FOREIGN KEY (id_category)
        REFERENCES categories (id) ON DELETE SET NULL;
