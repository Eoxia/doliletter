# [DoliLetter] [23.1.1] - Dolibarr 24 - Conformité Dolistore - Chaîne qualité

Description : Cette version corrige le motif pour lequel le contrôle de paquet du **Dolistore** refusait le zip, déclare le module compatible **Dolibarr 24**, et met en place une **chaîne de contrôles qualité** sur les pull requests — analyse statique, lint PHP et parité des fichiers de langue. Dix clés de traduction mortes sont retirées du fichier anglais.

**Cette version demande Saturne 23.2.1 ou supérieur.**

## Améliorations & corrections

### Conformité du paquet Dolistore

* Le contrôle de paquet refuse un module qui inclut les classes d'un module custom par un chemin `/custom` en dur. DoliLetter en comptait **25**, réparties sur onze fichiers : les classes et libs de Saturne et celles de DoliLetter lui-même. Elles passent par `dol_include_once`, qui cherche dans les deux racines de documents — ce qui répare du même coup le module installé à la racine de Dolibarr.

### Compatibilité

* Le module déclare **Dolibarr 23 au minimum et 24 au maximum**.
* `init()` et `remove()` déclarent leur type de retour, conformément à la signature du coeur.

### Traductions

* Dix clés mortes sont retirées du fichier anglais : elles n'avaient pas d'équivalent français, ce qui faisait basculer en anglais l'ensemble des libellés d'une même requête.

### Intégration continue

* Les pull requests passent désormais **PHPStan**, un **lint PHP** et un contrôle de **parité des fichiers de langue** français / anglais.
* La version du trigger est déclarée avec son type : sans cela, PHPStan la refusait et la baseline figeait le numéro, ce qui aurait cassé la chaîne qualité à chaque release.

## Comparaison des versions [23.1.0](https://github.com/Eoxia/doliletter/compare/23.1.0...23.1.1) et 23.1.1

* [#534] [CI] rework: aligner phpstan.neon sur le gabarit commun [`5dd7e32`](https://github.com/Eoxia/doliletter/commit/5dd7e32)
* [#532] [CI] fix: ignorer par motif la version des triggers, figée dans la baseline [`511a89e`](https://github.com/Eoxia/doliletter/commit/511a89e)
* [#530] [Module] fix: inclure classes et libs custom par dol_include_once [`64d88fd`](https://github.com/Eoxia/doliletter/commit/64d88fd)
* [CI] fix: compléter les dossiers du coeur vus par PHPStan [`4cb6c71`](https://github.com/Eoxia/doliletter/commit/4cb6c71)
* [#528] [CI] feat: PHPStan, lint PHP et parité des langues [`6487f0c`](https://github.com/Eoxia/doliletter/commit/6487f0c)
* [#528] [Lang] fix: retirer dix clés mortes de en_US [`e575f17`](https://github.com/Eoxia/doliletter/commit/e575f17)
* [#528] [Module] fix: déclarer le type de retour int sur init() et remove() [`7cd32ac`](https://github.com/Eoxia/doliletter/commit/7cd32ac)
* [#526] [Module] rework: bornes de version Dolibarr 23 minimum, 24 maximum [`09b0d7d`](https://github.com/Eoxia/doliletter/commit/09b0d7d)
