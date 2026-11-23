-- =====================================================================
--  CITYFIX — Données de démonstration (FACULTATIF)
--  À importer APRÈS cityfix.sql, pour tester les rôles et les filtres.
--
--  Comptes :
--    agent@cityfix.tn    / Agent@2026    (agent municipal, actif)
--    citoyen@cityfix.tn  / Citoyen@2026  (citoyen, actif)
--    inactif@cityfix.tn  / Citoyen@2026  (citoyen, INACTIF -> connexion refusée)
-- =====================================================================

-- Encodage de la connexion : indispensable pour les accents (é, è, à...)
SET NAMES utf8mb4;

USE cityfix;

INSERT INTO users (role_id, nom, prenom, email, telephone, password, statut) VALUES
    (2, 'Hammami',  'Nour',  'agent@cityfix.tn',   '22654321', '$2y$10$7N3p7zh.NZBzil88R9DZDeW/tzBm0VYCnzWzE8lq32JzW72SkiyRK', 'actif'),
    (1, 'Trabelsi', 'Amine', 'citoyen@cityfix.tn', '20123456', '$2y$10$OoJciDUx.kAN7W1/WImSneIkrk94L1LeWI9XH1ADOlPWkSjfT0i7O', 'actif'),
    (1, 'Mansour',  'Youssef','inactif@cityfix.tn', NULL,      '$2y$10$OoJciDUx.kAN7W1/WImSneIkrk94L1LeWI9XH1ADOlPWkSjfT0i7O', 'inactif'),
    (1, 'Kefi',     'Rania', 'rania.kefi@exemple.com',   '55333444', '$2y$10$OoJciDUx.kAN7W1/WImSneIkrk94L1LeWI9XH1ADOlPWkSjfT0i7O', 'actif'),
    (2, 'Gharbi',   'Mehdi', 'mehdi.gharbi@exemple.com', NULL,       '$2y$10$7N3p7zh.NZBzil88R9DZDeW/tzBm0VYCnzWzE8lq32JzW72SkiyRK', 'inactif'),
    (1, 'Ben Salah','Ines',  'ines.bensalah@exemple.com','98765432', '$2y$10$OoJciDUx.kAN7W1/WImSneIkrk94L1LeWI9XH1ADOlPWkSjfT0i7O', 'actif');
