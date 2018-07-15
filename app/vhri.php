<?php namespace Agc7\vhri;

include 'class/Groupe.php'; // Gestion du Groupe
include 'class/Membre.php'; // Membre d'un groupe
include 'functions/gc7.php'; // Petites fonctions

$gr = new Groupe('Vous');

// Tentative de REnommer un fondateur rejettée par le fait que la classe Groupe est un singleton
// $gr = new Groupe('Vous2');

if (!isset($gr)) {
    die('<p class="modeVue grand">Pas encore de groupe initié...</p>');
}

// 0 = id  du membre parrain
// (En grand en mode 1, mode construction !)

$gr->add('Pier', 0);
$gr->add('Pol', 0);
$gr->add('Jack', 0);
$gr->add('Polo', 3);
$gr->add('Pierot', 3);


// Tentative d'ajouter un membre en indiquant un id de parrain inexistant
// $gr->add('Juda', 999);

// Tests: Création aléatoire de $nbre membres

$nombre = 2; //5

for ($i = 1; $i < $nombre + 1; $i++) {
    $fakePseudo = 'Untel_' . $i;
    $gr->add($fakePseudo, array_rand($gr->membres));
}

// Affiche la liste des membres
// $gr->affListeMembres();
// vd($gr);

// Pour le membre d'index 2, affichage de son nom, de celui du parrain enregistré et du parrain calculé selon Représentation Intervallaire
// $m = $gr->membres[2]; echo $m->nom . ' < ' . $m->parr. ' - '. $m->getParrRi( $gr );
//vd($m);

//Affiche le nom d'un membre au hazard
// echo $gr->membres[array_rand($gr->membres)]->nom;

echo $gr->nbr(1);

// Affiche une vue hierarchique du Groupe
$gr->affVueHierarchique();

echo '<br>';
