<?php namespace Agc7\Vhri;

/**
 * Fichier pour Dev
 *
 * Certaines lignes pdoivent être supprimées/modifiées pour mise en ligne, en prod
 *
 * À noter que sont mises des class dans les tag /div... Ce qui n'est pas orthodoxe...
 * Bien-sûr, à ne pas faire dans un fichier final qui plus est en production ! ;-)
 * W3C vous en voudrait...
 */

class Groupe
{
    /**
     * Mode de vue appliqué à tous les membres
     *
     * 0 ou '' ou null Pas de ligne indicant le mode de vue (ou toute valeur autres que celles i-dessous: Tout pour dev
     * (Autre que 0 affiche aussi détail de la vue en entête de doc)
     * 1 Que le nom, l'idAff en grand (Mode idéal pour la construction du groupe)
     * 2 idAff + nom et pf agrandie
     * 3 Que l'Idpo et le nom Mode Normal
     *
     */
    const MODE = 3; // 777 (Quasi Tout + ligne Mode de vue)

    // mec = Membre En Cours
    public $mec;

    // Tableau des bornes droites des noeuds pour fermer aux bons endroits dans le code le bloc des 3 div créées pour les parents
    public $bds = [];

    // L'attribut qui stockera l'instance unique de cette classe singleton
    public static $_instance;

    // Pour affichage selon MODE choisi
    public $mode; // Détail des infos voulues
    public $infos; // la chaînes HTML correspondante;

    // Éléments HTML
    public $elts = [];

    /**
     * Méthode statique qui permet d'instancier ou de récupérer l'instance unique
     *
     * @param $fondateur
     *
     * @return Membre
     */
    protected static function getInstance($fondateur)
    {
        if (null === self::$_instance) {
//      echo '<p class="lead">Je créé un groupe initial avec ' . $fondateur . ' comme fondateur.</p>';
            self::$_instance = new Membre($fondateur);
        } else {
            echo '<p class="erreur">NB: La création du fondateur ' . $fondateur . ' n\'est pas considérée car un précédent fondateur a déjà été nommé (' . self::$_instance->nom . ')...</p>';
        }

        return self::$_instance;
    }

    public function __construct($fondateur)
    {

        // vd(self::$_instance);
        $this->membres[] = static::getInstance($fondateur);

        $this->mec = $this->membres[0];

        // vd($this);

        $this->initFondateur();

        $this->detailsModeVue();

        if (self::MODE) {
            echo '<div class="modeVue">Mode de vue: <span>' . $this->mode . '</span></div>
			';
        }

        self::elts();

        //vd($this);

    }

    // NB: parrId = id du parrain
    // Rappel: MODE 1 conseillé
    public function add($nom, $parrId)
    {
//    echo '<div class="lead">Ajout de ' . $nom . ' sous le parrain  ' . $this->membres{$parrId}->nom . '</div>';
        if (array_key_exists($parrId, $this->membres)) {

            $parr = $this->membres[$parrId];
            $parr->t = 'p';
//    echo 'Parrain: ' . $parr->nom . ' (' . $parr->bg . ', ' . $parr->bd . ')<br>';
            $ref = $parr->bd;

            foreach ($this->membres as $m) {

//      echo 'Étude de ' . $m->nom . ' (' . $m->bg . ', ' . $m->bd . ')<br>';

                /* 1) Ajouter 2 aux BD qui sont à droite ( = >= à celle de bd qui reçoit) */
                if ($m->bd >= $ref) {
                    $m->bd += 2;
                }

                /* 2) Ajouter 2 aux BG qui sont à droite ( = > à celle de bd qui reçoit) */
                if ($m->bg >= $ref) {
                    $m->bg += 2;
                }
            }

            /* 3) Insertion du membre avec bg =bd de ref (Élément de référence, là, qui reçoit) */
            $this->membres[] = new Membre(ucfirst(strtolower($nom)), $ref, $ref + 1, $parr->nom, $parr->pf + 1, 'c');
        } else {
            echo '<p class="erreur ajoutNoId">Attention: Impossible d\'ajouter ' . $nom . ': Pas de parrain avec l\'idAff ' . $parrId . ' !</p>';
        }

    }

    /**
     * Nombre de membres du groupe
     *
     * @param  bool $txt True Affiche membre(s)
     * @return int  Nombre de membres
     */
    public function nbr($txt = null)
    {
        $n = count($this->membres);
        $txt = ($txt) ? ' membre' . (($n - 1) ? 's' : '') . '.' : '';
        return $n . $txt;
    }

    public function getMembre($idAff)
    {
//    echo '<h1>'.$idAff.'</h1>';

        if ($idAff + 1 > self::nbr()) {
            return 'Pas de membre avec cet idAff';
        } else {
            $m = $this->membres[$idAff];

//      vd($this->membres);
            //      echo $m->nom.' : '.$m->getParrRi($this->membres);

            return
            $idAff . ': ' . $m->nom . ' (' . $m->bg . ', ' . $m->bd . ' | Parr: ' . $m->parr . '-' . $m->getParrRi($this->membres) . ' | Prof: ' . $m->pf . ' | Type: ' . $m->t .
                ')';

        }
    }

    public function affListeMembres()
    {
        echo '
    <p class="lead">Liste des membres (Nom(BG, BD | Nom | Profondeur | Type)) :</p>

<article class="listeMembres">
';
        $gr = $this;
        foreach ($gr->membres as $k => $m) {
            echo '
    <p>' . $this->getMembre($k) . '</p>';
        }
        echo '

</article>
';
    }

    /**
     * Affiche vue hiérarchique
     *
     * @return codeHTML
     */
    public function affVueHierarchique()
    {
        if ($this->nbr() - 1) {

            // Les membres avant tris
            // vdColl( $this->membres, '$m->membres dans l\'ordre d\'arrivée' );

            foreach ($this->membres as $i => $m) {
                $m->id = $i; // idAff d'origine
                usort($this->membres, __NAMESPACE__ . '\Groupe::compareBgMembres');
            }

            // Les membres sont dans l'ordre du ver dans $this->Smembres
            //            vdColl( $this->membres, '$m->membres après tri selon leur borne gauche (Déplacement du ver)' );

            // Début de la vue
            $html = $this->globalHtml();

            // Fondateur
            $fondateur = $this->membres[0];
            $fondateur->idAff = 0;
            $this->mec = $fondateur;

            $this->detailsVue();

            $html .= $this->elts['p']
            . $this->carte(self::MODE)
            . $this->elts['fp'];

            array_shift($this->membres); // Fondateur pas géré dans la boucle

            foreach ($this->membres as $i => $m) {
                ++$i;
                $m->idAff = $i; // idAff selon l'ordre du déplacement du ver

                if ($m->t === 'p') {

                    $m->coulItem = 'redLi';
                    array_unshift($this->bds, $m->bd); // Retient la BD du noeud à fermer
                    $elt['childNode'] = '<div class="hv-item-child">
                    ';

                } else {

                    $m->coulItem = 'blueLi';
                    $elt['childNode'] = $elt['fChildNode'] = '';

                }

                // bds de Noeuds à fermer
                $m->vt = (isset($this->bds[0])) ? serialize($this->bds) : [];

                // mec = Membre En Cours
                $this->mec = $m;

//                vd( $m );
                //vd( $this );

                // html + carte
                $html .= $elt['childNode'] . $this->elts[$m->t] . $this->carte(self::MODE) . $this->elts['f' . $m->t];
                //. $elt[ 'fChild' ];

                // Si un Node reste à être fermé
                if (array_key_exists(0, $this->bds)
                    // Et il y a un membre suivant
                     && array_key_exists($m->idAff, $this->membres)
                    // NB Les idAff sont décalés de 1 donc suivant = idAff et pas idAff+1
                ) {
                    while (array_key_exists(0, $this->bds) &&
                        $this->membres[$m->idAff]->bg > $this->bds[0]) {

                        //vd( $m->nom );
                        //vd( $m->id );
                        //
                        //vd( $this->membres[ $m->idAff ]->nom );
                        //vd( $this->membres[ $m->idAff ]->bg );

                        $html .= '
          </div class="finChildren">
        </div class="finItem">
      </div class="finChild' . $this->bds[0] . '">
';
                        array_shift($this->bds);

                    }
                }

            }

            // Fin du Node Fondateur
            $html .= '                </div class="finChildren">
            </div class="finItem">
';
            // Fin de la vue
            $html .= $this->globalHtml(1);

        } else {
            // Si n'y a que le fondateur dans le Groupe...

            $this->detailsVue();

            $html = '
<p class="lead tac">
<img src="assets/img/desert_ici.jpg" alt=""></p>
Est-ce bien nécessaire de schématiser une structure hiérarchique avec
 un seul membre...? Lol... Enfin... Voilà:</p>
'
            . $this->globalHtml()
            . $this->elts['c'] . $this->carte() . $this->elts['fc']
            . $this->globalHtml(1);
        }

        echo $html;
    }

    public function elts()
    {
        $this->elts['p'] = '<div class="hv-item">
        <div class="hv-item-parent mbr">
            ';

        $this->elts['fp'] = '</div class="finParent">
            <div class="hv-item-children mbr">

                ';

        $this->elts['fN'] = '</div class="finParent">
            <div class="hv-item-children mbr">

            ';

        $this->elts['c'] = '<div class="hv-item-child">
				';

        $this->elts['fc'] = '</div class="finChild">
';
    }

    public function globalHtml($bloc = null)
    {
        return ($bloc)
        ?
        '
		</div class="finWrapper" >
	</div class="finContainer" >
</section >

'
        :
        '
<section class="basic-style">
  <div class="hv-container">
    <div class="hv-wrapper">

    	';
    }

/**
 * Initie les params nécessaires pour afficher le fondateur
 *
 * @return Rien
 */
    public function initFondateur()
    {
        $fondateur = $this->mec;
        $fondateur->id = 0;
        $fondateur->idAff = 0;
        $fondateur->vt = '';
        $fondateur->coulItem = 'redLi';

        $this->mec = $fondateur;
    }

    public function detailsVue()
    {
        // Rappel: mec = Membre En Cours de traitement
        // (Mais ça va bien comme terme! Lol)
        $m = $this->mec;
        // vd( $m );

        switch (self::MODE) :

    case (1):
        // Mode construction (id très visible - idAff en petit dessous)
        // Rappel: Le petit chiffre indique le déplacement du ver,
        // ordre utile pour l'écriture du code Htmml
        $infos = $m->nom . '<span class="grand">' . $m->id . '</span>' . $m->idAff;
        break;

    case (2):
        // Utile pour vérifier conformité grand groupes
        $infos = $m->id . ' ' . $m->nom . '<span class="grand">' . $m->pf . '</span>';
        break;

    case (3):
        // Pour affichage simplissime
        $infos = $m->id . ' ' . $m->nom;
        break;

    default:
        // Affichage complet pour dev
        if (array_key_exists(0, $this->bds)) {
                $m->vt = serialize($this->bds);
        } else {
            // vt = variable pour VueTechnique (Ici, du array(bds) sérialisé)
            $m->vt = '[bds] vide';
        }
        $infos = (isset($m->idAff) ? $m->idAff
            : '00') . '|' . $m->id . ' ' . $m->nom . ' ' . $m->bg . ',' . $m->bd . '-' . $m->pf . '<br>' . $m->vt;

        endswitch;

        $this->infos = $infos;
    }

    /**
     * Permet d'afficher une ligne en haut du doc précisant le mode actuel
     * en fonction du choix fait dans la constante MODE
     *
     * Proriété mode
     *
     * Return Rien
     */

    public function detailsModeVue()
    {
        // On ne mmélange pas ces actions avec detailsVue, pourtant boucle très similaire, parce que cette dernière est itérative (Répétée pour chaque membre) alorqs que cette info est calculée qu'une seule fois)

        $m = $this->mec;
        //vd( $m );
        switch (self::MODE):

    case (1):
        // Pour construire aisément le groupe, idAff en grand (+nom)
        $mode = '1: Nom + id en grand + idAff en tout petit dessous';
        break;

    case (2):
        // Utile pour vérifier conformité grand groupes
        // Rappel: id = idAff d'origine (Ordre d'entrée)
        $mode = '2: id + Nom en petit & profondeur en grand';
        break;

    case (3):
        // Pour affichage simplissime
        $mode = '3: id + Nom uniquement';
        break;

    default:
        // Affichage complet pour dev
        $mode = 'Quasi tout (idAff|id Nom bg,bd-pf $vt)';

        endswitch;

        $this->mode = $mode;
    }

    /**
     * Contenu de la carte du membre
     *
     * @param null $mode
     *
     * @return string
     */
    public function carte($mode = null)
    {
            $m = $this->mec;

            // vd($this);

            $this->detailsVue();

            // Affichage Carte du membre
            $carte = '<p class="cardM ' . $m->coulItem . '">' .
            $this->infos
                . '
						</p>
        ';

            return $carte;
    }

    /**
     * Fonction pour trier les membres de la classe Groupe
     * en fonction de leur borne gauche
     *
     * @param $a Membre A
     * @param $b Membre B
     *
     * @return int
     */
    public static function compareBgMembres($a, $b)
    {
        if ($a->bg === $b->bg) {
            return 0;
        }

        return ($a->bg > $b->bg) ? +1 : -1;
    }

}
