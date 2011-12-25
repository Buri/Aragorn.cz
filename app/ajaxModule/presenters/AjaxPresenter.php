<?php

namespace ajaxModule{
    use Nette\Environment;
    use \usock;
    use \Node;
    
    class ajaxPresenter extends \BasePresenter {
        
        public function startup(){
            header("Content-type: application/xml");
            #header("Content-type: text/plain");
            $this->setView('default');
            parent::startup();
        }

        public function actionDefault(){
            $this->getTemplate()->data = "<version>1.0</version>";
        }
        
        public function actionLoginui(){
            $this->setView('loginui');
        }
        public function actionTestIdentity(){
            /* Sync with node.js */
            $this->getTemplate()->data = Node::userlogin();
        }
    }
}
