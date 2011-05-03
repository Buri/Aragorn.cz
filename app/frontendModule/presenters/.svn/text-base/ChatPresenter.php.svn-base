<?php

class frontend_chatPresenter extends BasePresenter {
    public function actionDefault(){
        $this->getTemplate()->chatrooms = array();
        foreach(DB::chatrooms() as $row){
            $users = array();
            foreach($row->chatroom_occupants() as $occup){
                $users[] = $occup->users["username"];
            }
            $this->getTemplate()->chatrooms[] = array("name"=>$row["name"], "password"=>($row["password"] ? true : false),
                "id" => $row["id"],
                "description" => $row["description"],
                "occupants" => $users);;
        }
    }
    public function actionRoom($id){
        $this->getTemplate()->rid = $id;
    }
    
    public function actionRoomenter($id, $param){
        $r = DB::chatrooms()->where('id = ?', $id);
        if($r->count()){
            if(!$r["password"] || $r["password"] == $param){
                if(!DB::chatroom_occupants()->where('user = ?', NEnvironment::getUser()->getId())->count())
                    DB::chatroom_occupants()->insert(array("id"=>0, "user"=>NEnvironment::getUser()->getId(), "activity"=>time()));
                $this->redirect(301, 'chat:room', $id);
            }else{
                $this->redirect(301, 'chat:spatneheslo');
            }
        }else{
            $this->redirect(301, 'chat:');
        }
    }
    
    public function actionSpatneheslo(){
        $this->setView('default');
        $this->getTemplate()->message = "Zadali jste špatné heslo.";
        $this->getTemplate()->title = "Špatné heslo";
        $this->actionDefault();
    }
}
