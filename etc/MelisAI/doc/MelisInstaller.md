---
title: MelisInstaller module
package: melisplatform/melis-installer
doc_type: module-documentation
audience: [users, developers, ai]
language: en
module_version: unversioned
last_reviewed: 2026-06-08
maintainer: Melis Technology
keywords: [installer, install, setup, wizard, requirements, database, modules, demo, melis, core, foundation]
screenshots_dir: ./images
---

# MelisInstaller — Functional & Technical Documentation (for AI)

> **What this is.** MelisInstaller is the platform's **first-run installer** — the setup wizard
> that gets a fresh Melis Platform up and running: it checks the server, verifies file
> permissions and the database connection, installs the chosen modules, and lets you start from an
> **empty install**, a **starter site module**, or the **MelisCmsDemo** learning website. It is
> part of the platform foundation (§0).
>
> **Two parts:** **[Part A — Functional Guide](#part-a--functional-guide)** ·
> **[Part B — Technical Reference](#part-b--technical-reference)** (developers/AI, with examples).
> Consumed by the **MelisAI** MCP. Reviewed 2026-06-08.

---

## 0. The MelisCore platform foundation (this family of modules)

> These modules are the **foundation of the Melis platform** — collectively referred to as
> **"MelisCore"**. *MelisCore* proper is the back-office heart everything depends on; the other
> four are the infrastructure that installs, deploys, serves and migrates the platform.

- **MelisCore** — the **back-office foundation** (login, users/rights, tools framework, dashboard,
  config, events, base services). **Every module depends on it.**
  → [MelisCore doc](../../../melis-core/etc/MelisAI/doc/MelisCore.md)
- **MelisAssetManager** — serves module assets & bundles them; module discovery.
  → [MelisAssetManager doc](../../../melis-asset-manager/etc/MelisAI/doc/MelisAssetManager.md)
- **MelisDbDeploy** — applies database migrations.
  → [MelisDbDeploy doc](../../../melis-dbdeploy/etc/MelisAI/doc/MelisDbDeploy.md)
- **MelisComposerDeploy** — runs Composer from inside the platform to install/update/remove modules.
  → [MelisComposerDeploy doc](../../../melis-composerdeploy/etc/MelisAI/doc/MelisComposerDeploy.md)
- **MelisInstaller** *(this module)* — the **first-run installer** wizard.

**Dependency note:** MelisInstaller requires **MelisCore** and **MelisEngine**, and orchestrates
the other foundation tools during setup — it uses **MelisComposerDeploy** to install module
packages and **MelisDbDeploy** to apply their database migrations, then **MelisAssetManager**
serves their assets.

---
---

# PART A — Functional Guide

## A1. What the installer does

MelisInstaller is the **one-time setup wizard** you run when first installing the platform (from
the Melis skeleton). It walks you through:

1. **System check** — verifies your PHP version and required **extensions** are present, and that
   the needed folders are **writable**.
2. **Database** — checks the **MySQL connection** and prepares the schema.
3. **Modules** — installs the **modules** you select.
4. **Starting point** — choose how to begin:
   - **Empty install** — a clean platform ready to build on.
   - **A starter site module** — scaffolds a site module for an easy start.
   - **MelisCmsDemo** — installs the demo website, great for learning.
5. **Admin account** — creates the first back-office administrator.

After it finishes, you log in to the back-office (MelisCore) and start building.

## A2. When you use it

Only at **initial setup** (or when re-provisioning an environment). Day-to-day, you don't touch
the installer — you manage modules from the back-office Modules tool instead.

> **Screenshots** (recommended, to add under `./images/` later): the requirements/system-check
> screen, the database step, the module-selection step, and the install-type choice
> (empty / site module / CmsDemo). None are captured yet.

---
---

# PART B — Technical Reference

## B1. Metadata & dependencies

| Item | Value |
|---|---|
| Package | `melisplatform/melis-installer` · category `core` · namespace `MelisInstaller\` |
| Requires | `melisplatform/melis-core`, `melisplatform/melis-engine` (`^5.2`) |

## B2. Services (with examples)

**`InstallHelperService`** — the environment checks and DB bootstrap:

```php
$helper = $sm->get(\MelisInstaller\Service\InstallHelperService::class);
$helper->setRequiredExtensions(['intl','json','openssl','pdo_mysql']);
$ok    = $helper->isExtensionsExists();                 // all required ext present?
$conn  = $helper->checkMysqlConnection($host,$user,$pwd,$db);
$helper->setDbAdapter($adapter);
$exists= $helper->isDbTableExists('melis_core_user');   // already installed?
$helper->executeRawQuery($sql);                          // run setup SQL
```

Methods: `setRequiredExtensions`/`getRequiredExtensions`, `getPhpExtensions`,
`isExtensionsExists`, `isDomainExists`/`getDomain`, `checkMysqlConnection`, `setDbAdapter`,
`executeRawQuery`, `isDbTableExists`.

**`MelisInstallerModulesService`** — which modules to install (mirrors asset-manager's discovery):
`getAllModules`, `getVendorModules`, `getModulesAndVersions`, `getUserModules`, `getSitesModules`,
`getCoreModules`, `getModulePath`, `getComposerModulePath`, `getComposer`/`setComposer`.

**`MelisInstallerConfigService`** / **`MelisInstallerTranslationService`** — config merge & the
installer's own translations (the wizard runs before MelisCore's services are fully available, so
the installer carries config/translation helpers of its own: `getItem`, `getMelisKeys`,
`getFormMergedAndOrdered`, `getTranslationMessages`, `addTranslationFiles`, `getDateFormat`…).

## B3. Flow & orchestration

The wizard (controllers in `src/Controller`, forms in `src/Form`, validators in `src/Validator`)
drives: system checks (`InstallHelperService`) → DB setup → module install via
**MelisComposerDeploy** (`MelisComposerService::download`) → **MelisDbDeploy** applies the deltas →
optional demo/site setup → create the admin user (fires `meliscore_install_create_new_user`).
Two listeners (`src/Listener`) hook the install process.

## B4. Quick code map

```
melis-installer/
├── composer.json                 → deps (core + engine), category core
├── config/                       → installer routes, interface, forms
├── src/   Controller/ · Form/ · Validator/ · Listener/
│        · Service/ (InstallHelper, Config, Modules, Translation, AbstractService)
├── view/ · public/ · language/
└── etc/   MarketPlace + MelisAI/doc (this doc)
```

---

*Document for AI consumption (MelisAI MCP) — `melisplatform/melis-installer`. Part A = functional;
Part B = technical with examples. Part of the MelisCore platform foundation. Last reviewed 2026-06-08.*
