-- =====================================================================
--  CITYFIX — Base de données du Module 1 (Gestion des utilisateurs)
--  Compatible MySQL 5.7+ / MariaDB 10.4+ (XAMPP 8.2)
--  Import : phpMyAdmin > Importer > ce fichier (ou onglet SQL > coller)
--
--  Tables :
--    roles            (3 rôles prédéfinis : citoyen, agent, admin)
--    users            (N utilisateurs -> 1 rôle, via role_id)
--    permissions      (droits du Module 1)
--    role_permission  (association N,N : quels droits pour quel rôle)
--
--  Compte administrateur créé par défaut :
--    e-mail : admin@cityfix.tn   mot de passe : Admin@2026
--    -> À CHANGER après la première connexion (Mon profil).
--
--  Modules 2, 3 et 4 : aucune table créée volontairement.
-- =====================================================================

-- Encodage de la connexion : indispensable pour les accents (é, è, à...)
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS cityfix
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cityfix;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS role_permission;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Table roles : rôles prédéfinis (pas de CRUD des rôles dans l'application)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nom         VARCHAR(30)   NOT NULL,              -- identifiant technique : citoyen | agent | admin
    libelle     VARCHAR(50)   NOT NULL,              -- nom affiché
    description VARCHAR(255)  NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table users : relation N,1 avec roles (clé étrangère role_id)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    role_id          INT UNSIGNED  NOT NULL,
    nom              VARCHAR(50)   NOT NULL,
    prenom           VARCHAR(50)   NOT NULL,
    email            VARCHAR(150)  NOT NULL,
    telephone        VARCHAR(20)   NULL,
    password         VARCHAR(255)  NOT NULL,          -- hachage password_hash() (bcrypt)
    statut           ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    reset_token_hash CHAR(64)      NULL,              -- SHA-256 du jeton de réinitialisation
    reset_expires_at DATETIME      NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    KEY idx_users_statut (statut),
    KEY idx_users_reset (reset_token_hash),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT                             -- impossible de supprimer un rôle utilisé
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table permissions : droits du Module 1
--   reserve_admin = 1 : droit réservé à l'administrateur (non attribuable)
-- ---------------------------------------------------------------------
CREATE TABLE permissions (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cle           VARCHAR(50)  NOT NULL,              -- ex : users.view
    libelle       VARCHAR(100) NOT NULL,
    reserve_admin TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_cle (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table role_permission : association N,N roles <-> permissions
-- ---------------------------------------------------------------------
CREATE TABLE role_permission (
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    KEY idx_rp_permission (permission_id),
    CONSTRAINT fk_rp_role       FOREIGN KEY (role_id)       REFERENCES roles (id)       ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Données initiales
-- ---------------------------------------------------------------------
INSERT INTO roles (id, nom, libelle, description) VALUES
    (1, 'citoyen', 'Citoyen',         'Rôle attribué à l''inscription. Accès à l''espace citoyen.'),
    (2, 'agent',   'Agent municipal', 'Traite le travail municipal. Consulte les utilisateurs.'),
    (3, 'admin',   'Administrateur',  'Supervise la plateforme et gère tous les comptes.');

INSERT INTO permissions (id, cle, libelle, reserve_admin) VALUES
    (1, 'profile.manage',    'Gérer son profil',            0),
    (2, 'backoffice.access', 'Accéder au Back Office',      0),
    (3, 'users.view',        'Voir les utilisateurs',       0),
    (4, 'users.create',      'Créer un utilisateur',        0),
    (5, 'users.edit',        'Modifier un utilisateur',     0),
    (6, 'users.toggle',      'Activer / désactiver',        0),
    (7, 'users.delete',      'Supprimer un utilisateur',    0),
    (8, 'roles.manage',      'Gérer les rôles et permissions', 1);

-- Citoyen : profil uniquement
INSERT INTO role_permission (role_id, permission_id) VALUES (1, 1);
-- Agent : profil + Back Office en lecture
INSERT INTO role_permission (role_id, permission_id) VALUES (2, 1), (2, 2), (2, 3);
-- Admin : tout (ses permissions sont verrouillées dans l'application)
INSERT INTO role_permission (role_id, permission_id) VALUES
    (3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8);

-- Administrateur par défaut — mot de passe : Admin@2026
INSERT INTO users (role_id, nom, prenom, email, telephone, password, statut) VALUES
    (3, 'Admin', 'CityFix', 'admin@cityfix.tn', NULL,
     '$2y$10$.Es/O9CBsInpk1xGLe2lLeyfNxT6Th5H7Xs2XybUg6LV9JWP4mWBy', 'actif');
