# TP TDD Texas Hold'em Poker

Petit projet de TP pour pratiquer le **Test Driven Development** en PHP autour de la comparaison de mains de poker (Texas Hold’em).

## Objectif

- Pour chaque joueur : on prend **7 cartes** (2 hole cards + 5 cartes du board) et on calcule la **meilleure main de 5 cartes**.
- Le programme annonce ensuite le **gagnant** ou un **split pot** (égalité).
- Aucune carte dupliquée (entre joueurs et board).

## Format des cartes

Une carte est au format **`<Couleur><Valeur>`** :

- Couleurs :
  - `S` = Spades (pique)
  - `H` = Hearts (coeur)
  - `D` = Diamonds (carreau)
  - `C` = Clubs (trèfle)
- Valeurs : `2-9`, `T` (10), `J`, `Q`, `K`, `A`

Dans les arguments CLI, plusieurs cartes sont séparées par `:`.

Exemples :
- `SK` = Roi de pique
- `HQ` = Dame de coeur
- `SK:HQ` = Roi de pique + Dame de coeur

## Aperçu rapide du code

- `src/Domain`
  - `Card`, `Rank`, `Suit`, `Deck` : objets de base (représentation + génération).
  - `Card::fullName()` fournit un affichage humain (ex: `As de pique`).

- `src/Poker/Hand`
  - `HandEvaluator` : calcule la meilleure main de 5 cartes parmi 7.
  - `HandComparer` : compare deux mains évaluées (tie-breakers).
  - `HandCategory` : enum des catégories (HighCard, OnePair, ..., StraightFlush).

- `src/Application`
  - `PokerGame` : service applicatif qui orchestre l’évaluation de plusieurs joueurs + board et retourne les winners.
  - `PokerGameResult` : contient `results` (tous les joueurs) + `winners`.
  - `BoardProvider` : prend un board fourni ou en génère un aléatoire (sans doublons).
  - `GameInputValidator` : refuse toute duplication de cartes.

- `bin/poker`
  - Entrée CLI (parse les arguments, valide, lance `PokerGame`, affiche le résultat).

## Utilisation CLI

### Aide

```cmd
php bin/poker --help
```

### Lancer une partie à 2 joueurs (board aléatoire)

```cmd
php bin/poker run --p1 SK:HQ --p2 DA:C3
```

Sortie (exemple, le board est aléatoire donc ça change) :

```text
Board: Neuf de pique | Sept de carreau | Huit de carreau | Neuf de coeur | Deux de pique
p1 hole: Roi de pique | Dame de coeur | best: Neuf de pique | Neuf de coeur | Roi de pique | Dame de coeur | Huit de carreau | category: OnePair
p2 hole: As de carreau | Trois de trèfle | best: Neuf de pique | Neuf de coeur | As de carreau | Huit de carreau | Sept de carreau | category: OnePair
Winner: p2
```

### Lancer une partie avec un board imposé

```cmd
php bin/poker run --board S2:H3:D4:C5:S6 --p1 SK:HQ --p2 DA:C3
```

Sortie attendue (proche, l’ordre exact des cartes best peut varier selon l’implémentation, mais l’idée est la même) :

```text
Board: Deux de pique | Trois de coeur | Quatre de carreau | Cinq de trèfle | Six de pique
p1 hole: Roi de pique | Dame de coeur | best: Six de pique | Cinq de trèfle | Quatre de carreau | Trois de coeur | Deux de pique | category: Straight
p2 hole: As de carreau | Trois de trèfle | best: Six de pique | Cinq de trèfle | Quatre de carreau | Trois de coeur | Deux de pique | category: Straight
Split pot: p1,p2
```

## Tests

Lancer toute la suite :

```cmd
composer test
```

## Notes / limites actuelles

- La CLI actuelle ne  supporte que `--p1` et `--p2`. On pourrait modifier cela pour avoir un nombre non fixe de joueurs.
