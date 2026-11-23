# [DoliLetter] [23.3.1] - La bonne lettre partagée et envoyée, même avec des annexes

Description : quand une enveloppe a des annexes dans ses fichiers joints, le lien de partage public, la page du destinataire après sa signature et l'envoi par e-mail ou par courrier utilisent toujours la dernière lettre générée, et plus une annexe.

**Cette version demande Saturne 23.9.1 ou supérieur.**

## Améliorations & corrections

### Partage public : le lien va à la dernière lettre générée

* Le document partagé par une enveloppe était le dernier fichier **par ordre alphabétique**, pas le dernier généré :
  * une annexe téléversée dans l'onglet Fichiers joints (par exemple `Annexe_contrat.pdf`) recevait le lien de partage à la place de la lettre, et la page publique affichée au destinataire après sa signature lui proposait l'annexe ;
  * à partir de la onzième génération d'un même jour, c'est l'avant-dernier document qui était repartagé (`_10` se range avant `_2`).
* Toutes les actions qui partagent un document (génération depuis la fiche, accusé de réception, preuve de dépôt, numéro de suivi, envoi par e-mail ou par courrier) et la page publique de signature partagent désormais la **dernière lettre générée** de l'enveloppe, sans spécimen ni fichier téléversé.
* Quand plusieurs scans sont joints d'un coup à l'accusé de réception ou à la preuve de dépôt, le deuxième n'est plus rangé dans le dossier des documents générés.

### Envoi par e-mail et par courrier : la lettre est jointe, pas l'annexe

* Le formulaire d'envoi par e-mail joignait le fichier le plus récent du dossier de l'enveloppe : **une annexe téléversée après la lettre partait à sa place**.
* Avec une annexe et aucune lettre générée, ni l'envoi par e-mail ni l'envoi par courrier ne générait la lettre : l'e-mail partait avec l'annexe seule.
* Les deux formulaires joignent désormais la dernière lettre générée de l'enveloppe, et la génèrent si elle n'existe pas encore. Une annexe reste ajoutable à la main dans le formulaire.
* Après l'envoi d'un e-mail, retour à la fiche de l'enveloppe sans avertissement PHP « Undefined variable $paramname ».

### Activation

* À l'activation du module, les documents de DoliLetter restés dans un dossier « (PROVn) » rejoignent celui de leur référence définitive (avec Saturne 23.9.1).

### Pages publiques et onglets

* La page publique de signature ne s'arrête plus sur une erreur fatale quand son lien porte un type d'objet inconnu ou vise un signataire introuvable : elle répond comme pour un lien inconnu.
* Les onglets Contacts et Notes d'une enveloppe vérifient le bon droit pour retirer un lien, et ne laissent plus d'avertissements PHP à chaque affichage.

## Testez les nouveautés maintenant

**Allez tester l'envoi d'une enveloppe avec une annexe sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.3.0](https://github.com/Eoxia/doliletter/compare/23.3.0...23.3.1) et 23.3.1

* [#711] [Envelope] fix: droit de retrait de lien des onglets Contacts et Notes [`2d73a3a4`](https://github.com/Eoxia/doliletter/commit/2d73a3a4)
* [#709] [Signature] fix: erreurs PHPStan révélées par le type désormais connu de la page [`24f01035`](https://github.com/Eoxia/doliletter/commit/24f01035)
* [#709] [Signature] fix: page publique de signature sans fatale pour un type inconnu ou un signataire introuvable [`c1cae7f0`](https://github.com/Eoxia/doliletter/commit/c1cae7f0)
* [#702] [Mod] fix: fichiers des objets validés restés sous (PROVn) repris à l'activation depuis Accueil > Modules [`9e76e9d1`](https://github.com/Eoxia/doliletter/commit/9e76e9d1)
* [#705] [Envelope] fix: plus de « Undefined variable $paramname » à chaque envoi par mail d'une enveloppe [`f0facf16`](https://github.com/Eoxia/doliletter/commit/f0facf16)
* [#704] [Envelope] fix: le formulaire d'envoi par mail joint la lettre, plus le fichier le plus récent du dossier [`e3af4fe8`](https://github.com/Eoxia/doliletter/commit/e3af4fe8)
* [#701] [Envelope] fix: le lien de partage public va au dernier document généré, plus au dernier fichier par ordre du nom [`020f9503`](https://github.com/Eoxia/doliletter/commit/020f9503)
