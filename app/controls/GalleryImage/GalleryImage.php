<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of GalleryImage
 *
 * @author Buri <buri.buster@gmail.com>
 */
class GalleryImage extends Nette\Application\UI\Control {
    //put your code here

    /**
     *
     * @var id identifier of image
     */
    protected $id;

    public function setDependencies($id){
        $this->id = $id;
        return $this;
    }

    public function renderSmall(){
        $t = $this->template;
        $t->setFile('small.latte');
        $t-render();
    }
}
