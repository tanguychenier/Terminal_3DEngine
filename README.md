#3D CUBE PhP Terminal Engine - Tchenier
#AFPA 2016-2017

## About this

The projet is a personnal research about PhP in Unix Terminal.
It print a 3D Cube using Vertex, Rasterize and Edge Engine made in PhP Only.

## Current build status

0.9:
- Color   Class OK: Used to print a gradient color around the Cube: https://fr.wikipedia.org/wiki/Codage_informatique_des_couleurs
- Vector  Class OK: Used to print a vector to x,y,z coordinates, in
correlation with the Color class: 
https://fr.wikipedia.org/wiki/Vecteur_(structure_de_donn%C3%A9es)
- Vertex  Class OK: Used to print a specify point used to Camera
static orientation in correlation with the Vector + Color class: 
https://fr.wiktionary.org/wiki/vertex
- Matrix  Class OK: Used to print the 4 cartesian points in correlation
with the Vertex + Vector + Color class: 
https://fr.wikipedia.org/wiki/Matrice_(math%C3%A9matiques)
- Camera  Class OK: Used to print about a define point with define angle in
correlation with Matrix + Vertex + Vector + Color class: 
https://fr.wikipedia.org/wiki/Clipping_(infographie)
- Engine  Class WORK IN PROGRESS: Used to print in 3D, the model with Vertex, Rasterize & Edge
physical Value in correlation with Camera + Matrix + Vertex + Vector + Color class: 
https://fr.wikipedia.org/wiki/Moteur_3D
- Triangle Class WORK IN PROGRESS: Used to print a Triangle in correlation with Matrix +
Vertex + Vector + Color class.
### Quick start

How to use:

```
Run in you terminal ressources/php main[x].php
x = 01, 02, ...

each of these will explain the process about the homogeneous coordinates
implement in a 3D variation static system.
```
## From

- AFPA-Center: `Session 2016-2017 - Duboeuf Team`
