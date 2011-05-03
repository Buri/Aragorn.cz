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
        $data = '{"command":"user-login","data":{"PHPSESSID":"'.session_id().
        '","nodeSession":"'.$_COOKIE["sid"].
        '","id":'.NEnvironment::getUser()->getIdentity()->getId().
        ',"username":"'.addslashes(NEnvironment::getUser()->getIdentity()->data["username"]).'"}}';
        
        $this->getTemplate()->data = usock::writeReadClose($data, 4096);
    }
}
