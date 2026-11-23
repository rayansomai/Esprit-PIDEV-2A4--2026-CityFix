#!/usr/bin/env python3
"""
CITYFIX — Tests HTTP automatisés du Module 1 (authentification, sessions, rôles,
permissions, CRUD, sécurité).

Prérequis :
  - base « cityfix » fraîchement importée : database/cityfix.sql PUIS database/demo_data.sql
  - Python 3 + bibliothèques : pip install requests beautifulsoup4
Lancement :
  python tests/http_tests.py http://localhost/CityFix
"""
import re
import sys

import requests
from bs4 import BeautifulSoup

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost/CityFix").rstrip("/")
RESULTS = []


def url(action, **params):
    q = "&".join(f"{k}={v}" for k, v in params.items())
    return f"{BASE}/index.php?action={action}" + (f"&{q}" if q else "")


def check(test_id, label, condition, detail=""):
    RESULTS.append((test_id, label, bool(condition), detail))
    print(f"[{'OK ' if condition else 'ÉCHEC'}] {test_id} — {label}" + (f"  ({detail})" if detail and not condition else ""))


def csrf(session, action, **params):
    html = session.get(url(action, **params)).text
    m = re.search(r'name="csrf_token" value="([a-f0-9]{64})"', html)
    return m.group(1) if m else ""


def login(email, password):
    s = requests.Session()
    token = csrf(s, "login")
    r = s.post(url("login"), data={"csrf_token": token, "email": email, "password": password}, allow_redirects=False)
    return s, r


def user_id_by_email(session, email):
    html = session.get(url("users", q=email)).text
    m = re.search(r'name="id" value="(\d+)"', html) or re.search(r"action=user_show&amp;id=(\d+)", html)
    return int(m.group(1)) if m else 0


def no_php_error(html):
    return not re.search(r"(Warning|Notice|Deprecated|Fatal error|Uncaught)\b.*on line", html)


# ---------------------------------------------------------------- PAGES PUBLIQUES
for action in ["home", "about", "login", "register", "forgot_password"]:
    r = requests.get(url(action))
    check("P0", f"Page publique « {action} » accessible sans erreur PHP", r.status_code == 200 and no_php_error(r.text))
r = requests.get(url("inexistante"))
check("P1", "Action inconnue -> 404", r.status_code == 404)
r = requests.get(url("home"))
check("S1", "En-têtes de sécurité présents (CSP, X-Frame-Options, nosniff)",
      "default-src 'self'" in r.headers.get("Content-Security-Policy", "")
      and r.headers.get("X-Frame-Options") == "DENY" and r.headers.get("X-Content-Type-Options") == "nosniff")
cookie = r.headers.get("Set-Cookie", "")
check("S2", "Cookie de session HttpOnly + SameSite=Lax", "HttpOnly" in cookie and "SameSite=Lax" in cookie, cookie)

# ---------------------------------------------------------------- INSCRIPTION
s = requests.Session()
t = csrf(s, "register")
r = s.post(url("register"), data={"csrf_token": t, "nom": "Testeur", "prenom": "Iyed", "email": "iyed.test@exemple.com",
                                  "telephone": "20 111 222", "password": "Secret123", "password_confirm": "Secret123",
                                  "role_id": "3", "role": "admin"}, allow_redirects=False)
check("T1", "Inscription valide -> redirection vers la connexion", r.status_code == 302 and "action=login" in r.headers.get("Location", ""))
s2, r = login("iyed.test@exemple.com", "Secret123")
check("T3", "Inscription : role_id=3 injecté ignoré -> rôle citoyen (redirigé vers le profil)",
      "action=profile" in r.headers.get("Location", ""), r.headers.get("Location", ""))

s = requests.Session()
t = csrf(s, "register")
r = s.post(url("register"), data={"csrf_token": t, "nom": "A", "prenom": "Iyed1", "email": "pas-un-email",
                                  "password": "faible", "password_confirm": "autre"})
check("T2a", "Inscription invalide -> erreurs affichées, pas de redirection",
      r.status_code == 200 and "Format d&#039;adresse e-mail invalide" in r.text and "entre 2 et 50" in r.text)
t = csrf(s, "register")
r = s.post(url("register"), data={"csrf_token": t, "nom": "Double", "prenom": "Compte", "email": "iyed.test@exemple.com",
                                  "password": "Secret123", "password_confirm": "Secret123"})
check("T2b", "Inscription avec e-mail déjà utilisé -> refusée", "déjà utilisée" in r.text)
t = csrf(s, "register")
r = s.post(url("register"), data={"csrf_token": t, "nom": "Mdp", "prenom": "Diff", "email": "mdp.diff@exemple.com",
                                  "password": "Secret123", "password_confirm": "Secret124"})
check("T2c", "Inscription : confirmation différente -> refusée", "ne correspondent pas" in r.text)

# ---------------------------------------------------------------- CONNEXION
for email, pwd, target, label in [("citoyen@cityfix.tn", "Citoyen@2026", "profile", "citoyen -> profil"),
                                  ("agent@cityfix.tn", "Agent@2026", "dashboard", "agent -> dashboard"),
                                  ("admin@cityfix.tn", "Admin@2026", "dashboard", "admin -> dashboard")]:
    sess, r = login(email, pwd)
    check("T4", f"Connexion valide {label}", r.status_code == 302 and f"action={target}" in r.headers.get("Location", ""))

sess, r = login("admin@cityfix.tn", "MauvaisMdp1")
check("T5a", "Mauvais mot de passe -> refus, pas de session",
      r.status_code == 200 and "incorrect" in r.text and sess.get(url("dashboard"), allow_redirects=False).status_code == 302)
sess, r = login("inactif@cityfix.tn", "Citoyen@2026")
check("T5b", "Compte inactif -> connexion refusée", r.status_code == 200 and "désactivé" in r.text)
sess, r = login("inconnu@exemple.com", "Secret123")
check("T5c", "E-mail inconnu -> même message générique", "E-mail ou mot de passe incorrect" in r.text)

s = requests.Session()
s.get(url("login"))
before = s.cookies.get("PHPSESSID")
t = csrf(s, "login")
s.post(url("login"), data={"csrf_token": t, "email": "citoyen@cityfix.tn", "password": "Citoyen@2026"}, allow_redirects=False)
after = s.cookies.get("PHPSESSID")
check("T6", "Identifiant de session régénéré après connexion", before and after and before != after, f"{before} / {after}")

# Limitation des tentatives
s = requests.Session()
for _ in range(5):
    t = csrf(s, "login")
    s.post(url("login"), data={"csrf_token": t, "email": "agent@cityfix.tn", "password": "Faux12345"})
t = csrf(s, "login")
r = s.post(url("login"), data={"csrf_token": t, "email": "agent@cityfix.tn", "password": "Agent@2026"})
check("S3", "Après 5 échecs : blocage temporaire même avec le bon mot de passe", "Trop de tentatives" in r.text)

# ---------------------------------------------------------------- DÉCONNEXION
s, _ = login("citoyen@cityfix.tn", "Citoyen@2026")
r = s.get(url("logout"), allow_redirects=False)
check("T8a", "Déconnexion en GET refusée (405)", r.status_code == 405)
t = csrf(s, "profile")
s.post(url("logout"), data={"csrf_token": t})
r = s.get(url("profile"), allow_redirects=False)
check("T8b", "Après déconnexion : page protégée inaccessible", r.status_code == 302 and "action=login" in r.headers.get("Location", ""))

# ---------------------------------------------------------------- ACCÈS ET PERMISSIONS
anon = requests.Session()
for action in ["dashboard", "users", "roles", "profile", "user_create"]:
    r = anon.get(url(action), allow_redirects=False)
    check("T9", f"Sans connexion : « {action} » -> redirection connexion", r.status_code == 302)

cit, _ = login("citoyen@cityfix.tn", "Citoyen@2026")
for action in ["dashboard", "users", "roles", "user_create"]:
    r = cit.get(url(action), allow_redirects=False)
    check("T10", f"Citoyen : « {action} » -> 403", r.status_code == 403)

agent, _ = login("agent@cityfix.tn", "Agent@2026")
check("T11a", "Agent : dashboard accessible", agent.get(url("dashboard")).status_code == 200)
r = agent.get(url("users"))
check("T11b", "Agent : liste des utilisateurs en lecture seule (aucun bouton Supprimer)",
      r.status_code == 200 and "action=user_delete" not in r.text and "action=user_edit" not in r.text)
for action in ["user_create", "roles"]:
    check("T11c", f"Agent : « {action} » -> 403", agent.get(url(action), allow_redirects=False).status_code == 403)

admin, _ = login("admin@cityfix.tn", "Admin@2026")
victim_id = user_id_by_email(admin, "rania.kefi@exemple.com")
t = csrf(agent, "users")
r = agent.post(url("user_delete"), data={"csrf_token": t, "id": victim_id}, allow_redirects=False)
check("T11d", "Agent : POST user_delete forgé -> 403, rien n'est supprimé",
      r.status_code == 403 and admin.get(url("user_show", id=victim_id)).status_code == 200)
t = csrf(agent, "users")
r = agent.post(url("user_toggle"), data={"csrf_token": t, "id": victim_id}, allow_redirects=False)
check("T11e", "Agent : POST user_toggle forgé -> 403", r.status_code == 403)

# ---------------------------------------------------------------- CRUD ADMIN
t = csrf(admin, "user_create")
r = admin.post(url("user_store"), data={"csrf_token": t, "nom": "Nouvel", "prenom": "Agent", "email": "nouvel.agent@exemple.com",
                                        "telephone": "", "role_id": "2", "statut": "actif",
                                        "password": "Agent12345", "password_confirm": "Agent12345"}, allow_redirects=False)
check("C1", "Admin : création d'un utilisateur", r.status_code == 302 and "action=users" in r.headers.get("Location", ""))
new_id = user_id_by_email(admin, "nouvel.agent@exemple.com")
check("C2", "Admin : recherche par e-mail retrouve l'utilisateur créé", new_id > 0)
r = admin.get(url("users", role="agent", statut="actif"))
check("C3", "Admin : filtre rôle + statut", r.status_code == 200 and "nouvel.agent@exemple.com" in r.text and "mehdi.gharbi" not in r.text)
check("C4", "Admin : fiche détail", "nouvel.agent@exemple.com" in admin.get(url("user_show", id=new_id)).text)
t = csrf(admin, "user_edit", id=new_id)
r = admin.post(url("user_update"), data={"csrf_token": t, "id": new_id, "nom": "Modifié", "prenom": "Agent",
                                         "email": "nouvel.agent@exemple.com", "telephone": "71000000", "role_id": "1",
                                         "statut": "actif", "password": "", "password_confirm": ""}, allow_redirects=False)
check("C5", "Admin : modification (nom + rôle)", r.status_code == 302 and "Modifié" in admin.get(url("user_show", id=new_id)).text)
t = csrf(admin, "users")
admin.post(url("user_toggle"), data={"csrf_token": t, "id": new_id})
check("C6", "Admin : désactivation", "Inactif" in admin.get(url("user_show", id=new_id)).text)
t = csrf(admin, "users")
admin.post(url("user_delete"), data={"csrf_token": t, "id": new_id})
check("C7", "Admin : suppression", admin.get(url("user_show", id=new_id)).status_code == 404)
r = admin.get(url("user_delete"), allow_redirects=False)
check("C8", "Suppression par simple lien (GET) refusée", r.status_code == 405)

# ---------------------------------------------------------------- DERNIER ADMINISTRATEUR
admin_id = user_id_by_email(admin, "admin@cityfix.tn")
t = csrf(admin, "users")
admin.post(url("user_delete"), data={"csrf_token": t, "id": admin_id})
check("T13a", "Admin : auto-suppression refusée", admin.get(url("user_show", id=admin_id)).status_code == 200)

# Création d'un 2e admin, connexion avec lui, tentative sur le 1er (dernier actif après désactivation du 2e ?)
t = csrf(admin, "user_create")
admin.post(url("user_store"), data={"csrf_token": t, "nom": "Second", "prenom": "Admin", "email": "admin2@exemple.com",
                                    "role_id": "3", "statut": "actif", "password": "Admin2Pass", "password_confirm": "Admin2Pass"})
admin2, _ = login("admin2@exemple.com", "Admin2Pass")
admin2_id = user_id_by_email(admin, "admin2@exemple.com")
# admin1 désactive admin2 -> admin1 devient le dernier admin actif
t = csrf(admin, "users")
admin.post(url("user_toggle"), data={"csrf_token": t, "id": admin2_id})
r = admin2.get(url("dashboard"), allow_redirects=False)
check("T12", "Admin désactivé pendant sa session -> accès perdu à la requête suivante", r.status_code == 302)
# réactiver admin2, puis admin2 tente de rétrograder/supprimer admin1 alors qu'il y a 2 admins -> autorisé ? On teste le cas « dernier »
t = csrf(admin, "users")
admin.post(url("user_toggle"), data={"csrf_token": t, "id": admin2_id})   # réactivé
admin2, _ = login("admin2@exemple.com", "Admin2Pass")
t = csrf(admin2, "users")
admin2.post(url("user_toggle"), data={"csrf_token": t, "id": admin_id})   # admin1 désactivé -> admin2 seul admin actif
admin2_self = user_id_by_email(admin2, "admin2@exemple.com")
t = csrf(admin2, "user_edit", id=admin2_self)
r = admin2.post(url("user_update"), data={"csrf_token": t, "id": admin2_self, "nom": "Second", "prenom": "Admin",
                                          "email": "admin2@exemple.com", "role_id": "1", "statut": "actif",
                                          "password": "", "password_confirm": ""})
check("T13b", "Dernier admin (soi-même) : rétrogradation refusée", "propre rôle" in r.text or "dernier administrateur" in r.text)
# admin1 (désactivé) réactivé par admin2 pour la suite
t = csrf(admin2, "users")
admin2.post(url("user_toggle"), data={"csrf_token": t, "id": admin_id})
# admin1 supprime admin2 -> ok (2 admins actifs)
admin, _ = login("admin@cityfix.tn", "Admin@2026")
t = csrf(admin, "users")
admin.post(url("user_delete"), data={"csrf_token": t, "id": admin2_id})
check("T13c", "Suppression d'un admin possible quand il en reste un autre", admin.get(url("user_show", id=admin2_id)).status_code == 404)
# Dernier admin : une autre session admin ne peut plus le désactiver -> vérifié via un agent promu ? Vérification directe :
r = admin.get(url("user_edit", id=admin_id))
check("T13d", "Dernier admin : formulaire verrouillé (rôle/statut désactivés)", "Dernier administrateur actif" in r.text)

# ---------------------------------------------------------------- CSRF / INJECTION SQL / XSS
r = admin.post(url("user_toggle"), data={"id": victim_id}, allow_redirects=True)
check("T14a", "POST sans jeton CSRF refusé", "Jeton de sécurité invalide" in r.text)
r = admin.post(url("user_delete"), data={"csrf_token": "0" * 64, "id": victim_id}, allow_redirects=True)
check("T14b", "POST avec faux jeton CSRF refusé", "Jeton de sécurité invalide" in r.text and admin.get(url("user_show", id=victim_id)).status_code == 200)

s = requests.Session()
t = csrf(s, "login")
r = s.post(url("login"), data={"csrf_token": t, "email": "' OR '1'='1", "password": "' OR '1'='1"})
check("T15a", "Injection SQL dans la connexion sans effet", "incorrect" in r.text or "Veuillez" in r.text)
r = admin.get(url("users", q="%27%20OR%201%3D1%20--%20"))
check("T15b", "Injection SQL dans la recherche sans effet (0 résultat, pas d'erreur)", r.status_code == 200 and "Aucun utilisateur" in r.text)

s = requests.Session()
t = csrf(s, "register")
s.post(url("register"), data={"csrf_token": t, "nom": "<script>alert(1)</script>", "prenom": "Xss", "email": "xss@exemple.com",
                              "password": "Secret123", "password_confirm": "Secret123"})
check("T16a", "XSS : nom avec <script> refusé par la validation", user_id_by_email(admin, "xss@exemple.com") == 0)
r = admin.get(url("users", q="<script>alert(1)</script>"))
check("T16b", "XSS : terme de recherche réaffiché échappé", "<script>alert(1)</script>" not in r.text and "&lt;script&gt;" in r.text)

# ---------------------------------------------------------------- PROFIL
cit, _ = login("citoyen@cityfix.tn", "Citoyen@2026")
t = csrf(cit, "profile")
r = cit.post(url("profile_update"), data={"csrf_token": t, "nom": "Trabelsi", "prenom": "Amine", "email": "citoyen@cityfix.tn", "telephone": "29999999"}, allow_redirects=True)
check("F1", "Citoyen : mise à jour du profil", "Profil mis à jour" in r.text)
t = csrf(cit, "profile")
r = cit.post(url("password_update"), data={"csrf_token": t, "current_password": "Mauvais123", "new_password": "Nouveau123", "new_password_confirm": "Nouveau123"})
check("F2", "Citoyen : changement de mot de passe refusé si l'actuel est faux", "actuel est incorrect" in r.text)

# ---------------------------------------------------------------- RÉINITIALISATION DU MOT DE PASSE
s = requests.Session()
t = csrf(s, "forgot_password")
r = s.post(url("forgot_password"), data={"csrf_token": t, "email": "citoyen@cityfix.tn"})
m = re.search(r'href="([^"]*action=reset_password[^"]*)"', r.text)
check("T17a", "Mot de passe oublié : lien généré (mode test local)", m is not None)
t = csrf(s, "forgot_password")
r2 = s.post(url("forgot_password"), data={"csrf_token": t, "email": "inconnu@exemple.com"})
check("T18", "E-mail inconnu : même message de confirmation", "Vérifiez votre boîte mail" in r2.text and "Vérifiez votre boîte mail" in r.text)
if m:
    link = m.group(1).replace("&amp;", "&")
    token = re.search(r"token=([a-f0-9]{64})", link).group(1)
    page = s.get(url("reset_password", token=token)).text
    t = re.search(r'name="csrf_token" value="([a-f0-9]{64})"', page).group(1)
    r = s.post(url("reset_password"), data={"csrf_token": t, "token": token, "password": "Reinit2026", "password_confirm": "Reinit2026"}, allow_redirects=False)
    check("T17b", "Lien valide : nouveau mot de passe enregistré", r.status_code == 302)
    _, r = login("citoyen@cityfix.tn", "Reinit2026")
    check("T17c", "Connexion avec le nouveau mot de passe", "action=profile" in r.headers.get("Location", ""))
    r = s.get(url("reset_password", token=token))
    check("T17d", "Lien déjà utilisé -> refusé (usage unique)", "invalide ou expiré" in r.text)
r = s.get(url("reset_password", token="a" * 64))
check("T17e", "Jeton inventé -> refusé", "invalide ou expiré" in r.text)

# ---------------------------------------------------------------- PERMISSIONS MODIFIABLES
t = csrf(admin, "roles")
admin.post(url("roles_update"), data={"csrf_token": t, "perms[1][]": ["profile.manage"],
                                      "perms[2][]": ["profile.manage", "users.toggle", "roles.manage"]})
agent, _ = login("agent@cityfix.tn", "Agent@2026")
r = agent.get(url("users"))
check("R1", "Permission users.toggle donnée à l'agent -> bouton visible (users.view ajouté automatiquement)", "action=user_toggle" in r.text)
check("R2", "roles.manage (réservée admin) jamais attribuée à l'agent", agent.get(url("roles"), allow_redirects=False).status_code == 403)
t = csrf(agent, "users")
r = agent.post(url("user_toggle"), data={"csrf_token": t, "id": admin_id}, allow_redirects=False)
check("R3", "Agent avec users.toggle ne peut pas agir sur un compte administrateur (403)", r.status_code == 403)
t = csrf(admin, "roles")
admin.post(url("roles_update"), data={"csrf_token": t, "perms[1][]": ["profile.manage"], "perms[2][]": ["profile.manage", "users.view"]})
r = agent.get(url("users"))
check("R4", "Permission retirée -> effet immédiat (bouton disparu)", "action=user_toggle" not in r.text)

# ---------------------------------------------------------------- BILAN
ok = sum(1 for r in RESULTS if r[2])
print(f"\n{ok}/{len(RESULTS)} tests réussis")
sys.exit(0 if ok == len(RESULTS) else 1)
