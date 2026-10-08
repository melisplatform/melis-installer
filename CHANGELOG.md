## v6.0.4 - 2026-10-06
### Changed
* Hide melis-design from the legacy setup module selection

## v6.0.3 - 2026-08-12
### Added
* **setup-react:** complete the React wizard API + install idempotence
### Fixed
* **setup-react:** adopt the installed site module, correct the password messages

## v6.0.2 - 2026-08-10
### Dependencies & build
* **composer:** update docs/homepage links, swap zf2 keyword for laminas, bump php constraint to ^8.3|^8.5

## v6.0.0 - 2026-08-10
### Security
* **security:** add SECURITY.md (private vulnerability reporting policy)
### Added
* **melis-installer:** add MelisAI two-part documentation (functional guide + technical reference)
* Add explicit nullable param types (PHP 8.4 deprecation)
### Fixed
* **security:** harden legacy file/dir creation & output escaping
* Fix PDO::MYSQL_ATTR_INIT_COMMAND deprecation on PHP 8.5
### Changed
* Pre-fill DB connection form from MYSQL_* environment variables
* Clarify recommended PHP label (8.1 -> "8.1 or higher")
* Improve code formatting and consistency in InstallerController
### Dependencies & build
* **deps:** require melisplatform/melis-core ^6.0
* Local WIP snapshot before reconcile (20260806-114605)
* **sync:** align melis-react branch with parent deliverable
### Docs
* **MelisAI:** regenerate MelisInstaller doc with the wizard screenshots

## v5.3.2 - 2025-02-12
### Added
* Added platform svg logo
### Fixed
* Fixed problem not showing option when installing demo sites
### Changed
* Edits on setup type and modules
* Edits for login page melis-box image
* Update on css and logo
* Updated back office melis-log.svg and favicon
* Edit on hide modal
### Dependencies & build
* Rebundle js

## v5.3.1 - 2024-10-21
### Security
* Added etc folder for bundle rights checking

## v5.3.0 - 2024-10-08
### Added
* Additional edit on css for tooltip and tab
* Added console.log on getRequest fail
* Added console logs
* Added logs
### Fixed
* Fix dropdown language
* Fixing issue on tooltip css
* Restore InstallerController.php
### Changed
* Remove border radius on step configuration tabs
* Css and tooltip placement changes
* Edit on dropdown language
* Edit on setup and change language issue
* Remove conflict css
* Check issue on tab
* Check issue on tooltip and tab
* Debug on tooltip and tab issues
* Debug issue on bootstrap tooltip and data-bs-toggle=tab
* Edit - added back console log on getRequest() fail function
* Remove console logs
* Edit setup.js
* Edit on InstallerController.php
* Edit on setup.js
* Edit on setup.js errorCallBack()
* Edit setup.js response.success
* Checking issue
* Edit setup.js and setup.css
* Edits setup.js and InstallerController.php
* Edits on setup.js
* Edit download.phtml
* Edits for jquery migration
* Updated files related to jquery migration
* Data/ directory added to check
### Dependencies & build
* Rebundle assets
* Rebundle js and css

## v5.2.2 - 2024-09-04
### Changed
* Translations service
* Last step reload target url

## v5.2.1 - 2024-07-25
### Changed
* Update setting env datas

## v5.2.0 - 2024-06-06
### Security
* Added bundles-generated to rights check
### Changed
* Set melis-core required version to 5.2
* Update folder name checking

## v5.1.1 - 2024-02-13
### Changed
* Removed temporary branch set when developing

## v5.1.0 - 2024-02-13
### Fixed
* Handles warnings
### Changed
* Update installer to get third party framework with specific branch
### Dependencies & build
* Update melis core version to 5.1
* Update translations and temporarily set update/php83 as default branch
* Update php version

## v5.0.2 - 2023-05-24
### Changed
* Update utf8 to utf8mb4
* Changed collation to utfmb4_general_ci

## v5.0.1 - 2022-06-23
### Fixed
* Fix encountered bug wherein Melis Demo CMS is included in melis.module.load file

## v5.0.0 - 2022-06-22
### Added
* Added trim functions to db parameters
* Added php8 as one of the required php versions
### Changed
* Removed Silex from the platform framework options
* Removed trim function
* Parse to object the extra variable
* Changed array_key_exists to property_exists and removed get_object_vars
* Changed ArraySerializable to ArraySerializableHydrator
* Set 7.3 as minimum php version
### Dependencies & build
* Update melis package version to 5.0

<!-- Historical entries preserved below -->

All notable changes to this project will be documented in this file.  

[Released]
* Bugs Fixes

## Un-release
* Step 3.1 - Removed scroll bar on Modules selection
