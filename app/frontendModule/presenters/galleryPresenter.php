<?php

namespace frontendModule{
    use Nette\Environment;
    
    class galleryPresenter extends \BasePresenter {
        public function renderDefault() {
          //$juzr= $user->geIdentity()->username ;
          
          $top[1]['nazev']="pokus";
          $top[1]['adresa']="http://chachatelier.fr/programmation/images/mozodojo-mosaic-image.jpg";
          $top[1]['autor']="baba jaga";
          
          $top[2]['nazev']="pokus2";
          $top[2]['adresa']="http://coolcosmos.ipac.caltech.edu/cosmic_classroom/multiwavelength_astronomy/multiwavelength_museum/images/sun_euv19.gif";
          $top[2]['autor']="baba jaga";
          
          $galerie[1]['nazev']="pokus";
          $galerie[1]['adresa']="http://chachatelier.fr/programmation/images/mozodojo-mosaic-image.jpg";
          $galerie[1]['autor']="baba jaga";
          
          $galerie[2]['nazev']="pokus2";
          $galerie[2]['adresa']="http://coolcosmos.ipac.caltech.edu/cosmic_classroom/multiwavelength_astronomy/multiwavelength_museum/images/sun_euv19.gif";
          $galerie[2]['autor']="baba jaga";
          
          $galerie[3]['nazev']="pokus";
          $galerie[3]['adresa']="http://chachatelier.fr/programmation/images/mozodojo-mosaic-image.jpg";
          $galerie[3]['autor']="baba jaga";
          
          $galerie[4]['nazev']="pokus2";
          $galerie[4]['adresa']="http://coolcosmos.ipac.caltech.edu/cosmic_classroom/multiwavelength_astronomy/multiwavelength_museum/images/sun_euv19.gif";
          $galerie[4]['autor']="baba jaga";
          
          //$this->getTemplate()->user = $juzr;
          $this->getTemplate()->top = $top;
          $this->getTemplate()->galerie = $galerie;
        }
    }
}
