# [DoliLetter] [23.2.0] - Feuille d'émargement PDF - Documents de la diffusion - Parcours de signature guidé

Description : Chaque signature d'une diffusion produit désormais une **feuille d'émargement PDF** qui garde la preuve de ce qui a été lu et coché. Un nouvel onglet **Documents de la diffusion** règle en un clic ce que voient les signataires, et la page publique **guide chaque personne étape par étape** jusqu'à sa signature. DoliLetter passe aussi au design moderne des modules Evarisk.

**Cette version demande Saturne 23.8.0 ou supérieur.**

## Nouvelles fonctionnalités et innovations

### Un parcours de signature guidé

* En haut de la page publique de diffusion, un **fil d'Ariane** montre les trois étapes : *Qui va signer*, *Lecture du document*, *Validation + signature*. Chaque étape dit où elle en est et mène à sa partie de la page.
* La barre reste épinglée en haut de l'écran et suit la progression sans recharger la page.
* La signature se place tout en bas, après les risques à cocher et les documents demandés.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.2.0/etapes-signature.png" alt="Page publique d'une diffusion : les étapes Qui va signer et Lecture du document validées, l'étape Validation + signature en cours">

### Feuille d'émargement PDF

* Une **feuille d'émargement** est générée à chaque signature : nom, présence, signature et date de chaque personne. Elle se télécharge depuis la liste des signataires de la page publique et se range dans les fichiers joints de l'onglet Diffusion.
* La feuille garde la **preuve de ce qui a été signé** : la version du document lu au moment de la signature, les risques pris en connaissance (x sur y, avec leurs noms) et la réponse à chaque document demandé. Quand le document change entre deux signatures, ses versions sont numérotées.
* Le pied de page figure sur chaque page de la feuille.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.2.0/signataires-public.png" alt="Signataires de la diffusion : 2 signatures sur 3 et le bouton Feuille d'émargement (PDF)">

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.2.0/feuille-emargement.png" alt="Feuille d'émargement PDF : deux personnes signées, une en attente">

### Onglet « Documents de la diffusion »

* Nouvel onglet à côté de *Diffusion* : pour chaque document de l'objet, documents générés compris, un choix **Masqué**, **En téléchargement** ou **À lire**. « À lire » l'affiche en entier sur la page de diffusion, avant la signature.
* **Ajouter un document** se fait en un clic. L'onglet remplace l'étoile de l'onglet Fichiers joints, qui ne marchait qu'une fois le fichier partagé.
* Les boutons **Ouvrir la page de diffusion** et **Copier le lien** sont dans le bandeau des deux onglets.
* Un document à lire que le navigateur ne sait pas afficher devient une carte de téléchargement.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.2.0/documents-diffusion.png" alt="Onglet Documents de la diffusion : pour chaque document, les choix Masqué, En téléchargement et À lire">

### Liste des signataires

* L'**e-mail** et le **téléphone** de chaque personne s'affichent sous son nom, selon deux réglages séparés de Digirisk.
* Sous chaque personne, un panneau **Documents et réponses** montre les risques pris en connaissance et chaque élément demandé (CACES, permis, habilitation…) avec son état (*Fourni*, *Non concerné*, *Non fourni*) et ses documents. Les photos s'affichent en vignette. Ce panneau est réservé aux utilisateurs qui ont le droit de lire le plan de prévention ou le permis de feu.
* Sur la diffusion d'un plan de prévention ou d'un permis de feu, un justificatif **PDF** se dépose pour une certification, plus seulement une photo.
* Le nom des pictogrammes du récapitulatif s'affiche au survol.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.2.0/diffusion-signataires.png" alt="Onglet Diffusion : trois signataires avec leur e-mail et leur téléphone, leur état, leur présence et leur lien individuel">

### Design moderne et administration

* Les écrans de DoliLetter adoptent le **thème moderne** des modules Evarisk.
* Le modèle d'e-mail de la diffusion apparaît dans la **vue d'ensemble des modèles d'e-mail** de Saturne.
* DoliLetter refuse de s'activer sur un Saturne absent ou trop ancien, au lieu d'une erreur fatale, et active ses dépendances avant de s'installer.

## Améliorations & corrections

### Page publique de diffusion

* La page ne charge plus que les fichiers partagés de l'objet diffusé, et non ceux de toute l'instance : elle pouvait finir en erreur de mémoire.
* Prénom, nom, e-mail et téléphone sont obligatoires par défaut pour une personne extérieure, et le téléphone est mis au bon format. Une inscription sans aucun champ est refusée.
* La signature n'est créée qu'une fois la personne inscrite : plus de signataire « Inconnu » dans la liste. Les réponses « pas concerné » sont conservées.
* Les risques et le récapitulatif restent masqués tant que l'étape *Qui va signer* n'est pas franchie.
* La page de succès s'affiche sans erreur fatale, et une signature déjà faite y renvoie directement.
* Un fichier trop gros affiche un message lisible.
* Les protections d'un risque de catégorie 0 ne sont plus affichées en double, l'intitulé de l'en-tête est centré et le bouton « Je suis concerné » reste lisible au survol.

### Réglages

* Les onglets Diffusion et Documents de la diffusion apparaissent enfin sur les commandes et propositions fournisseur, les interventions, les nomenclatures et les listes d'appels de ReedCRM.
* Sur une facture, une commande, un tiers ou un autre objet de Dolibarr, la page Diffusion retrouve son bandeau, la page publique de signature et l'onglet Documents de la diffusion s'ouvrent, et la liste des diffusions ne s'arrête plus sur une erreur fatale.
* La diffusion n'est proposée que sur les objets qui ont une fiche.
* Le libellé du modèle d'e-mail de diffusion est traduit, et le choix vide le remet à zéro.
* La feuille d'émargement PDF est proposée et présélectionnée dans l'onglet Diffusion.

### Enveloppes

* Plus d'avertissement PHP sur la fiche, la signature, l'agenda et les documents d'une enveloppe. L'infobulle retrouve son pictogramme, et une page d'envoi orpheline est retirée.

### Sécurité et droits

* Les liens et appels AJAX portent le jeton anti-CSRF, et les paramètres des requêtes de la page de diffusion sont échappés.
* Les droits de lecture sont accordés aux utilisateurs qui ont déjà un droit DoliLetter.

### Compatibilité

* DoliLetter fonctionne dans une racine alternative à `custom/` : liens, onglets, chemins sur le disque et inclusion de Saturne.

## Testez les nouveautés maintenant

**Allez tester la feuille d'émargement et le parcours de signature sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.1.1](https://github.com/Eoxia/doliletter/compare/23.1.1...23.2.0) et 23.2.0

* [#620] [Spread] fix: find the spread object under the link name it stores [`ae4bcf54`](https://github.com/Eoxia/doliletter/commit/ae4bcf54)
* [#618] [Spread] fix: drop the second include of the spread lib in the descriptor [`1ba37434`](https://github.com/Eoxia/doliletter/commit/1ba37434)
* [#618] [Spread] fix: declare the spread tabs under the tab type of each card [`c9874379`](https://github.com/Eoxia/doliletter/commit/c9874379)
* [#614] [Spread] add: photos affichées, téléphone et email réglés séparément dans la liste des signataires [`d02c4ddc`](https://github.com/Eoxia/doliletter/commit/d02c4ddc)
* [#615] [Spread] fix: only offer the spread on objects that have a card [`2088a06a`](https://github.com/Eoxia/doliletter/commit/2088a06a)
* [#612] [Spread] fix: translate spread email template label in setup [`d3cc5a06`](https://github.com/Eoxia/doliletter/commit/d3cc5a06)
* [#610] [Spread] fix: escape fromtype in the spread page query [`8a8d07ef`](https://github.com/Eoxia/doliletter/commit/8a8d07ef)
* [#608] [Spread] feat: carte de téléchargement pour un document à lire non affichable [`0a69e54f`](https://github.com/Eoxia/doliletter/commit/0a69e54f)
* [#606] [Spread] fix: message lisible quand le fichier envoyé est trop gros [`12110d4b`](https://github.com/Eoxia/doliletter/commit/12110d4b)
* [#603] [Envelope] fix: info() teste le résultat de fetch_object(), baseline PHPStan nettoyé [`f6ff46fe`](https://github.com/Eoxia/doliletter/commit/f6ff46fe)
* [#603] [Envelope] fix: warnings PHP de l'agenda et des documents, page d'envoi orpheline retirée [`630b8e46`](https://github.com/Eoxia/doliletter/commit/630b8e46)
* [#599] [Signature] fix: page de succès de signature sans fatale [`6cdffafd`](https://github.com/Eoxia/doliletter/commit/6cdffafd)
* [#599] [Envelope] fix: pictogramme doliletter16px de l'infobulle des enveloppes [`e299531c`](https://github.com/Eoxia/doliletter/commit/e299531c)
* [#599] [Signature] fix: redirection d'une signature déjà faite vers la page de succès [`68b1bc76`](https://github.com/Eoxia/doliletter/commit/68b1bc76)
* [#599] [Envelope] fix: warnings PHP de la fiche et de la signature d'enveloppe [`4f3164c9`](https://github.com/Eoxia/doliletter/commit/4f3164c9)
* [#598] [Diffusion] fix: page publique limitée aux fichiers partagés de l'objet [`6c3a4daa`](https://github.com/Eoxia/doliletter/commit/6c3a4daa)
* [#596] [Module] fix: URL relatives et réécrites vers custom/ [`c137dcfd`](https://github.com/Eoxia/doliletter/commit/c137dcfd)
* [#596] [Module] fix: onglets du descripteur sans custom/ [`85de022a`](https://github.com/Eoxia/doliletter/commit/85de022a)
* [#596] [Module] fix: chemins disque construits avec DOL_DOCUMENT_ROOT . '/custom/…' [`3c4d68e0`](https://github.com/Eoxia/doliletter/commit/3c4d68e0)
* [#596] [Module] fix: dol_buildpath('/custom/…') sans custom/ [`a6afe574`](https://github.com/Eoxia/doliletter/commit/a6afe574)
* [#596] [Module] fix: URL construites avec DOL_URL_ROOT . '/custom/…' [`258e4b7d`](https://github.com/Eoxia/doliletter/commit/258e4b7d)
* [#594] [CI] ci: actions GitHub en Node 24 (checkout@v7, cache@v6) [`d74eb9a5`](https://github.com/Eoxia/doliletter/commit/d74eb9a5)
* [#592] [CI] ci: contrôle « API Saturne publiée » dans la CI qualité [`1ca7fcb4`](https://github.com/Eoxia/doliletter/commit/1ca7fcb4)
* [#590] [Mod] feat: version minimale de Saturne, activation refusee sur un Saturne trop ancien [`fffdc87a`](https://github.com/Eoxia/doliletter/commit/fffdc87a)
* [#588] [Admin] fix: read the spread email template with GETPOSTINT, the empty choice resets it [`6fe17db1`](https://github.com/Eoxia/doliletter/commit/6fe17db1)
* [#586] [Email] feat: declare the DoliLetter email template setting to the Saturne overview [`f6931a8b`](https://github.com/Eoxia/doliletter/commit/f6931a8b)
* [#584] [CI] ci: refuser les inclusions relatives de Saturne [`40500d81`](https://github.com/Eoxia/doliletter/commit/40500d81)
* [#582] [Spread] rework: porte-voix comme picto de la diffusion [`8f0720d3`](https://github.com/Eoxia/doliletter/commit/8f0720d3)
* [#578] [Module] fix: refuse the activation instead of a fatal error when Saturne is missing [`601d0f31`](https://github.com/Eoxia/doliletter/commit/601d0f31)
* [#579] [Spread] feat: onglet « Documents de la diffusion » et boutons de la page publique dans le bandeau [`0cf73cba`](https://github.com/Eoxia/doliletter/commit/0cf73cba)
* [#579] [Spread] fix: icône des fichiers à télécharger déclarée dans la lib, la page publique ne plante plus [`5e658156`](https://github.com/Eoxia/doliletter/commit/5e658156)
* [#576] [Module] fix: depend on Saturne and activate dependencies before init [`ab54994e`](https://github.com/Eoxia/doliletter/commit/ab54994e)
* [#574] [Header] feat: thème partagé sur les pages des courriers [`41f4090d`](https://github.com/Eoxia/doliletter/commit/41f4090d)
* [#574] [SCSS] feat: thème moderne des écrans DoliLetter [`574db494`](https://github.com/Eoxia/doliletter/commit/574db494)
* [#574] [Front] rework: styles inline déplacés en SCSS [`ab997319`](https://github.com/Eoxia/doliletter/commit/ab997319)
* [#572] [Spread] fix: risques et récapitulatif masqués tant que l'étape « Qui va signer » n'est pas franchie [`9dca0cc2`](https://github.com/Eoxia/doliletter/commit/9dca0cc2)
* [#570] [Spread] feat: feuille d'émargement PDF générée à chaque signature et téléchargeable depuis la liste des signataires [`15452d11`](https://github.com/Eoxia/doliletter/commit/15452d11)
* [#568] [Spread] feat: fil d'Ariane des étapes de signature en haut de la diffusion [`efc3dcb8`](https://github.com/Eoxia/doliletter/commit/efc3dcb8)
* [#566] [Droits] fix: accorder lire et read aux utilisateurs qui ont deja un droit DoliLetter [`40a137d1`](https://github.com/Eoxia/doliletter/commit/40a137d1)
* Evarisk/Digirisk#5382 [Spread] fix: feuille d'émargement PDF proposée et présélectionnée dans l'onglet Diffusion [`dfea5cf1`](https://github.com/Eoxia/doliletter/commit/dfea5cf1)
* Evarisk/Digirisk#5382 [Spread] feat: version du document signé et preuve des coches sur la feuille d'émargement [`6755011b`](https://github.com/Eoxia/doliletter/commit/6755011b)
* [#564] [CSRF] fix: ajouter le token CSRF aux liens et appels AJAX [`f06222b6`](https://github.com/Eoxia/doliletter/commit/f06222b6)
* [#562] [Saturne] fix: inclure Saturne par dol_include_once / dol_buildpath [`7f4939aa`](https://github.com/Eoxia/doliletter/commit/7f4939aa)
* Evarisk/Digirisk#5309 [Spread] feat: documents et réponses de chaque personne dans la liste des signataires [`efe378cf`](https://github.com/Eoxia/doliletter/commit/efe378cf)
* Evarisk/Digirisk#5309 [Spread] fix: prénom, nom, email et téléphone obligatoires par défaut pour un signataire externe [`46ec3e80`](https://github.com/Eoxia/doliletter/commit/46ec3e80)
* Evarisk/Digirisk#5309 [Spread] fix: format du téléphone à l'inscription, connecté ou non [`1a6c31c6`](https://github.com/Eoxia/doliletter/commit/1a6c31c6)
* Evarisk/Digirisk#5309 [Spread] fix: signature au-dessus de la liste des signataires [`000c81fd`](https://github.com/Eoxia/doliletter/commit/000c81fd)
* Evarisk/Digirisk#5309 [Spread] fix: signature tout en bas de la page personnelle, après les risques à cocher [`c8d7928a`](https://github.com/Eoxia/doliletter/commit/c8d7928a)
* Evarisk/Digirisk#5309 [Spread] fix: inscription publique refusée sans aucun champ, réponses « pas concerné » conservées [`439ab37b`](https://github.com/Eoxia/doliletter/commit/439ab37b)
* Evarisk/Digirisk#5309 [Spread] fix: signature créée seulement une fois la personne validée, plus de « Inconnu » dans la liste des signataires [`a180c255`](https://github.com/Eoxia/doliletter/commit/a180c255)
* Evarisk/Digirisk#5351 [Spread] fix: protections du risque de catégorie 0 plus affichées en double dans un bloc à part [`2bac5faf`](https://github.com/Eoxia/doliletter/commit/2bac5faf)
* Evarisk/Digirisk#5350 [Spread] fix: intitulé de l'en-tête centré sur la carte [`fe0ee650`](https://github.com/Eoxia/doliletter/commit/fe0ee650)
* Evarisk/Digirisk#5343 [Spread] feat: nom des pictos du récapitulatif au survol [`f8043e0f`](https://github.com/Eoxia/doliletter/commit/f8043e0f)
* Evarisk/Digirisk#5342 [Spread] fix: bouton « Je suis concerné » lisible au survol [`be4f75cb`](https://github.com/Eoxia/doliletter/commit/be4f75cb)
* Evarisk/Digirisk#5341 [Spread] fix: variables définies hors du bloc Digirisk pour PHPStan [`7d440870`](https://github.com/Eoxia/doliletter/commit/7d440870)
* Evarisk/Digirisk#5341 [Spread] feat: déposer un PDF pour une certification [`721720db`](https://github.com/Eoxia/doliletter/commit/721720db)
* [#548] [CI] rework: elaguer les entrees mortes de la baseline [`92a46148`](https://github.com/Eoxia/doliletter/commit/92a46148)
* [#545] [CI] rework: scanner le socle par dossier plutot que l exclure par morceaux [`c5802385`](https://github.com/Eoxia/doliletter/commit/c5802385)
* [#541] [CI] fix: exclure les bouchons phan de saturne de l'analyse [`20fad000`](https://github.com/Eoxia/doliletter/commit/20fad000)
* [#539] [CI] fix: sortir les stubs de test de saturne du perimetre scanne [`96217d1e`](https://github.com/Eoxia/doliletter/commit/96217d1e)
