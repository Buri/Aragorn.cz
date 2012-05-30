<?php

namespace frontendModule{
    use Nette\Application\UI\Form;
    
    class galleryPresenter extends \BasePresenter {
        public function renderDefault() {
            $this->template->db = $this->context->database;

        }

        public function createComponentUploadForm(){
            $form = new Form;
            $form->addText('title', 'Název díla')
                    ->addRule(Form::FILLED, 'Dílo se musí nějak jmenovat.');
            $form->addTextArea('description', 'Popis díla')
                    ->addRule(Form::FILLED, 'Zadejte popis')
                    ->addRule(Form::MIN_LENGTH, 'Popis musí mít alespoň 30 znaků.', 30);
            $form->addHidden('file')
                    ->addRule(Form::FILLED, 'Nahrajte váš obrázek tlačítkem vpravo.');

            $form->onSuccess[] = callback($this, 'uploadNewImage');

            $form->addSubmit('send', 'Odeslat dílo');
            return $form;
        }

        /**
         *
         * @param Form $form
         */
        public function uploadNewImage(Form $form){
            if($this->user->isLoggedIn()){
                $v = $form->getValues();
                $v['time'] = time();
                $v['author'] = $this->user->getId();
                
                $src = APP_DIR . '/../userspace/u/' . $v['file'];
                if(!file_exists($src)){
                    $this->flashMessage('Při zpracování souboru došlo k chybě.');
                    $this->redirect($this);
                }

                /* Process uploaded image */
                $img =\Nette\Image::fromFile($src, $format);
                if($img->width > $img->height){
                    $img->resize(140, null);
                }else{
                    $img->resize(null, 140);
                }

                $img->save(APP_DIR . '/../userspace/g/thumbs/'.$v['file'], null, $format);
                rename($src, APP_DIR . '/../userspace/g/'.$v['file']);
                $this->context->database->gallery()->insert($v);
                $this->flashMessage('Obrázek byl odeslán ke schválení.');
            }else{
                $this->flashMessage('Nahrávat smí pouze přihlášení uživatelé.');
            }
            $this->redirect('this');
        }
    }
}
