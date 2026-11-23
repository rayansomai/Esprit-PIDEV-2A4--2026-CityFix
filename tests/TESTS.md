# CITYFIX — Cahier de tests du Module 1

Date : 23/11/2026 · Environnement de test : PHP 8.3 (code vérifié compatible PHP 8.2), MariaDB 10.11, serveur PHP intégré + Chromium (Playwright).

Deux types de tests :

1. **Tests automatiques** (`tests/http_tests.py`) : 69 vérifications HTTP. Résultat : **69/69 réussis** (détail : `tests/resultats_tests_auto_2026-11-23.txt`).
2. **Tests manuels / navigateur** : responsive, accessibilité, expiration de session, validation JavaScript.

> Avant chaque campagne : réimporter `database/cityfix.sql` puis `database/demo_data.sql` (les tests modifient les données).

## 1. Comptes de test (demo_data.sql)

| Rôle | E-mail | Mot de passe | Statut |
|---|---|---|---|
| Administrateur | admin@cityfix.tn | Admin@2026 | actif |
| Agent municipal | agent@cityfix.tn | Agent@2026 | actif |
| Citoyen | citoyen@cityfix.tn | Citoyen@2026 | actif |
| Citoyen | inactif@cityfix.tn | Citoyen@2026 | **inactif** |

## 2. Authentification et sessions

| # | Scénario | Résultat attendu | Résultat |
|---|---|---|---|
| T1 | Inscription valide | compte créé (citoyen), redirection connexion, mot de passe haché `$2y$…` | ✅ |
| T2 | Inscription : e-mail invalide, nom trop court, mot de passe faible, confirmation différente, e-mail déjà utilisé | messages d'erreur, rien en base | ✅ |
| T3 | Inscription avec `role_id=3` ajouté dans la requête | rôle reste **citoyen** | ✅ |
| T4 | Connexion citoyen / agent / admin | citoyen → profil ; agent, admin → dashboard | ✅ |
| T5 | Mauvais mot de passe, e-mail inconnu, compte inactif | refus, message générique, aucune session | ✅ |
| T6 | Cookie PHPSESSID avant / après connexion | valeur différente (`session_regenerate_id`) | ✅ |
| T7 | Expiration : `SESSION_TEST_MODE = true` (10 s) | actif après 5 s d'activité ; après 12 s d'inactivité → connexion + message « session expirée » | ✅ |
| T8 | Déconnexion en GET / en POST + retour arrière | GET refusé (405) ; après POST, pages protégées inaccessibles | ✅ |
| S1 | En-têtes HTTP | CSP, X-Frame-Options DENY, nosniff, Referrer-Policy | ✅ |
| S2 | Cookie de session | HttpOnly + SameSite=Lax | ✅ |
| S3 | 5 échecs de connexion | blocage 60 s, même avec le bon mot de passe | ✅ |

## 3. Rôles et permissions (contrôlés côté serveur)

| # | Scénario | Résultat attendu | Résultat |
|---|---|---|---|
| T9 | Sans connexion : dashboard, users, roles, profile, user_create | redirection connexion | ✅ |
| T10 | Citoyen : dashboard, users, roles, user_create | 403 | ✅ |
| T11 | Agent : dashboard et liste OK ; création/rôles 403 ; POST forgé `user_delete` / `user_toggle` | 403, aucune modification | ✅ |
| T12 | Admin désactivé pendant sa session | accès perdu à la requête suivante | ✅ |
| T13 | Auto-suppression ; rétrogradation du dernier admin ; formulaire du dernier admin | refusé ; verrouillé | ✅ |
| R1 | Admin donne `users.toggle` à l'agent | bouton visible, `users.view` + `backoffice.access` ajoutés automatiquement | ✅ |
| R2 | `roles.manage` envoyée pour l'agent | ignorée (réservée à l'admin) | ✅ |
| R3 | Agent avec `users.toggle` sur un compte admin | 403 | ✅ |
| R4 | Permission retirée | effet immédiat | ✅ |

## 4. CRUD Back Office

| # | Scénario | Résultat |
|---|---|---|
| C1–C2 | Création + recherche par e-mail | ✅ |
| C3 | Filtre rôle + statut combinés | ✅ |
| C4 | Fiche détail | ✅ |
| C5 | Modification (nom, téléphone, rôle) | ✅ |
| C6 | Désactivation | ✅ |
| C7 | Suppression | ✅ |
| C8 | Suppression via lien GET | refusée (405) ✅ |

## 5. Sécurité

| # | Scénario | Résultat |
|---|---|---|
| T14 | POST sans jeton CSRF / avec faux jeton | refusé ✅ |
| T15 | `' OR '1'='1` dans la connexion et la recherche | sans effet ✅ |
| T16 | `<script>alert(1)</script>` comme nom / dans la recherche | refusé par la validation / réaffiché échappé ✅ |
| T17 | Réinitialisation : lien valide, nouveau mot de passe, réutilisation du lien, jeton inventé | OK / connexion OK / refusé / refusé ✅ |
| T18 | Mot de passe oublié avec e-mail inconnu | même message ✅ |

## 6. Profil citoyen

| # | Scénario | Résultat |
|---|---|---|
| F1 | Mise à jour des informations | ✅ |
| F2 | Changement de mot de passe avec mot de passe actuel faux | refusé ✅ |

## 7. Responsive, accessibilité, JavaScript (navigateur Chromium)

| Vérification | Largeurs | Résultat |
|---|---|---|
| Aucun débordement horizontal sur 12 pages (Front + Back) | 1366, 820, 390 px | ✅ (2 défauts trouvés et corrigés : barre du haut mobile, libellés cachés de la matrice) |
| Aucune erreur console ni blocage CSP | 3 largeurs | ✅ |
| Spinner masqué après chargement | 3 largeurs | ✅ |
| Menu mobile (Front) et sidebar coulissante (Back) | 390 px | ✅ |
| Validation JavaScript : e-mail invalide → erreur affichée, envoi bloqué | 3 largeurs | ✅ |
| Lien « Aller au contenu », focus visible, `aria-label` des boutons icônes, `aria-invalid` des champs en erreur | — | ✅ (vérification du code) |

## 8. Relancer les tests automatiques

```bash
pip install requests beautifulsoup4
# base fraîche : réimporter database/cityfix.sql puis database/demo_data.sql
python tests/http_tests.py http://localhost/CityFix
```
