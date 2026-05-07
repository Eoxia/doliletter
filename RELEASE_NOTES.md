# [DoliLetter] [23.0.0] - Spread enrichi - Interface publique étendue - PDF de signature

Description : Cette version transforme DoliLetter en outil complet de gestion de courriers et signatures collectives : nouvelle vue Spread (liste avec colonnes personnalisées), interface publique enrichie (fichiers liés, notes publiques, signatures, mp4), tableau de bord, génération PDF configurable et signature rapide. Saut de version 1.0.1 → 23.0.0 pour aligner sur la famille Saturne / Dolibarr 23.

## Nouvelles fonctionnalités et innovations

### Spread — vue liste enrichie

* Nouvelle **liste Spread** complète avec onglet de gauche dédié.
* Colonne personnalisée `NumberOfPersons` ajoutée à la liste.
* Compteur sur l'onglet Spread (action DoliLetter).
* Liste des utilisateurs affichée directement sur la fiche Spread.
* Élément ajouté à la liste Spread.

<!-- 📸 Ajouter une screenshot ici -->

### Interface publique étendue

* **Liste de fichiers liés** affichée sur la vue publique avec informations d'objet.
* Bouton « Voir signatures » sur l'interface publique.
* Note publique éditable et message de succès via modale.
* Lien signataire intégré à la modale de signature.
* Affichage de fichiers MP4 dans la vue publique.
* Lien de connexion (« LoginLink ») ajouté sur la page publique.
* Permission de mise à jour des utilisateurs depuis l'interface publique.
* Email envoyé aux utilisateurs Spread depuis la vue publique.
* Refonte IHM de l'interface publique.

<!-- 📸 Ajouter une screenshot ici -->

### Génération PDF & feuille de signature

* Configuration admin pour la génération PDF (#384).
* Génération PDF intégrée à `modDoliLetter`.
* `DocSigninSheet` : date de signature ajoutée.
* `TemplateSigninsheet` : note publique ajoutée et ID d'objet retiré.
* Configuration et droit d'affichage de la signature.
* Configuration email (avec date picker) sur `setup.php`.

<!-- 📸 Ajouter une screenshot ici -->

### Tableau de bord

* Nouveau **dashboard** dédié à DoliLetter.
* Labels ajoutés sur les widgets du dashboard.

<!-- 📸 Ajouter une screenshot ici -->

### Spread — signature rapide

* Option « signature rapide » (`quick sign spread`) sur le Spread.
* Favori sur fichier et lien, affichage en haut de la liste.
* Tabbar du PDF retirée pour une lecture épurée.
* Agenda lié au Spread.

---

## Améliorations & corrections

### Interface publique

* Utilisateurs désactivés filtrés dans le sélecteur d'utilisateurs.
* JS de signature corrigé.
* Message affiché aux utilisateurs non connectés.
* Note publique masquée aux utilisateurs non connectés.
* Rendu PDF correct même non connecté.
* Mise à jour de la note publique fonctionnelle (bug du double save corrigé).
* Boutons grisés à la sauvegarde — corrigés.

### Spread / AddSpread

* Suppression d'utilisateur impossible et erreurs HTML — corrigés.
* Type incorrect des fichiers liés à la tâche corrigé.
* Migration vers `$objectMetadata` (pattern Saturne).
* Code mort retiré.
* Libellé d'objet corrigé.
* Ref provisoire (`ref prov`) corrigée.

### Module / configuration

* Constante d'auto-activation ajoutée (#447).
* `Envelope` : tous les projets dans le sélecteur de projet.
* Documents intégrés à `modDoliLetter`.
* Traductions ajoutées (#374).
* Plusieurs passes de nettoyage de code (`[Class] core: clean code`).

### Dashboard / Spread (corrections)

* `AttendanceSheetClass` : colonne du dashboard corrigée.
* `PublicSpreadView` : filepath corrigé (#417).

## Comparaison des versions [1.0.1](https://github.com/Eoxia/doliletter/compare/1.0.1...23.0.0) et 23.0.0

* [#454] [Dashboard] add: label to dashboard [`0baad56`](https://github.com/Eoxia/doliletter/commit/0baad56)
* [#451] [PublicSpreadView] fix: remove disabled user from select user [`66c5be8`](https://github.com/Eoxia/doliletter/commit/66c5be8)
* [#449] [LoginLink] add: login link to public page [`03d03a7`](https://github.com/Eoxia/doliletter/commit/03d03a7)
* [#447] [Mod] add: const auto active [`c5aee9a`](https://github.com/Eoxia/doliletter/commit/c5aee9a)
* [#444] [PublicSpreadView] fix: mp4 not display [`dc854ce`](https://github.com/Eoxia/doliletter/commit/dc854ce)
* [#442] [PublicSpreadView] add: signatory link with only modal [`0eb719b`](https://github.com/Eoxia/doliletter/commit/0eb719b)
* [#441] [Spread/PublicSpreadView] add: quick sign + JS fix [`b7b11ac`](https://github.com/Eoxia/doliletter/commit/b7b11ac) [`3d28194`](https://github.com/Eoxia/doliletter/commit/3d28194)
* [#439] [AddSpread] fix: cant delete user and html error [`441b130`](https://github.com/Eoxia/doliletter/commit/441b130)
* [#436] [SpreadView] fix: pdf not render when not logged [`888a801`](https://github.com/Eoxia/doliletter/commit/888a801)
* [#433] [SpreadView] add: message when not logged [`d4873ab`](https://github.com/Eoxia/doliletter/commit/d4873ab)
* [#432] [SpreadView] remove: public note if not logged [`5fe4c04`](https://github.com/Eoxia/doliletter/commit/5fe4c04)
* [#430] [Config/Rights] add: show signature config and right [`5648e43`](https://github.com/Eoxia/doliletter/commit/5648e43)
* [#424] [SpreadList] add: element on spread list [`8245d2d`](https://github.com/Eoxia/doliletter/commit/8245d2d)
* [#423] [Setup.php] add: email config + picker [`98ff8c2`](https://github.com/Eoxia/doliletter/commit/98ff8c2) [`0df1a9f`](https://github.com/Eoxia/doliletter/commit/0df1a9f)
* [#419] [DocSigninSheet] add: signature date [`41011de`](https://github.com/Eoxia/doliletter/commit/41011de)
* [#417] [PublicSpread] add: favorite for file/link, remove tabbar, fix label/filepath [`4eca72a`](https://github.com/Eoxia/doliletter/commit/4eca72a) [`a0d25d2`](https://github.com/Eoxia/doliletter/commit/a0d25d2) [`a88cbed`](https://github.com/Eoxia/doliletter/commit/a88cbed) [`937c3b1`](https://github.com/Eoxia/doliletter/commit/937c3b1)
* [#415] [ActionsDoliletter] add: counter on spread tab [`0c4b8cb`](https://github.com/Eoxia/doliletter/commit/0c4b8cb)
* [#412] [PublicSpreadView] fix: can't update public note twice [`1cad9df`](https://github.com/Eoxia/doliletter/commit/1cad9df)
* [#410] [AddSpread] fix: useless code, $objectMetadata, task linked files type [`bd3d489`](https://github.com/Eoxia/doliletter/commit/bd3d489) [`995a355`](https://github.com/Eoxia/doliletter/commit/995a355) [`02503f7`](https://github.com/Eoxia/doliletter/commit/02503f7)
* [#409] [PublicInterface] add: permission to update users [`d6d15e7`](https://github.com/Eoxia/doliletter/commit/d6d15e7)
* [#404] [PublicInterface] add: button to see signature, new ihm [`0e57dec`](https://github.com/Eoxia/doliletter/commit/0e57dec) [`3ccbccb`](https://github.com/Eoxia/doliletter/commit/3ccbccb)
* [#399] [PublicSpreadView] remove: button grey [`83fc0b9`](https://github.com/Eoxia/doliletter/commit/83fc0b9) [`10779dd`](https://github.com/Eoxia/doliletter/commit/10779dd)
* [#397] [TemplateSigninsheet] add: public note and remove id of object [`0895120`](https://github.com/Eoxia/doliletter/commit/0895120)
* [#389] [Spread] fix: change place of public interface [`c561e1b`](https://github.com/Eoxia/doliletter/commit/c561e1b)
* [#388] [Spread] add: users list on card [`a567157`](https://github.com/Eoxia/doliletter/commit/a567157)
* [#387] [PublicView] add: send email to spread users [`49019ee`](https://github.com/Eoxia/doliletter/commit/49019ee)
* [#386] [PublicView] add: linked file list and object information [`889df78`](https://github.com/Eoxia/doliletter/commit/889df78)
* [#385] [Agenda] add: agenda to spread [`9d02da6`](https://github.com/Eoxia/doliletter/commit/9d02da6)
* [#384] [AdminConfig/ModDoliLetter] add: pdf generation config [`9819d68`](https://github.com/Eoxia/doliletter/commit/9819d68) [`ecb696e`](https://github.com/Eoxia/doliletter/commit/ecb696e)
* [#383] [AddSpread] fix: ref prov [`d28f9ff`](https://github.com/Eoxia/doliletter/commit/d28f9ff)
* [#379] [SpreadList] add: spread list with custom column NumberOfPersons [`d07541f`](https://github.com/Eoxia/doliletter/commit/d07541f) [`f035308`](https://github.com/Eoxia/doliletter/commit/f035308) [`6ef5eed`](https://github.com/Eoxia/doliletter/commit/6ef5eed)
* [#378] [Dashboard] add: dashboard for doliletter [`d706e80`](https://github.com/Eoxia/doliletter/commit/d706e80)
* [#377] [PublicInterface] fix: ihm of public interface [`763d9e0`](https://github.com/Eoxia/doliletter/commit/763d9e0)
* [#374] [ModDoliletter/Lang] add: documents inside, langs [`37cb890`](https://github.com/Eoxia/doliletter/commit/37cb890) [`7fc70d9`](https://github.com/Eoxia/doliletter/commit/7fc70d9)
* [Envelope] add: all projects in project selector [`816bb4d`](https://github.com/Eoxia/doliletter/commit/816bb4d)
* [Class] core: clean code [`29b69f9`](https://github.com/Eoxia/doliletter/commit/29b69f9)
