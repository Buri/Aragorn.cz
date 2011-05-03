<?php

class frontend_banPresenter extends BasePresenter {
    public function renderDefault() {
        if(empty($_SESSION["ban"])){
            $this->view = "noban";
        }else{
            $this->getTemplate()->data = unserialize($_SESSION["ban"]);
            unset($_SESSION["ban"]);
        }
    }

}
