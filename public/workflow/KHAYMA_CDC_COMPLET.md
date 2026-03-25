# KHAYMA — CAHIER DES CHARGES COMPLET
## Plateforme SaaS Multi-Tenant de Gestion d'Entreprises Africaines
**Version :** 1.0  
**Date :** Mars 2026  
**Statut :** Validé par quiz interactif  

---

# TABLE DES MATIÈRES

1. [Vision & Identité du Projet](#1-vision--identité-du-projet)
2. [Marché Cible](#2-marché-cible)
3. [Modèle Commercial](#3-modèle-commercial)
4. [Architecture Technique](#4-architecture-technique)
5. [Infrastructure & Hébergement](#5-infrastructure--hébergement)
6. [Sécurité](#6-sécurité)
7. [Site Vitrine](#7-site-vitrine)
8. [Backoffice Super Admin](#8-backoffice-super-admin)
9. [Espace Entreprise — Fonctionnalités Transversales](#9-espace-entreprise--fonctionnalités-transversales)
10. [Module Restaurant / Fast-food](#10-module-restaurant--fast-food)
11. [Module Quincaillerie](#11-module-quincaillerie)
12. [Module Boutique / POS](#12-module-boutique--pos)
13. [Module Location](#13-module-location)
14. [Notifications & Alertes](#14-notifications--alertes)
15. [Rapports & Exports](#15-rapports--exports)
16. [Intégrations Externes](#16-intégrations-externes)
17. [Identité Visuelle](#17-identité-visuelle)
18. [Conformité Légale](#18-conformité-légale)
19. [Support Client](#19-support-client)
20. [Roadmap & Planning](#20-roadmap--planning)
21. [Phase 2 — Vision Future](#21-phase-2--vision-future)
22. [Schéma Base de Données](#22-schéma-base-de-données)

---

# 1. VISION & IDENTITÉ DU PROJET

## 1.1 Nom de la plateforme

**KHAYMA**

- Origine : Arabe/Swahili — *"khaïma"* (خيمة) = **la tente**
- Symbolique : La tente est le lieu historique du commerce, de la rencontre et des échanges en Afrique
- Slogan : *"Khayma — Là où votre business prend vie."*
- Domaine cible : `khayma.com` ou `khayma.africa`

## 1.2 Définition

Khayma est une plateforme **SaaS multi-tenant** permettant aux entreprises africaines de gérer intégralement leurs activités via des **modules métiers spécialisés** accessibles par abonnement.

## 1.3 Objectifs stratégiques

- Digitaliser les PME et entreprises informelles africaines
- Centraliser toutes les opérations métier en un seul outil
- Offrir une solution simple, accessible et puissante
- Créer une plateforme scalable à l'échelle africaine
- Démarrer au Sénégal, s'étendre progressivement

## 1.4 Proposition de valeur

| Pour qui | Problème résolu | Bénéfice |
|---|---|---|
| Restaurateurs | Suivi ventes et dépenses manuel | Rapports automatiques, caisse digitale |
| Quincaillers | Stock complexe, crédit client | Gestion multi-unités, suivi dettes |
| Commerçants | Inventaire et caisse papier | POS digital, alertes stock |
| Agences location | Contrats et plannings dispersés | Calendrier, alertes, PDF automatiques |

---

# 2. MARCHÉ CIBLE

## 2.1 Géographie

- **Phase 1 :** Sénégal uniquement
- **Phase 2 :** Afrique de l'Ouest francophone (Mali, Côte d'Ivoire, Burkina Faso...)
- **Phase 3 :** Extension continentale

## 2.2 Profil des clients

| Segment | Description | Plan recommandé |
|---|---|---|
| Micro-entreprise | Boutique solo, 1-2 employés | Starter |
| PME informelle | Restaurant familial, 3-9 employés | Business |
| PME structurée | Entreprise organisée, 10+ employés | Pro |
| Grande entreprise | Multi-activités, besoins sur mesure | Custom |

## 2.3 Devise & Langue

- **Devise principale :** XOF (Franc CFA) — modifiable par entreprise
- **Langue interface :** Français uniquement (Phase 1)
- **TVA Sénégal :** 18% — configurable et désactivable par entreprise

---

# 3. MODÈLE COMMERCIAL

## 3.1 Principe fondamental

> Chaque entreprise cliente souscrit à **UN SEUL module** de son choix.  
> Les fonctionnalités disponibles varient selon le **niveau d'abonnement** choisi.  
> Les prix et limites de chaque niveau sont **configurables par le Super Admin**.

## 3.2 Plans d'abonnement

| Plan | Produits | Transactions/mois | Stockage | Utilisateurs | API calls/min |
|---|---|---|---|---|---|
| **Starter** | 300 | 1 000 | 1 Go | 2 | 30 |
| **Business** | 2 000 | 10 000 | 5 Go | 7 | 100 |
| **Pro** | Illimité | Illimité | 20 Go | 20 | 300 |
| **Custom** | Sur mesure | Sur mesure | Sur mesure | Sur mesure | Sur mesure |

*Toutes ces limites sont configurables depuis le backoffice admin.*

## 3.3 Périodes de facturation

- Mensuel
- Trimestriel *(réduction applicable)*
- Annuel *(réduction applicable)*

## 3.4 Essai gratuit

- **Durée :** 7 jours maximum
- **Accès :** Module complet en mode essai
- **Données de démo :** Pré-remplies, effaçables en un clic

## 3.5 Frais d'installation

- Variables selon le module choisi
- Configurables depuis le backoffice
- Facturés une seule fois à l'activation

## 3.6 Sources de revenus

| Source | Description |
|---|---|
| Abonnements récurrents | Mensuel / Trimestriel / Annuel |
| Frais d'installation | Variable par module |
| Support premium | Formation sur site payante |
| Développement sur mesure | Fonctionnalités spécifiques *(optionnel)* |

## 3.7 Paiement des abonnements Khayma

- **Plateforme :** PayDunya *(agrégateur sénégalais)*
- **Méthodes couvertes :** Wave, Orange Money, Free Money, cartes bancaires
- **Facturation automatique :** PDF généré et envoyé par email à chaque paiement

## 3.8 Cycle de vie client

```
Inscription (7j essai gratuit)
    ↓
Activation abonnement (paiement via PayDunya)
    ↓
Utilisation normale
    ↓
[Non-renouvellement] → Grâce 3 jours → Lecture seule 7 jours → Blocage total
    ↓
[Résiliation] → Export données (PDF/Excel) → Suppression après 90 jours
    ↓
[Réactivation] → Toutes les données restaurées (si < 90 jours)
```

---

# 4. ARCHITECTURE TECHNIQUE

## 4.1 Stack technologique

| Couche | Technologie | Justification |
|---|---|---|
| Backend | **Laravel 11+** (PHP 8.3+) | Robuste, écosystème riche |
| Frontend | **Vue.js 3** + **Inertia.js** | Réactif, performant, SPA |
| CSS Framework | **Tailwind CSS** | Utility-first, cohérent |
| Base de données | **PostgreSQL 16** | Robuste, RLS natif, relations complexes |
| Cache | **Redis** | Sessions, queues, cache |
| Queue | **Laravel Horizon** (Redis) | Jobs asynchrones |
| Auth | **Laravel Sanctum** | API tokens, SPA auth |
| Permissions | **Spatie Laravel Permission** | RBAC granulaire |
| Recherche | **Laravel Scout** | Recherche full-text |
| PDF | **Laravel DomPDF** | Génération PDF |
| Storage | **Cloudflare R2** | Compatible S3, économique |

## 4.2 Architecture Multi-Tenant

**Stratégie retenue : Shared Database, Shared Schema**

- Toutes les tables métier contiennent `company_id`
- **Row-Level Security (RLS)** PostgreSQL activé sur toutes les tables
- **Middleware Laravel** `ResolveTenant` injecte automatiquement le `company_id` dans toutes les requêtes
- **Global Scope Eloquent** sur tous les modèles pour filtrage automatique
- Isolation totale et non négociable entre tenants

```php
// Exemple middleware
class ResolveTenant {
    public function handle($request, $next) {
        $subdomain = extractSubdomain($request->host());
        $company = Company::where('slug', $subdomain)->firstOrFail();
        app()->instance('currentCompany', $company);
        return $next($request);
    }
}
```

## 4.3 Accès multi-tenant

- URL : `{slug-entreprise}.khayma.com`
- Exemple : `restaurantawa.khayma.com`
- Wildcard DNS configuré sur OVH
- SSL automatique via Let's Encrypt (wildcard)

## 4.4 API REST

- Versioning : `/api/v1/`
- Format réponse standardisé :
```json
{
  "success": true,
  "data": {},
  "message": "...",
  "meta": { "pagination": {} }
}
```
- Authentification : Bearer Token (Sanctum)
- Rate limiting : configurable par plan
- Documentation : Laravel Scribe (auto-générée)
- API publique optionnelle pour partenaires tiers

---

# 5. INFRASTRUCTURE & HÉBERGEMENT

## 5.1 Hébergement

| Composant | Service | Détail |
|---|---|---|
| Serveur principal | **OVH VPS** | VPS SSD 4-8 vCPU, 16-32 Go RAM |
| Web server | **Nginx** | Reverse proxy + SSL |
| SSL | **Let's Encrypt** | Wildcard `*.khayma.com` |
| CDN | **Cloudflare** | Cache, protection DDoS |
| Stockage fichiers | **Cloudflare R2** | Compatible S3, sans frais egress |
| Email transactionnel | **Mailgun** | Fiable, bon délivrabilité |
| SMS & OTP | **Twilio** | Couverture internationale + Sénégal |

## 5.2 Environnement de développement

- **OS :** Windows + XAMPP
- **Contrôle version :** Git + GitHub/GitLab
- **Local dev :** Laravel Sail ou XAMPP
- **CI/CD :** GitHub Actions

## 5.3 Sauvegardes automatiques

| Type | Fréquence | Rétention |
|---|---|---|
| Base de données | Quotidienne | 7 jours |
| Base de données | Hebdomadaire | 4 semaines |
| Fichiers | Quotidienne | 7 jours |

## 5.4 Environnements

- **Production :** `khayma.com` (OVH VPS)
- **Staging :** `staging.khayma.com`
- **Local :** XAMPP / Laravel Sail

---

# 6. SÉCURITÉ

## 6.1 Isolation des données (NON NÉGOCIABLE)

- `company_id` présent dans **toutes** les tables métier
- RLS PostgreSQL activé
- Global Scope Eloquent sur tous les modèles
- Middleware de résolution de tenant sur toutes les routes
- Tests d'isolation automatisés dans la CI/CD

## 6.2 Authentification

| Méthode | Détail |
|---|---|
| Email + Mot de passe | Bcrypt hashing (Laravel par défaut) |
| Téléphone + OTP | Code SMS via Twilio, validité 5 minutes |
| 2FA | Optionnelle (TOTP via Google Authenticator) |
| Déconnexion auto | 2 heures d'inactivité |
| Récupération | Par email uniquement |
| Tentatives | Blocage après 5 tentatives échouées |

## 6.3 Chiffrement

- Mots de passe : Bcrypt
- Tokens : Chiffrés en base
- Fichiers sensibles (contrats) : Chiffrés sur Cloudflare R2
- Communications : HTTPS obligatoire (HSTS activé)

## 6.4 Accès Super Admin aux données

- Le Super Admin voit uniquement les **métadonnées** (stats, abonnements, logs)
- Aucun accès aux données métier des clients
- Toute action admin est loggée dans l'audit trail

## 6.5 Conformité RGPD

- Politique de confidentialité affichée
- Consentement explicite à l'inscription
- Droit à l'export des données (PDF/Excel)
- Droit à l'effacement (après résiliation, 90 jours)

## 6.6 Audit Trail

Chaque action critique est enregistrée avec :
- **Qui** (user_id + nom)
- **Quoi** (action + données modifiées)
- **Quand** (timestamp précis)
- **Où** (IP + user-agent)

Actions tracées : suppression, modification prix, annulation vente, changement rôle, accès admin, modifications paramètres.

## 6.7 Autres mesures

| Mesure | Détail |
|---|---|
| OWASP Top 10 | Respect complet |
| SQL Injection | Requêtes préparées (Eloquent ORM) |
| XSS | Échappement automatique (Vue.js + Blade) |
| CSRF | Token CSRF sur toutes les requêtes |
| Rate Limiting | Par IP + par token API |
| Headers sécurité | CSP, X-Frame-Options, etc. |

---

# 7. SITE VITRINE

## 7.1 Objectif

Présenter Khayma, convaincre les prospects et permettre l'inscription ou la prise de contact.

## 7.2 Pages

| Page | Contenu |
|---|---|
| **Accueil** | Hero section, modules présentés, témoignages, CTA inscription |
| **Modules / Fonctionnalités** | Détail de chaque module avec screenshots |
| **Tarifs** | Plans configurables affiché dynamiquement depuis le backoffice |
| **Démo en ligne** | Accès démo sans inscription (données fictives) |
| **Contact / RDV** | Formulaire + calendrier de prise de RDV |
| **FAQ** | Questions fréquentes par module |
| **Blog / Actualités** | Articles, guides, actualités |
| **CGU & Politique de confidentialité** | Pages légales |
| **Mentions légales** | Informations légales Khayma |

## 7.3 Fonctionnalités

- Inscription en ligne en autonome **ET** via contact commercial
- Démo interactive sans inscription
- Chat WhatsApp Business intégré (bouton flottant)
- Formulaire de contact + prise de RDV planifié
- Google Analytics activé
- Responsive (mobile-first)
- SEO optimisé

## 7.4 Processus d'inscription

```
1. Client visite khayma.com
2. Choisit son module + plan
3. Remplit formulaire (nom entreprise, email, téléphone, secteur)
4. Accepte CGU + Politique de confidentialité
5. Reçoit email de bienvenue avec accès à l'essai 7 jours
6. Sous-domaine créé automatiquement : {slug}.khayma.com
7. Wizard de configuration au 1er accès
8. À la fin des 7 jours → invitation à souscrire via PayDunya
```

---

# 8. BACKOFFICE SUPER ADMIN

## 8.1 Accès

- URL : `admin.khayma.com`
- Accès réservé au Super Admin uniquement
- 2FA obligatoire pour le Super Admin

## 8.2 Tableau de bord global

- Nombre d'entreprises actives / en essai / suspendues
- Revenus du mois (MRR) et croissance
- Nouveaux clients cette semaine
- Tickets support en attente
- Graphiques : évolution abonnements, revenus, modules populaires
- Carte : localisation des clients

## 8.3 Gestion des entreprises

| Fonctionnalité | Détail |
|---|---|
| Créer une entreprise | Manuellement (activation commerciale) |
| Voir / modifier | Infos, module, plan, utilisateurs |
| Activer / suspendre / supprimer | Avec motif et notification automatique |
| Accéder aux métadonnées | Stats, logs, abonnements *(pas les données métier)* |
| Réinitialiser mot de passe owner | En cas de blocage |
| Prolonger essai | Manuellement si besoin |

## 8.4 Gestion des abonnements & paiements

- Voir tous les paiements (PayDunya)
- Activer/désactiver manuellement un abonnement
- Changer le plan d'une entreprise
- Historique complet des transactions
- Exportable en Excel/CSV

## 8.5 Configuration des plans & prix

- Modifier les prix de chaque plan (Starter/Business/Pro)
- Modifier les limites (produits, users, stockage...)
- Modifier les frais d'installation par module
- Activer/désactiver des fonctionnalités par plan
- Configurer les durées de période de grâce

## 8.6 Gestion du support

- Voir tous les tickets ouverts
- Répondre aux tickets
- Changer la priorité (Urgent/Normal/Faible)
- Clôturer les tickets
- Statistiques : temps de réponse moyen, satisfaction

## 8.7 Statistiques globales

- Revenus par module
- Modules les plus utilisés
- Taux de churn (résiliation)
- Taux de conversion essai → payant
- Évolution mensuelle

## 8.8 Annonces & Communications

- Envoyer une notification à tous les clients
- Envoyer à un segment (ex: tous les clients Restaurant)
- Annoncer une maintenance programée
- Publier un article sur le blog

## 8.9 Gestion du contenu du site vitrine

- Modifier les textes de la page d'accueil
- Gérer les articles de blog
- Mettre à jour la FAQ
- Modifier les pages légales

## 8.10 Logs système & sécurité

- Tentatives de connexion suspectes (nombreuses échouées)
- Erreurs applicatives (500, etc.)
- Accès inhabituels
- Journal des actions admin

---

# 9. ESPACE ENTREPRISE — FONCTIONNALITÉS TRANSVERSALES

*Ces fonctionnalités sont communes à tous les modules.*

## 9.1 Authentification & Accès

- Connexion par email/mot de passe **OU** téléphone/OTP
- URL personnalisée : `{slug}.khayma.com`
- 2FA optionnelle
- Déconnexion automatique après 2h d'inactivité

## 9.2 Rôles & Permissions

| Rôle | Accès |
|---|---|
| **Owner** | Accès complet à son entreprise + paramètres + rapports |
| **Manager** | Opérations + rapports (sans paramètres sensibles) |
| **Caissier** | Ventes / caisse uniquement |
| **Magasinier** | Stock uniquement |
| **Agent** | Contrats / clients (module Location) |
| **Chef** | Gestion menu + commandes (module Restaurant) |
| **Secrétaire** | Saisie commandes + fournisseurs (module Restaurant) |

- L'Owner peut créer des **sous-rôles personnalisés** avec permissions granulaires
- Un utilisateur peut appartenir à **plusieurs entreprises**
- Journal d'activité complet par utilisateur

## 9.3 Dashboard entreprise

**Widgets affichés :**
- Ventes du jour / semaine / mois (avec comparaison période précédente)
- Dépenses du jour
- Bénéfice net estimé
- Alertes stock critiques
- Paiements en retard (location/crédit)
- Dernières transactions
- Graphiques : courbe ventes, camembert par catégorie, barres comparatives

**Modes :** Clair / Sombre (dark mode)

## 9.4 Personnalisation de l'espace

| Paramètre | Détail |
|---|---|
| Logo | Upload, affiché dans l'app et sur tous les documents |
| Couleurs | Couleur primaire personnalisable |
| Nom entreprise | Sur tous les documents (factures, reçus, contrats) |
| Infos légales | NINEA, adresse, téléphone, email (pied de page docs) |
| Devise | XOF par défaut, changeable |
| Format numérotation | Ex: `FAC-2026-0001` ou `BOT/01/2026` |
| TVA | 18% par défaut, configurable ou désactivable |
| Fuseau horaire | Dakar (UTC+0) par défaut |

## 9.5 Gestion des clients finaux

| Fonctionnalité | Détail |
|---|---|
| Fiche client | Nom + Téléphone (obligatoires) + Email + Adresse + NIF (optionnels) |
| Client anonyme | "Client de passage" utilisable sans fiche |
| Catégories | VIP / Normal / Professionnel |
| Historique | Toutes les transactions par client |
| Solde / crédit | Suivi des dettes en temps réel |
| Fidélité | Système de points configurable |
| Import Excel | Modèle téléchargeable + import en lot |
| Export | Liste clients en Excel/CSV |

## 9.6 Gestion des fournisseurs

| Fonctionnalité | Détail |
|---|---|
| Fiche fournisseur | Nom + Téléphone (obligatoires) + Email + Adresse + NINEA + RIB (optionnels) |
| Bons de commande | Création, envoi par email/WhatsApp |
| Réceptions | Enregistrement des livraisons contre bon de commande |
| Historique achats | Par fournisseur, sur toute période |
| Solde fournisseur | Suivi des dettes en temps réel |
| Évaluation | Note /5 + commentaire |
| Import Excel | Modèle téléchargeable + import en lot |

## 9.7 Gestion du stock (commun)

| Fonctionnalité | Détail |
|---|---|
| Mouvements | Entrées (achats) + Sorties (ventes) + Ajustements |
| Historique complet | Par produit, par période |
| Inventaire physique | Comptage + rapport d'écarts + correction |
| Stock négatif | Configurable par entreprise (bloqué par défaut) |
| Alerte seuil minimum | Notification quand stock < seuil défini |
| Valorisation | Valeur totale en XOF (méthode PMP) |

## 9.8 Caisse

| Fonctionnalité | Détail |
|---|---|
| Ouverture de caisse | Fond de caisse initial obligatoire |
| Fermeture de caisse | Comptage final, rapport de clôture |
| Modes paiement | Cash / Wave / Orange Money / Free Money / Carte / Autre |
| Annulation vente | Motif obligatoire + traçabilité |
| Remises | % ou montant fixe |
| Avoir / note de crédit | Générable et utilisable sur prochain achat |
| TVA | Affiché HT/TTC selon configuration |
| Impression | Ticket thermique 80mm (ESC/POS) + PDF |

## 9.9 Taxes & Comptabilité

- TVA 18% configurable (activable/désactivable)
- Prix saisissables en HT ou TTC
- Rapport TVA collectée par période
- Pas de module comptabilité complet (Phase 1)

## 9.10 Notifications

**Canaux :** In-app + Email + SMS

| Alerte | Destinataire | Canal |
|---|---|---|
| Stock produit < seuil | Owner / Manager | In-app + Email |
| Paiement client en retard | Owner | In-app + Email + SMS |
| Contrat location expirant (7j) | Owner | In-app + Email + SMS |
| Abonnement Khayma expirant (3j) | Owner | In-app + Email + SMS |
| Rapport journalier | Owner | Email |
| Rapport mensuel | Owner | Email |
| Nouvelle commande | Caissier / Manager | In-app |

## 9.11 Onboarding

À la première connexion, un **wizard en 5 étapes** guide l'utilisateur :
1. Informations entreprise (nom, logo, adresse)
2. Paramètres de base (devise, TVA, format numérotation)
3. Créer les premiers produits/plats (ou importer depuis Excel)
4. Inviter les premiers utilisateurs
5. Paramétrer les alertes

- Email de bienvenue automatique avec guide PDF
- Données de démo pré-remplies (bouton "Effacer les données de démo")
- Vidéos tutoriels courtes intégrées par fonctionnalité
- Base de connaissances accessible depuis l'app (icône "?" en permanence)

---

# 10. MODULE RESTAURANT / FAST-FOOD

**Priorité :** 1 (premier module à développer)

## 10.1 Contexte métier

Vise les restaurants et fast-foods sénégalais : service en salle, au comptoir, et livraison. Menu préparé à l'avance selon les services (matin/midi/soir).

## 10.2 Profils utilisateurs

| Profil | Rôle |
|---|---|
| **Owner** | Accès complet + rapports + paramètres |
| **Chef** | Gestion menu, plats, catégories |
| **Secrétaire** | Enregistrement commandes fournisseurs |
| **Caissier** | Prise de commande + encaissement |

## 10.3 Gestion du Menu

| Fonctionnalité | Détail |
|---|---|
| Catégories | Créer/modifier/supprimer des catégories de plats |
| Plats | Nom, description, prix, photo, catégorie, disponibilité |
| Disponibilité par service | Un plat peut être actif seulement le matin, midi ou soir |
| Produits additionnels | Boissons, desserts, extras |
| Prix spéciaux | Promotions temporaires sur un plat |

## 10.4 Prise de commande

**Types de commande :**
- Sur table (numéro de table)
- À emporter
- Livraison (adresse + nom livreur)

**Flux :**
```
1. Caissier choisit le type (table / à emporter / livraison)
2. Sélectionne les plats (1 ou plusieurs)
3. Applique une remise si nécessaire
4. Choisit le mode de paiement (Cash/Wave/OM/Autre)
5. Paiement avant ou après (selon choix)
6. Impression ticket ou envoi WhatsApp
7. Commande enregistrée dans les rapports
```

**Caractéristiques :**
- Interface caisse rapide et tactile (utilisable sur tablette)
- Recherche rapide de plats par nom
- Pas de gestion de cuisine (pas d'écran cuisine)
- Annulation commande avec motif

## 10.5 Gestion des Services

| Service | Horaires configurables |
|---|---|
| Matin | Ex: 07h00 - 11h00 |
| Midi | Ex: 11h00 - 15h00 |
| Soir | Ex: 17h00 - 22h00 |

- Ouverture/fermeture de caisse par service
- Rapport de ventes par service

## 10.6 Gestion des Commandes Fournisseurs

*(ingrédients et matières premières — sans gestion des recettes)*

| Fonctionnalité | Détail |
|---|---|
| Fiche fournisseur | Informations complètes |
| Bon de commande | Créer commande avec produits et quantités |
| Réception | Enregistrer la livraison avec montant payé |
| Paiement partiel | Possibilité de régler en plusieurs fois |
| Solde fournisseur | Suivi dette en temps réel |
| Historique | Tous les achats par fournisseur |

*Note : Les ingrédients sont commandés mais leur consommation par plat n'est PAS gérée automatiquement.*

## 10.7 Dépenses diverses

- Enregistrement des charges : gaz, électricité, salaires, entretien, autres
- Catégories de dépenses personnalisables
- Montant + date + description + justificatif (photo optionnelle)

## 10.8 Rapports Restaurant

| Rapport | Détail |
|---|---|
| Ventes du jour | Total par service (matin/midi/soir) |
| Ventes par plat | Classement des plats les plus vendus |
| Ventes par type | Table vs À emporter vs Livraison |
| Ventes par mode paiement | Cash / Wave / OM / Autre |
| Dépenses fournisseurs | Par fournisseur, par période |
| Autres charges | Par catégorie, par période |
| Bénéfice net | Ventes - Toutes dépenses |
| Commandes annulées | Liste avec motifs |
| Rapport mensuel / annuel | Évolution, comparaisons |
| Rapport personnalisé | Du JJ/MM/AAAA au JJ/MM/AAAA |

**Rapports automatiques :**
- Résumé du soir envoyé par email chaque jour
- Rapport mensuel envoyé le 1er de chaque mois

---

# 11. MODULE QUINCAILLERIE

**Priorité :** 2

## 11.1 Contexte métier

Vise les quincailleries et magasins de matériaux de construction : produits de gros volume, unités multiples, vente au comptoir et sur devis, gestion de crédit client et dette fournisseur.

## 11.2 Gestion des Produits

| Fonctionnalité | Détail |
|---|---|
| Types de produits | Construction, outillage, électricité, plomberie, etc. — tout type |
| Catégories | Hiérarchiques (catégorie > sous-catégorie) |
| Unités multiples | Un produit peut être vendu en sac, tonne, kg, mètre, barre, palette, pièce... |
| Conversion d'unités | Ex: 1 palette = 50 sacs de ciment |
| Prix par unité | Prix différent selon l'unité de vente |
| Code produit | Référence interne |
| Fournisseurs associés | Un produit peut avoir plusieurs fournisseurs avec des prix différents |
| Seuil d'alerte | Stock minimum configurable par produit |

## 11.3 Gestion du Stock

| Fonctionnalité | Détail |
|---|---|
| Entrées stock | Via réception commande fournisseur |
| Sorties stock | Via vente ou ajustement manuel |
| Inventaire physique | Procédure complète avec rapport d'écart |
| Historique mouvements | Entrées + sorties + ajustements |
| Pertes / casse | Enregistrement avec motif |
| Valorisation | Valeur totale en XOF (PMP) |
| Alertes | Notification quand stock < seuil minimum |
| Multi-unités | Stock suivi dans l'unité de référence |

## 11.4 Ventes

### Vente comptoir (rapide)
```
1. Rechercher produit (nom ou référence)
2. Choisir l'unité (sac/tonne/kg...)
3. Saisir la quantité
4. Appliquer remise (% ou montant)
5. Choisir mode paiement
6. Imprimer ticket / générer facture
```

### Vente sur devis
```
1. Créer devis (client + liste produits et quantités)
2. Générer PDF du devis
3. Envoyer au client (email ou WhatsApp)
4. Valider le devis → transformation en bon de commande/facture
5. Suivi du paiement (acompte + solde)
```

### Credit client
- Vente à crédit : premier paiement partiel, reste à payer
- Tableau de suivi des créances par client
- Alertes paiements en retard
- Historique des paiements reçus

### Livraison
- Option livraison à cocher sur la vente
- Frais de livraison ajoutables au montant total
- Adresse de livraison enregistrée
- Suivi statut : préparé / livré / confirmé

## 11.5 Gestion des Fournisseurs (avancée)

| Fonctionnalité | Détail |
|---|---|
| Bons de commande | Créer commande → envoi email/WhatsApp → réception |
| Multi-fournisseurs | Même produit chez différents fournisseurs à des prix différents |
| Dettes fournisseurs | Achats à crédit, suivi du solde à payer |
| Retours fournisseur | Produits défectueux ou surplus retournés |
| Historique achats | Par fournisseur, par produit, par période |
| Évaluation fournisseur | Note /5 + commentaire |

## 11.6 Rapports Quincaillerie

- Ventes du jour / semaine / mois
- Ventes par produit (top ventes)
- Ventes par catégorie
- Devis en attente de validation
- Créances clients (liste des dettes)
- Dettes fournisseurs
- Mouvements de stock
- Rapport inventaire
- Bénéfice net
- Rapport personnalisé (période custom)

---

# 12. MODULE BOUTIQUE / POS

**Priorité :** 3

## 12.1 Contexte métier

Solution polyvalente pour tout type de boutique : épicerie, vêtements, téléphonie, cosmétiques, etc. Interface caisse rapide sur PC et tablette.

## 12.2 Gestion des Produits

| Fonctionnalité | Détail |
|---|---|
| Catégories | Hiérarchiques |
| Produits | Nom, description, prix achat/vente, photo, catégorie |
| Variantes | Taille, couleur, etc. — stock suivi par variante |
| Code-barres | Saisie manuelle ou scan via lecteur USB |
| Prix promotionnels | Réduction temporaire avec dates de début/fin |
| Import Excel | Importer catalogue produits en lot |

## 12.3 Multi-dépôts / Multi-entrepôts

- Une boutique peut avoir plusieurs dépôts (magasin principal + réserve + entrepôt)
- Stock suivi par dépôt
- Transferts inter-dépôts avec traçabilité
- Vente depuis un dépôt spécifique

## 12.4 Interface Caisse (POS)

**Design :** Rapide, tactile, utilisable sur tablette et PC

```
[Recherche produit / scan code-barres]
    ↓
[Sélection produit → quantité]
    ↓
[Panier visible en temps réel]
    ↓
[Remise % ou montant fixe]
    ↓
[Sélection client (ou passage)]
    ↓
[Mode paiement: Cash/Wave/OM/Carte/Crédit]
    ↓
[Impression ticket 80mm ou PDF]
```

## 12.5 Programme de Fidélité

- Accumulation de points à chaque achat (configurable : ex: 1 point par 1000 XOF)
- Seuils de récompense configurables (ex: 100 points = 2000 XOF de réduction)
- Consultation solde points par client
- Utilisation des points comme remise en caisse
- Historique des points gagnés / dépensés

## 12.6 Gestion des Achats Fournisseurs

- Bons de commande fournisseurs
- Réception marchandises avec mise à jour stock automatique
- Suivi dettes fournisseurs
- Historique achats

## 12.7 Rapports Boutique

- Ventes du jour / semaine / mois
- Ventes par produit et catégorie
- Ventes par mode paiement
- Stock disponible (par dépôt)
- Mouvements de stock
- Inventaire (écart physique/théorique)
- Créances clients
- Dettes fournisseurs
- Bénéfice net (prix vente - prix achat)
- Produits sous alerte stock
- Rapport fidélité (points émis / utilisés)
- Rapport personnalisé

---

# 13. MODULE LOCATION

**Priorité :** 4

## 13.1 Contexte métier

Gestion de biens à louer : véhicules (courte et longue durée), immobilier (résidentiel et commercial), matériels (chantier, événementiel, électroménager, etc.).

## 13.2 Gestion des Biens

| Fonctionnalité | Détail |
|---|---|
| Types de biens | Véhicule / Immobilier / Matériel / Autre |
| Fiche bien | Nom, description, photos, prix/jour ou prix/mois, statut |
| Statuts | Disponible / Loué / En maintenance / Hors service |
| Caractéristiques | Selon le type (marque, modèle, superficie, etc.) |
| Documents | Upload des documents liés au bien (carte grise, titre foncier...) |
| État des lieux | Optionnel — description + photos au départ et au retour |

## 13.3 Planning & Disponibilité

- **Calendrier visuel** : vue mensuelle/hebdomadaire des biens
- Couleurs selon statut : vert (disponible) / orange (loué) / rouge (maintenance)
- Vérification de conflit de réservation automatique
- Vue par type de bien (tous les véhicules, tous les appartements...)

## 13.4 Gestion des Contrats

| Fonctionnalité | Détail |
|---|---|
| Création contrat | Client + Bien + Période (début/fin) + Montant + Conditions |
| PDF contrat | Généré automatiquement, téléchargeable |
| Caution | Montant optionnel, enregistré séparément |
| État des lieux | Départ et retour (description + photos) |
| Renouvellement | Manuel ou automatique (optionnel) |
| Statuts contrat | En cours / Terminé / En retard / Annulé / Renouvelé |

## 13.5 Gestion des Paiements

| Fonctionnalité | Détail |
|---|---|
| Échelonnement | Loyers mensuels, trimestriels, annuels ou personnalisés |
| Suivi échéances | Liste des paiements attendus avec statut (payé/en attente/en retard) |
| Enregistrement paiement | Date + montant + mode paiement + reçu |
| Paiement partiel | Acompte initial + solde |
| Restitution caution | Enregistrement du remboursement de caution |
| Quittance | Génération PDF de quittance de paiement |

## 13.6 Alertes Location

| Alerte | Délai | Canal |
|---|---|---|
| Contrat expirant | 7 jours avant | In-app + Email + SMS |
| Paiement en retard | Le jour J dépassé | In-app + Email + SMS |
| Bien disponible (fin contrat) | À la fin du contrat | In-app |
| Renouvellement à confirmer | 14 jours avant fin | In-app + Email |

## 13.7 Rapports Location

- Biens loués vs disponibles
- Revenus locatifs par période
- Revenus par type de bien
- Paiements reçus vs attendus
- Créances (paiements en retard)
- Contrats actifs, terminés, renouvelés
- Taux d'occupation (% du temps loué)
- Rapport personnalisé

---

# 14. NOTIFICATIONS & ALERTES

## 14.1 Canaux disponibles

- **In-app** : Cloche de notification dans l'interface
- **Email** : Via Mailgun
- **SMS** : Via Twilio

## 14.2 Alertes pour les entreprises clientes

| Alerte | Destinataire | Canaux |
|---|---|---|
| Stock < seuil minimum | Owner / Manager | In-app + Email |
| Paiement client en retard | Owner | In-app + Email + SMS |
| Contrat location expirant dans 7j | Owner | In-app + Email + SMS |
| Abonnement Khayma expirant dans 3j | Owner | In-app + Email + SMS |
| Rapport journalier automatique | Owner | Email |
| Rapport mensuel le 1er | Owner | Email |
| Nouvelle commande (livraison) | Manager / Caissier | In-app |
| Tentative connexion suspecte | Owner | Email + SMS |

## 14.3 Alertes pour le Super Admin

| Alerte | Canal |
|---|---|
| Nouveau client inscrit | In-app + Email |
| Paiement abonnement reçu | In-app |
| Ticket support ouvert | In-app + Email |
| Tentative connexion suspecte | Email + SMS |
| Erreur système critique | Email |

---

# 15. RAPPORTS & EXPORTS

## 15.1 Formats d'export

- **PDF** : pour impression et archivage
- **Excel / CSV** : pour analyse et comptabilité

## 15.2 Périodes disponibles

- Aujourd'hui
- Cette semaine
- Ce mois
- Cette année
- **Période personnalisée** (du JJ/MM/AAAA au JJ/MM/AAAA)

## 15.3 Accès aux rapports

- Selon les permissions du rôle
- Owner : accès complet
- Manager : accès selon configuration
- Caissier / Magasinier : rapports limités à leur activité

## 15.4 Rapports automatiques

| Rapport | Fréquence | Canal |
|---|---|---|
| Résumé du jour | Chaque soir | Email |
| Résumé mensuel | 1er de chaque mois | Email |

## 15.5 Visualisations

| Type | Usage |
|---|---|
| Barres | Comparaisons (ventes par mois, par catégorie) |
| Courbes | Évolutions temporelles (CA semaine/mois) |
| Camembert | Répartitions (modes paiement, types commande) |
| Indicateurs KPI | Chiffres clés en gros sur le dashboard |

---

# 16. INTÉGRATIONS EXTERNES

| Service | Usage | Outil |
|---|---|---|
| Paiement abonnements | PayDunya (Wave, OM, Free Money) | API PayDunya |
| Email transactionnel | Notifications, rapports, factures | Mailgun |
| SMS & OTP | Authentification, alertes | Twilio |
| WhatsApp | Reçus, rappels paiement, bons commande | WhatsApp Business API |
| Impression thermique | Tickets de caisse | ESC/POS standard |
| Stockage fichiers | Photos, PDFs, logos | Cloudflare R2 |
| Analytics site vitrine | Comportement visiteurs | Google Analytics 4 |
| Chat support | Support in-app clients | Crisp |

---

# 17. IDENTITÉ VISUELLE

## 17.1 Positionnement

**Moderne avec une touche africaine chaleureuse** — une interface épurée ancrée dans les couleurs et l'énergie de l'Afrique.

## 17.2 Palette de couleurs

| Rôle | Couleur | Code hex |
|---|---|---|
| Primaire | Vert émeraude | `#10B981` |
| Secondaire | Orange doré | `#F59E0B` |
| Fond clair | Blanc cassé | `#F9FAFB` |
| Fond sombre (dark) | Bleu nuit | `#0F172A` |
| Texte principal | Gris foncé | `#1F2937` |
| Texte secondaire | Gris moyen | `#6B7280` |
| Succès | Vert | `#22C55E` |
| Danger / Alerte | Rouge | `#EF4444` |
| Avertissement | Ambre | `#F59E0B` |
| Info | Bleu | `#3B82F6` |

## 17.3 Typographie

- **Police principale :** Inter (Google Fonts — open source)
- **Tailles :** Scale harmonieux Tailwind (text-sm, text-base, text-lg, text-xl...)
- **Poids :** Regular (400) + Medium (500) + SemiBold (600) + Bold (700)

## 17.4 Iconographie

- **Librairie :** Heroicons (cohérent avec Tailwind CSS)
- **Style :** Outline par défaut, Solid pour les états actifs

## 17.5 Logo

- À concevoir : calligraphie stylisée du mot "Khayma" en vert émeraude
- Symbole : une silhouette de tente simplifiée moderne
- Formats : SVG (scalable) + PNG (512x512, 192x192, 64x64)
- Usage clair + usage sombre

---

# 18. CONFORMITÉ LÉGALE

## 18.1 Documents requis

| Document | Détail |
|---|---|
| CGU | Affichées à l'inscription, acceptation cochée obligatoire |
| Politique de confidentialité | Page dédiée, conforme RGPD |
| Mentions légales | À compléter avec NINEA et RC de Khayma |
| Contrat de service | Acceptation en ligne (clic = signature légale) |

## 18.2 Facturation Khayma → Clients

- Facture PDF automatique générée à chaque paiement d'abonnement
- Numérotation séquentielle
- Mentionner : nom, NINEA, adresse Khayma + détail de l'abonnement payé

## 18.3 Protection des données

- Données hébergées en Europe (OVH)
- Politique d'export : client peut exporter ses données à tout moment
- Politique de suppression : 90 jours après résiliation
- Aucune revente de données à des tiers

---

# 19. SUPPORT CLIENT

## 19.1 Canaux

| Canal | Disponibilité |
|---|---|
| Tickets in-app | 24h/24 (réponse sous 24h) |
| WhatsApp Business | Heures ouvrables |
| Email | `support@khayma.com` |

## 19.2 Système de tickets

- Ouvert depuis l'espace client avec : titre + description + pièce jointe
- Niveaux de priorité : Urgent / Normal / Faible
- Statuts : Ouvert / En cours / Résolu / Fermé
- Notification email à chaque mise à jour
- Historique complet consultable par le client

## 19.3 Niveaux de service

| Niveau | Temps de réponse | Inclus dans |
|---|---|---|
| Standard | 24 heures | Tous les plans |
| Premium | 4 heures | Formation sur site (payante) |

## 19.4 Base de connaissances

- Articles organisés par module
- Recherche plein texte
- Accessible depuis l'icône "?" dans l'app
- Rédigée et maintenue par le Super Admin
- Vidéos tutoriels courtes pour chaque fonctionnalité clé

---

# 20. ROADMAP & PLANNING

## 20.1 MVP — Lancement minimal (Premier jour)

**Priorité absolue pour aller sur le marché le plus vite possible :**

```
MVP = Site vitrine + Inscription en ligne + Module Boutique/POS basique
```

## 20.2 Phase 1 — Fondations (6 mois — Septembre 2026)

```
Mois 1-2 : Fondations techniques
├── Architecture multi-tenant (PostgreSQL + RLS)
├── Authentification (email/password + OTP)
├── Sous-domaines automatiques
├── Backoffice Super Admin (CRUD entreprises, modules, abonnements)
├── Système RBAC (Spatie Permission)
├── Site vitrine (toutes les pages)
└── Intégration PayDunya (abonnements)

Mois 3 : Module Restaurant
├── Gestion menu et catégories
├── Interface caisse (table / emporter / livraison)
├── Commandes fournisseurs
├── Dépenses diverses
├── Rapports complets
└── Impression tickets ESC/POS

Mois 4 : Module Quincaillerie
├── Gestion produits multi-unités
├── Vente comptoir + devis
├── Crédit client + dettes fournisseurs
├── Livraison avec frais
├── Bons de commande fournisseurs
└── Rapports

Mois 5 : Module Boutique / POS
├── Catalogue produits + variantes + code-barres
├── Interface POS rapide (PC + tablette)
├── Programme fidélité
├── Multi-dépôts
├── Import/export Excel
└── Rapports

Mois 6 : Module Location + Finitions
├── Gestion biens (véhicule / immobilier / matériels)
├── Contrats PDF
├── Planning visuel
├── Suivi paiements échelonnés
├── Alertes automatiques
├── Tests complets + sécurité + déploiement production
└── Documentation
```

## 20.3 Phase 2 (Trimestre 4 2026 — 2027)

```
├── Application mobile (Vue + Capacitor ou React Native)
│   ├── App gérants (rapports + alertes)
│   └── App caissiers (ventes)
├── Module Pharmacie
├── Module Salon de coiffure
├── Module École
├── Chatbot IA assistant (intégré dans l'app)
├── Multi-langues (English)
└── Expansion Afrique de l'Ouest
```

## 20.4 Phase 3 (2027+)

```
├── Extension continentale (Afrique anglophone)
├── Modules additionnels (sur demande marché)
├── API marketplace (partenaires tiers)
└── Version Enterprise
```

---

# 21. PHASE 2 — VISION FUTURE

## 21.1 Module Pharmacie

- Médicaments avec DCI, dosage, forme
- Gestion des dates d'expiration + alertes
- Ordonnances (référence, médecin, patient)
- Gestion par lot (numéro de lot)
- Réglementation : produits sous ordonnance vs en vente libre
- Stock par produit + alerte rupture
- Rapports : ventes, expirations proches, mouvements

## 21.2 Module Salon de Coiffure / Beauté

- Catalogue de prestations avec prix et durée
- Agenda / rendez-vous (vue journalière/hebdomadaire)
- Gestion des coiffeurs/esthéticiens (planning)
- Programme fidélité renforcé
- Historique beauté par client (dernière coupe, produits utilisés)
- Vente de produits en complément des services

## 21.3 Module École / Institution

- Inscription élèves / étudiants (dossier complet)
- Gestion frais de scolarité (paiement par tranches)
- Suivi des paiements + reçus
- Bulletins de notes (matières, coefficients, moyennes)
- Emploi du temps (classes + professeurs)
- Communication parents (notifications)
- Présences / absences

## 21.4 Application Mobile

- Framework : à décider (React Native ou Flutter)
- App Gérant : dashboard, rapports, alertes, notifications
- App Caissier : interface de vente optimisée mobile
- Authentification biométrique (empreinte/Face ID)
- Notifications push

## 21.5 Chatbot IA Assistant

- Intégré dans l'interface (bulle en bas à droite)
- Réponse aux questions sur les fonctionnalités
- Aide à la navigation ("Comment ajouter un produit ?")
- Analyse simple des données ("Quel est mon meilleur plat ce mois-ci ?")
- Basé sur GPT-4 API ou modèle open source
- Contexte : connait le module et les données de l'entreprise connectée

---

# 22. SCHÉMA BASE DE DONNÉES

## 22.1 Tables système (Khayma Global)

```sql
-- Entreprises clientes
companies
  id, name, slug, sector, phone, email, address, logo_url,
  primary_color, currency, timezone, ninea,
  subscription_status, trial_ends_at, created_at

-- Plans & Abonnements
plans
  id, name, code, max_products, max_users, max_storage_gb,
  max_transactions_month, api_rate_limit, price_monthly,
  price_quarterly, price_yearly, is_active

subscriptions
  id, company_id, plan_id, module_id, status,
  starts_at, ends_at, billing_period, amount_paid,
  payment_reference, created_at

-- Modules disponibles
modules
  id, name, code, description, icon,
  installation_fee, is_active

company_modules
  company_id, module_id, activated_at, activated_by

-- Utilisateurs
users
  id, name, email, phone, password,
  email_verified_at, phone_verified_at,
  two_factor_secret, is_active, created_at

company_users
  company_id, user_id, role_id, invited_by, joined_at

roles
  id, company_id, name, guard_name

permissions
  id, company_id, name, guard_name

-- Audit
audit_logs
  id, company_id, user_id, action, model_type, model_id,
  old_values, new_values, ip_address, user_agent, created_at

-- Notifications
notifications
  id, company_id, user_id, type, title, body,
  channel, is_read, sent_at, created_at

-- Support
support_tickets
  id, company_id, user_id, subject, status, priority,
  created_at, closed_at

support_messages
  id, ticket_id, user_id, body, attachments, created_at
```

## 22.2 Tables métier (toutes avec company_id)

```sql
-- Clients finaux
customers
  id, company_id, name, phone, email, address, ninea,
  category (vip/normal/pro), loyalty_points,
  outstanding_balance, created_at

-- Fournisseurs
suppliers
  id, company_id, name, phone, email, address, ninea, rib,
  rating, outstanding_balance, notes, created_at

-- Produits (commun)
products
  id, company_id, name, description, category_id,
  unit_id, purchase_price, selling_price, barcode,
  min_stock_alert, is_active, image_url, created_at

product_variants
  id, product_id, company_id, name, attributes (JSON),
  purchase_price, selling_price, barcode, created_at

categories
  id, company_id, name, parent_id, module, created_at

units
  id, company_id, name, symbol, base_unit_id, conversion_factor

-- Stock
stock_items
  id, company_id, product_id, variant_id, depot_id, quantity

depots
  id, company_id, name, address, is_default

stock_movements
  id, company_id, product_id, variant_id, depot_id,
  type (in/out/transfer/adjustment/loss), quantity,
  unit_cost, reference, notes, user_id, created_at

-- Ventes
sales
  id, company_id, customer_id, user_id, reference,
  type (counter/delivery/table/takeaway), status,
  subtotal, discount_amount, tax_amount, total,
  payment_status, notes, created_at

sale_items
  id, sale_id, company_id, product_id, variant_id,
  quantity, unit_price, discount, total

payments
  id, company_id, sale_id, amount, method (cash/wave/om/card/other),
  reference, notes, created_at

-- Dépenses
expenses
  id, company_id, category_id, amount, description,
  supplier_id, receipt_url, user_id, date, created_at

expense_categories
  id, company_id, name, created_at

-- Commandes fournisseurs
purchase_orders
  id, company_id, supplier_id, reference, status (draft/sent/received/partial),
  notes, ordered_at, expected_at

purchase_order_items
  id, purchase_order_id, company_id, product_id, quantity,
  unit_price, received_quantity

supplier_payments
  id, company_id, supplier_id, purchase_order_id,
  amount, method, reference, notes, created_at

-- Module Location
rental_assets
  id, company_id, name, type (vehicle/real_estate/equipment/other),
  description, photos (JSON), price_per_day, price_per_month,
  status (available/rented/maintenance), documents (JSON), created_at

rental_contracts
  id, company_id, asset_id, customer_id, reference,
  type (short_term/long_term), start_date, end_date,
  amount, status, deposit_amount, deposit_returned_at,
  departure_condition, return_condition, notes, created_at

rental_payments
  id, company_id, contract_id, customer_id,
  amount, due_date, paid_date, method, status, receipt_url

-- Module Restaurant
menu_items
  id, company_id, name, description, category_id,
  price, image_url, available_morning, available_noon, available_evening,
  is_available, position, created_at

restaurant_sessions
  id, company_id, type (morning/noon/evening), opened_by,
  opened_at, closed_at, opening_float, closing_amount, notes

orders
  id, company_id, session_id, customer_name, type (table/takeaway/delivery),
  table_number, delivery_address, status, total,
  payment_method, payment_status, user_id, created_at

order_items
  id, order_id, company_id, menu_item_id, quantity,
  unit_price, discount, notes

-- Fidélité
loyalty_transactions
  id, company_id, customer_id, sale_id,
  points_earned, points_spent, balance_after, created_at
```

---

# ANNEXES

## A. Checklist de lancement MVP

- [ ] Domaine `khayma.com` ou `khayma.africa` acheté
- [ ] VPS OVH configuré + Nginx + SSL wildcard
- [ ] Base de données PostgreSQL + Redis installés
- [ ] Application Laravel déployée
- [ ] Compte PayDunya créé et testé
- [ ] Compte Mailgun configuré
- [ ] Compte Twilio configuré
- [ ] Compte WhatsApp Business créé
- [ ] Compte Cloudflare R2 créé
- [ ] Site vitrine en ligne
- [ ] Backoffice Super Admin fonctionnel
- [ ] Premier module (Restaurant) testé
- [ ] CGU et politique de confidentialité rédigées
- [ ] Mentions légales complètes

## B. Contacts & Services

| Service | URL | Usage |
|---|---|---|
| PayDunya | paydunya.com | Paiements abonnements |
| Mailgun | mailgun.com | Emails transactionnels |
| Twilio | twilio.com | SMS et OTP |
| OVH | ovh.com | Hébergement VPS |
| Cloudflare | cloudflare.com | CDN + R2 storage |
| Crisp | crisp.chat | Chat support |

## C. Glossaire

| Terme | Définition |
|---|---|
| Multi-tenant | Architecture où plusieurs clients partagent la même application |
| company_id | Identifiant unique d'une entreprise cliente, présent dans toutes les tables |
| RLS | Row-Level Security — sécurité au niveau des lignes PostgreSQL |
| PMP | Prix Moyen Pondéré — méthode de valorisation du stock |
| ESC/POS | Protocole standard pour imprimantes thermiques de tickets |
| MRR | Monthly Recurring Revenue — revenus récurrents mensuels |
| Churn | Taux de résiliation / perte de clients |
| XOF | Franc CFA Ouest-Africain |

---

*Document généré le 24 Mars 2026*  
*Khayma — Là où votre business prend vie*  
*Version 1.0 — Validé par quiz interactif (40 questions)*
