# [DoliLetter] [23.3.0] - Signatures conservées - Accusés de réception fiables - Pages publiques sécurisées

Description : Les documents d'une enveloppe **gardent la signature** quand on les régénère, l'accusé de réception, la preuve de dépôt et le numéro de suivi **citent la bonne lettre**, et les pages publiques de signature ne servent plus que le signataire de leur lien. **Mise à jour recommandée sur toutes les instances : elle corrige une faille des pages publiques de signature.**

**Cette version demande Saturne 23.9.0 ou supérieur.**

## Nouvelles fonctionnalités et innovations

### Signatures conservées sur les documents

* Un courrier, une preuve de dépôt, un numéro de suivi ou un accusé de réception **régénéré après signature garde la signature**. Elle n'est plus remplacée par « Document généré » dès la première génération, ce qui laissait un cadre vide au document suivant.
* L'onglet Signature affiche la signature dans toutes les langues. Une signature déjà remplacée par une ancienne version affiche « Signé » au lieu d'une image cassée.
* Modifier le contenu d'une enveloppe efface toujours ses signatures : elles ne valent plus pour le nouveau texte.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.3.0/signature-conservee.png" alt="Zone de signature d'un courrier régénéré : avant, cadre vide ; maintenant, la signature reste">

### Diffusion : les modules liés suivent les signatures

* Chaque signature d'une diffusion prévient le module de l'objet diffusé. Digirisk s'en sert pour régénérer le PDF du plan de prévention ou du permis de feu avec la liste à jour des personnes qui ont signé.

## Améliorations & corrections

### Sécurité des pages publiques de signature

* La page publique ne signe plus que pour le signataire de son lien, et la page de succès ne propose les documents qu'à ce signataire, une fois sa signature faite.
* Désactiver l'interface publique de signature bloque aussi l'enregistrement d'une signature, plus seulement l'affichage de la page.
* Une ancienne page publique sans usage, joignable sans connexion et en erreur à chaque appel, est supprimée.

### Accusé de réception, preuve de dépôt et numéro de suivi

* Les trois documents citent l'empreinte de la **dernière lettre générée**, et non plus celle du spécimen du brouillon ou d'une annexe ajoutée dans les fichiers joints. S'il n'y a aucune lettre, ils l'écrivent au lieu de laisser la ligne vide.
* Le libellé dit ce qu'est la valeur : « Empreinte MD5 du courrier ».
* L'accusé d'un envoi par e-mail cite le vrai nom de la lettre.
* Plus d'avertissement PHP quand un scan est joint ou quand l'accusé est généré.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/doliletter/refs/heads/assets/release-23.3.0/accuse-empreinte-md5.png" alt="Accusé de réception : Empreinte MD5 du courrier reçu, celle de la dernière lettre générée">

### Envoi par courrier

* L'envoi par courrier ne coupe plus la page (erreur fatale quand DoliSIRH est actif) et enregistre le destinataire de l'enveloppe : il enregistrait jusqu'ici le premier contact de la base.
* Un contact sans adresse reprend celle de son tiers, et un échec d'enregistrement s'affiche au lieu de passer en silence.

### Enveloppes

* La fiche d'une enveloppe verrouillée qui a perdu son expéditeur ou son destinataire s'affiche, au lieu d'une erreur fatale.
* La signature des modèles PDF ne sort plus vide : elle est lue sous ses deux formes, et le dossier temporaire est créé sur une instance neuve.
* Les dates de signature et d'envoi sont enregistrées en heure universelle et affichées dans le fuseau de l'utilisateur.
* Un échec d'envoi par e-mail affiche un message traduit avec sa cause réelle.
* Plus d'avertissement PHP 8 sur le formulaire et l'envoi par e-mail, l'onglet Objets liés, l'onglet Signature sans signataire, les options jamais réglées et la numérotation sans modèle choisi ; plus de déclencheur au code vide aux changements de statut.

### Multi-société

* Les pages publiques et l'accusé de réception utilisent le dossier et l'index de documents de l'entité de l'enveloppe. Les liens de signature portent l'entité, et les liens déjà envoyés sont redirigés vers la bonne.

### Diffusion

* Une seule personne à faire signer à la fois dans la vue gestionnaire.
* L'icône Présence distingue le retard de l'absence.
* La feuille d'émargement PDF est enregistrée par défaut sans réactiver le module, et son titre est centré. Le modèle ODT numérote ses feuilles et prend les signataires extérieurs.
* Sur la page publique, le nom de l'objet diffusé s'affiche pour tout objet et le canevas de signature prend la taille de l'écran.
* Les dates de la diffusion s'affichent dans le fuseau de l'utilisateur.

### Signature et langues

* Quand une signature n'est pas enregistrée, un message traduit et utile remplace « Error », et l'alerte reste lisible sur un téléphone.
* Les textes en dur de la page publique et du back-office passent en clés de langue, et 170 clés sont traduites en anglais.

## Comparaison des versions [23.2.0](https://github.com/Eoxia/doliletter/compare/23.2.0...23.3.0) et 23.3.0

* [#696] [Signature] fix: page publique morte send_ar.php supprimée [`d33273d7`](https://github.com/Eoxia/doliletter/commit/d33273d7)
* [#694] [Envelope] fix: identifiant de l'enveloppe casté pour fetchSignatory() [`e3b12a8d`](https://github.com/Eoxia/doliletter/commit/e3b12a8d)
* [#694] [Envelope] fix: plus d'erreur fatale quand une enveloppe n'a plus d'expéditeur ou de destinataire actif [`e2761aa3`](https://github.com/Eoxia/doliletter/commit/e2761aa3)
* [#691] [Envelope] fix: clé de désinscription lue avec getDolGlobalString(), plus d'avertissement dans une entité sans elle [`991360a6`](https://github.com/Eoxia/doliletter/commit/991360a6)
* [#690] [Signature] fix: liens de l'onglet Signature sans type acceptés, analyse PHPStan sans nouvelle erreur [`3e96e7cd`](https://github.com/Eoxia/doliletter/commit/3e96e7cd)
* [#690] [Signature] fix: message de l'interface publique coupée traduit sur les pages publiques [`dca98489`](https://github.com/Eoxia/doliletter/commit/dca98489)
* [#690] [Signature] fix: pages publiques limitées à l'enveloppe et au signataire du lien [`9ca7509f`](https://github.com/Eoxia/doliletter/commit/9ca7509f)
* [#687] [Signature] fix: pages publiques et accusé de réception dans le dossier et l'index de l'entité de l'enveloppe [`41d71c18`](https://github.com/Eoxia/doliletter/commit/41d71c18)
* [#686] [Envelope] fix: envoi par courrier, adresse du tiers quand le contact destinataire n'en a pas [`80563d65`](https://github.com/Eoxia/doliletter/commit/80563d65)
* [#685] [Envelope] fix: accusé, preuve de dépôt et numéro de suivi annoncent une empreinte MD5, plus un « SHA » [`d36f9226`](https://github.com/Eoxia/doliletter/commit/d36f9226)
* [#683] [Envelope] fix: option « documents sur l'interface publique » lue avec getDolGlobalInt() [`50c31501`](https://github.com/Eoxia/doliletter/commit/50c31501)
* [#679] [Envelope] fix: envoi par courrier enregistré pour un contact sans adresse, échec signalé [`e62e6a21`](https://github.com/Eoxia/doliletter/commit/e62e6a21)
* [#680] [Envelope] fix: plus de « Undefined variable $pagenb » quand un fichier est joint à la preuve de dépôt, à l'accusé ou au numéro de suivi [`f3ee1793`](https://github.com/Eoxia/doliletter/commit/f3ee1793)
* [#679] [Envelope] fix: envoi par courrier sans erreur fatale ni avertissement, destinataire enregistré d'après l'enveloppe [`0effcf01`](https://github.com/Eoxia/doliletter/commit/0effcf01)
* [#677] [Envelope] fix: accusé de réception, preuve de dépôt et numéro de suivi citent la dernière lettre générée [`a670edfb`](https://github.com/Eoxia/doliletter/commit/a670edfb)
* [#675] [Signature] fix: fichier ECM de l'accusé de réception cherché par son seul chemin, comme avant [`35c85866`](https://github.com/Eoxia/doliletter/commit/35c85866)
* [#675] [Signature] fix: page publique de signature sans variables indéfinies ni option lue en direct [`38adda05`](https://github.com/Eoxia/doliletter/commit/38adda05)
* [#673] [Envelope] fix: signature gardée après la génération d'un document, plus d'image cassée dans une autre langue [`8d131319`](https://github.com/Eoxia/doliletter/commit/8d131319)
* [#671] [Signature] fix: paramètre $role sans valeur par défaut avant $fk_object (dépréciation PHP 8) [`40db1a56`](https://github.com/Eoxia/doliletter/commit/40db1a56)
* [#669] [Envelope] fix: accusé de réception sans avertissement, option de suppression des liens publics lue par getDolGlobalInt() [`59f3a639`](https://github.com/Eoxia/doliletter/commit/59f3a639)
* [#667] [Envelope] fix: signature des modèles PDF lue sous ses deux formes, sans avertissement [`11695739`](https://github.com/Eoxia/doliletter/commit/11695739)
* [#665] [Envelope] fix: document partagé sur l'interface publique, $filename initialisé et rien de partagé quand il n'y a que des spécimens [`419fd0df`](https://github.com/Eoxia/doliletter/commit/419fd0df)
* [#665] [Envelope] fix: identifiant d'utilisateur destinataire passé en entier à user_get_property() [`03e04b89`](https://github.com/Eoxia/doliletter/commit/03e04b89)
* [#665] [Envelope] fix: utilisateurs destinataires lus en tableau (GETPOST 'array'), comme PHPStan le type [`15a37e28`](https://github.com/Eoxia/doliletter/commit/15a37e28)
* [#665] [Envelope] fix: avertissements PHP 8 du formulaire et de l'envoi par e-mail d'une enveloppe [`b90a9250`](https://github.com/Eoxia/doliletter/commit/b90a9250)
* [#657] [Envelope] fix: échec d'envoi d'une enveloppe par e-mail, message traduit avec la cause réelle [`0e811344`](https://github.com/Eoxia/doliletter/commit/0e811344)
* [#658] [Signature] fix: alerte sur les pages publiques, sans thème Dolibarr la notification sort de l'écran d'un téléphone [`89cde5e2`](https://github.com/Eoxia/doliletter/commit/89cde5e2)
* [#660] [Spread] fix: feuille d'émargement PDF enregistrée par défaut sans réactivation du module [`b87bf30d`](https://github.com/Eoxia/doliletter/commit/b87bf30d)
* [#659] [Spread] fix: icône Présence de l'onglet Diffusion, retard distingué de l'absence [`590a0800`](https://github.com/Eoxia/doliletter/commit/590a0800)
* [#658] [Signature] fix: message traduit et utile au lieu de alert('Error') quand une signature n'est pas enregistrée [`d5a33530`](https://github.com/Eoxia/doliletter/commit/d5a33530)
* [#655] [Spread] fix: titre de la feuille d'émargement PDF centré sur la page [`5d8b8b92`](https://github.com/Eoxia/doliletter/commit/5d8b8b92)
* [#653] [Spread] add: événement SPREAD_SIGN à chaque signature de la diffusion [`6534b489`](https://github.com/Eoxia/doliletter/commit/6534b489)
* [#651] [Spread] fix: une seule personne à faire signer à la fois dans la vue gestionnaire [`1262b619`](https://github.com/Eoxia/doliletter/commit/1262b619)
* [#641] [Spread] fix: types attendus par TCPDF dans l'en-tête du tableau de la feuille d'émargement [`d6b570b3`](https://github.com/Eoxia/doliletter/commit/d6b570b3)
* [#649] [Langs] fix: 170 clés de doliletter.lang traduites en en_US [`3857d805`](https://github.com/Eoxia/doliletter/commit/3857d805)
* [#647] [Envelope] fix: onglet Objets liés d'une enveloppe sans avertissement PHP, titres en clés de langue [`17e5f49c`](https://github.com/Eoxia/doliletter/commit/17e5f49c)
* [#641] [Spread] fix: textes en dur du back-office en clés de langue (onglet Diffusion, feuille d'émargement PDF et ODT, configuration) [`8a9f302c`](https://github.com/Eoxia/doliletter/commit/8a9f302c)
* [#643] [Envelope] fix: numérotation des enveloppes avec repli quand DOLILETTER_ENVELOPE_ADDON manque [`4c3ce513`](https://github.com/Eoxia/doliletter/commit/4c3ce513)
* [#642] [Envelope] fix: onglet Signature d'une enveloppe sans signataire sans avertissement [`2dc7e8ef`](https://github.com/Eoxia/doliletter/commit/2dc7e8ef)
* [#635] [Envelope] fix: plus de déclencheur au code vide aux changements de statut d'une enveloppe [`f6d6445a`](https://github.com/Eoxia/doliletter/commit/f6d6445a)
* [#633] [Spread] fix: textes de la page publique en clés de langue (fr_FR, en_US) [`35b03820`](https://github.com/Eoxia/doliletter/commit/35b03820)
* [#634] [Envelope] fix: dates de signature et d'envoi enregistrées en GMT, affichées dans le fuseau de l'utilisateur [`db753fd8`](https://github.com/Eoxia/doliletter/commit/db753fd8)
* [#632] [Spread] fix: signataires externes et dossier temporaire du modèle ODT de feuille d'émargement [`24363133`](https://github.com/Eoxia/doliletter/commit/24363133)
* [#631] [Spread] fix: canevas de signature à la taille de l'écran sur la page publique de tout objet [`afb1ba16`](https://github.com/Eoxia/doliletter/commit/afb1ba16)
* [#629] [Spread] fix: numérotation du modèle ODT de feuille d'émargement [`38c94da2`](https://github.com/Eoxia/doliletter/commit/38c94da2)
* [#626] [Spread] fix: dates de la diffusion dans le fuseau de l'utilisateur [`f5427873`](https://github.com/Eoxia/doliletter/commit/f5427873)
* [#625] [Spread] fix: nom de l'objet diffusé sur la page publique pour tout objet [`f091443d`](https://github.com/Eoxia/doliletter/commit/f091443d)
