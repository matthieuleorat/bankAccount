[![codecov](https://codecov.io/gh/matthieuleorat/bankAccount/branch/master/graph/badge.svg?token=8WLNNUR8X2)](https://codecov.io/gh/matthieuleorat/bankAccount)
# Mon budget
Mon budget est un projet personnel, qui devrait me servir à suivre un peu mes dépenses, à savoir où part mon argent.  
C'est un projet en cours de développement, donc certaines fonctionnalités peuvent présenter quelques disfonctionnement.  
Pour l'instant, l'application est capable de lire un relevé de compte bancaire de la Société Générale.

## Présentation
### Source
L'entité source peut représenter un compte bancaire ou un porte monnaie physique, et peut être demain un compte bitcoin ou n'importe qu'elle autre source d'argent.

### Statement
Une entité représentant un relevé bancaire. Pour l'instant, seul les relevés bancaire d'un compte perso à la société générale sont importables.  
Sont récupérées les métadonnées. Date de début et de fin, balance de début et de fin et total des débits.  
Actuellement, on ne concerve pas sur le serveur le fichier pdf updload, ni sa version texte générée. Mais cela peut être une prochaine feature.

### Transaction
Une entité représentant un ligne d'un relevé de compte.

### Expense
Une entité représentant une dépense, catégorisée. Une transaction peut générer plusieurs dépenses. Par exemple, sur une transaction de course à Leclerc, celles ci peuvent contenir du terreau pour le jardin, un grille pain, qui n'iront pas dans le budget Alimentation.

### Category
Les différentes catégries dans lesquelles classer les dépenses. Pour l'instant les catégories sont communes à tous les utilisateurs. Cela devra être modifier pour que chaque utilisateur puisse avoir ses propres catégories.

### DetailToCategory et Filter
Le système de filtres pour créer automatiquement des dépenses à partir des relevés bancaire importés.