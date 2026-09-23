# [DoliLetter] [23.1.0] - Diffusion publique remaniée - Permis de feu - Droits assainis

Description : Cette version remanie la **page publique de diffusion** : un bloc par risque avec prise de connaissance à cocher avant de signer, le document en haut de page, le nombre d'étapes, un récapitulatif en bas, le logo et les retours vers Dolibarr. L'**inscription libre** y est activée par défaut, et la diffusion accepte désormais le **permis de feu** au même titre que le plan de prévention. Elle corrige surtout la numérotation des droits du module, qui aurait attribué de mauvaises permissions à la prochaine réactivation, et une centaine d'avertissements PHP 8 relevés par le premier passage du smoke test.

**Cette version demande Saturne 23.2.0 ou supérieur.**

## Nouvelles fonctionnalités et innovations

### Page publique de diffusion

* **Un bloc par risque**, avec une prise de connaissance à cocher avant de pouvoir signer.
* Le document remonte en haut de page, le **nombre d'étapes** apparaît sur les blocs de risques, et un récapitulatif ferme la page.
* Logo, retours vers Dolibarr et **liste des signataires** sur la diffusion.
* Détail du plan de prévention, vue de signature individuelle et photos de certification.
* Lien de signature individuel dans la liste des signataires.
* Inscription libre et renoncement aux documents obligatoires ; l'inscription libre est **active par défaut**.

### Diffusion

* Le **permis de feu** se diffuse comme le plan de prévention.
* Les types d'objets sur lesquels une diffusion peut être créée se choisissent en configuration.

## Améliorations & corrections

### Droits du module

* **Les identifiants des droits sont figés.** Ils étaient déduits du rang dans la liste du descripteur, et deux droits avaient été ajoutés en tête. Or une désactivation efface toutes les définitions de droits du module et la réactivation les recrée, sans toucher aux droits déjà attribués aux utilisateurs : à la prochaine mise à jour, un utilisateur qui pouvait lire les enveloppes se serait retrouvé avec une autre permission. La numérotation historique est préservée et les deux nouveaux droits prennent des identifiants libres.

### Génération de documents

* **Dolibarr 24 : la génération de documents est réparée** — le cœur y refuse les modèles livrés avec le module et ne transmet plus ses paramètres au générateur.
* La page de configuration des documents ne part plus en erreur fatale : la déclaration du modèle PDF de feuille de signature était devenue incompatible avec le socle.
* Le nom du modèle de document Saturne est corrigé.

### Fiche enveloppe et listes

* Une centaine d'avertissements PHP 8 supprimés : libellé court de statut jamais rempli, listes de valeurs et filtre `customsql` lus sans vérification, variables de formulaire non initialisées, et une référence de permission erronée.

### Interface

* L'écran de succès passe au-dessus de l'aperçu PDF, et le formulaire d'inscription publique au-dessus de la liste des risques — les cases cochées n'étaient plus perdues à l'envoi.
* Titre et ordre des colonnes de l'écran de succès, bouton discret et message de traduction vide réparés.
* Accents UTF-8 corrompus dans les messages du plan de prévention.

### Intégration continue

* Les assets compilés sont vérifiés à chaque poussée.

## Comparaison des versions [23.0.0](https://github.com/Eoxia/doliletter/compare/23.0.0...23.1.0) et 23.1.0
