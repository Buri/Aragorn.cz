<?php

class ajax_AjaxPresenter extends BasePresenter {
    public function __construct(){
        header("Content-type: application/xml");
    }

    public function actionDefault(){
        $this->getTemplate()->data = "<version>1.0</version>";
    }

    public function actionTestIdentity(){
        /* Sync with node.js */
        $data = json_encode(array("command" => "user-login",
                "data" => array("PHPSESSID" => session_id(),
                    "nodeSession" => $_COOKIE["sid"],
                    "id" => NEnvironment::getUser()->getIdentity()->getId(),
                    "username" => NEnvironment::getUser()->getIdentity()->username,
                    "preferences" => NEnvironment::getUser()->getIdentity()->preferences
                )));
        $this->getTemplate()->data = usock::writeReadClose($data, 4096);
    }
}
