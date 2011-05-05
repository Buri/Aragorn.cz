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
        $room = DB::chatrooms('id', $id);
        if(!$room->count()){
            $this->redirect(301, 'chat:');
            exit;
        }
        $room = $room->fetch();
        if(!DB::chatroom_occupants('idroom = ? AND idusers = ?', array($id, NEnvironment::getUser()->getId()))->count()){
            $this->redirect(301, 'chat:enter', $id);
        }
        $t = $this->getTemplate();
        $t->rid = $id;
        $t->title = $room["name"];
    }
    
    public function actionEnter($id, $param){
        $r = DB::chatrooms('id', $id)->select('id,password');
        if($r->count()){
            $r = $r->fetch();
            if(!$r["password"] || $r["password"] == $param){
                if(!DB::chatroom_occupants()->where('idusers = ?', NEnvironment::getUser()->getId())->count())
                    DB::chatroom_occupants()->insert(array("id"=>0, "idroom"=>$id, "idusers"=>NEnvironment::getUser()->getId(), "activity"=>time()));
                usock::writeReadClose('{"command":"chat", "data":{"uid":'.NEnvironment::getUser()->getId().',"name":'.json_encode(NEnvironment::getUser()->getIdentity()->data["username"]).', "room":'.$r["id"].', "action":"enter"}}', 4096);
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
        $this->actionDefault();
    }
    
    public function actionLeave($id){
        $u = DB::chatroom_occupants('idroom = ? AND idusers = ?', array($id, NEnvironment::getUser()->getId()));
        if($u->count()){
            $u->delete();
            usock::writeReadClose('{"command":"chat", "data":{"uid":'.NEnvironment::getUser()->getId().',"name":'.json_encode(NEnvironment::getUser()->getIdentity()->data["username"]).', "room":'.$id.', "action":"leave"}}', 4096);
        }
        $this->redirect(301, 'default');
    }
    
    
}
