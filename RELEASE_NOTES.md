# [DoliLetter] [23.3.2] - Page publique de diffusion plus sûre - Feuille d'émargement plus juste

Description : la page publique de diffusion contrôle désormais qui agit sur quelle personne. La feuille d'émargement dit la vérité quand un document prévu manquait au moment de la signature, et l'écran de fin de signature s'affiche correctement sur téléphone. **Mise à jour recommandée pour toutes les instances.**

**Cette version demande Saturne 23.9.1 ou supérieur.**

## Améliorations & corrections

### Page publique de diffusion : contrôles renforcés

* Chaque action sur une personne de la diffusion (signer, répondre, déposer un document, recevoir son lien) vérifie maintenant deux choses :
  * la personne fait bien partie de la diffusion affichée ;
  * le visiteur est le titulaire du lien de signature, ou un gestionnaire connecté.
* Une signature déjà enregistrée ne peut plus être remplacée.
* Une personne qui a signé ne peut plus être retirée ni modifiée.
* Les photos et documents déposés vont uniquement dans les dossiers des documents demandés de la diffusion.
* La note publique de la diffusion ne se modifie plus que par un gestionnaire, et elle est affichée échappée.
* Une adresse e-mail qui a déjà signé ne peut plus se réinscrire pour récupérer un nouveau lien.
* Les usages prévus ne changent pas : signature par lien, inscription publique, et gestion des participants par un utilisateur connecté.

### Preuve de signature et feuille d'émargement

* Un document mis en avant qui manquait sur le disque au moment de la signature n'est plus enregistré comme « version signée ».
* La feuille d'émargement et le détail des signataires indiquent alors « Document prévu absent au moment de la signature », avec son nom.
* Les signatures enregistrées avant cette version s'affichent comme avant.

### Documents absents du disque

* La page publique de diffusion ne propose plus un document mis en avant ou un fichier joint absent du disque. Avant, la carte affichée menait à un message d'erreur qui montrait le chemin interne du fichier, et un avertissement PHP s'imprimait dans la page.

### Affichage sur téléphone

* L'écran « signé » de la page publique de diffusion ne déborde plus de l'écran d'un téléphone (390 px). La coche passe au-dessus du titre sur un petit écran.

### Paquet du module

* Un fichier de travail versionné par erreur (`temp_block.txt`) est retiré du module.

## Testez les nouveautés maintenant

**Allez tester la diffusion d'un document sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.3.1](https://github.com/Eoxia/doliletter/compare/23.3.1...23.3.2) et 23.3.2

* [Spread] fix: contrôle du signataire sur la page publique [`5e5396d2`](https://github.com/Eoxia/doliletter/commit/5e5396d2)
* [#726] [Spread] fix: document prévu absent au moment de la signature, affiché sur la feuille d'émargement et dans le détail des signataires [`bffacf46`](https://github.com/Eoxia/doliletter/commit/bffacf46)
* [#724] [Spread] fix: document mis en avant absent du disque, plus inscrit comme version signée [`7736639e`](https://github.com/Eoxia/doliletter/commit/7736639e)
* [#722] [Spread] fix: type des lignes ECM resserré avant le contrôle du disque (PHPStan) [`a695d73c`](https://github.com/Eoxia/doliletter/commit/a695d73c)
* [#722] [Spread] fix: fichiers joints absents du disque retirés de la page publique, comme les documents mis en avant [`4f2190f4`](https://github.com/Eoxia/doliletter/commit/4f2190f4)
* [#719] [Mod] chore: retrait de temp_block.txt, versionné par erreur [`9e5d05e1`](https://github.com/Eoxia/doliletter/commit/9e5d05e1)
* [#718] [Spread] fix: document mis en avant absent du disque, ni avertissement ni accès cassé [`4bd4b7f9`](https://github.com/Eoxia/doliletter/commit/4bd4b7f9)
* [#716] [Spread] fix: écran de succès signé lisible sur téléphone [`1984e750`](https://github.com/Eoxia/doliletter/commit/1984e750)
