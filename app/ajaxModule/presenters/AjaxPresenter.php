<?php

namespace ajaxModule{
    use Nette\Environment;
    use \usock;
    use \Node;
    
    class ajaxPresenter extends \BasePresenter {
        public function __construct(){
            header("Content-type: application/xml");
        }
        
        public function startup(){
            $this->setView('default');
            parent::startup();
        }

        public function actionDefault(){
            $this->getTemplate()->data = "<version>1.0</version>";
        }

        public function actionTestIdentity(){
            /* Sync with node.js */
            
            $this->getTemplate()->data = Node::userlogin();
        }
    }
}
