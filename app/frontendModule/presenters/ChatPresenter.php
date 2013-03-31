<?php

namespace frontendModule{
    use \DB;
    use \Nette\Environment;
    class chatPresenter extends \BasePresenter {

        public function actionDefault(){
            $this->getTemplate()->chatrooms = array();
            foreach(DB::chatrooms() as $row){
                $users = array();
                $users = array_filter(json_decode($this->node->getConnection()->writeRead(json_encode(array("command" => "chat",
                        "data" => array(
                            "room" => $row["id"],
                            "action" => "user-name-list"
                        )
                    )), 4096)));
                $this->getTemplate()->chatrooms[] = array("name"=>$row["name"], "password"=>($row["password"] ? true : false),
                    "id" => $row["id"],
                    "description" => $row["description"],
                    "max" => $row["max"],
                    "occupants" => $users);
            }
        }
        
        public static function getChatroomOccupants(\NodeConnector $node){
            $c = array();
            foreach(DB::chatrooms() as $row){
                $c[$row['id']] = array_filter(json_decode($node->writeRead(json_encode(array("command" => "chat",
                        "data" => array(
                            "room" => $row["id"],
                            "action" => "user-name-list"
                        )
                    )), 4096)));
            }
            return $c;
        }
        public function actionRoom($id, $param = null){
            /*if(!$room->count() || \Nette\Environment::getUser()->getId() == null){
                $this->redirect(301, 'chat:');
                exit;
            }
            
            if(!DB::chatroom_occupants('idroom = ? AND idusers = ?', array($id, Environment::getUser()->getId()))->count() && !(Environment::getUser()->isAllowed('chat', 'ninja') || $param == "ninja")){
                $this->redirect(301, 'chat:enter', $id);
            }*/
            /*$users = array_filter(json_decode($this->node->getConnection()->writeRead(json_encode(array("command" => "chat",
                        "data" => array(
                            "room" => $id,
                            "action" => "user-name-list"
                        )
                    )), 4096)));
            if(!(Environment::getUser()->isAllowed('chat', 'ninja') || $param == "ninja")){
                $this->redirect(301, 'chat:enter');
                exit;
            }
            
            if(!DB::chatroom_occupants('idroom = ? AND idusers = ?', array($id, Environment::getUser()->getId()))->count() && !(Environment::getUser()->isAllowed('chat', 'ninja') || $param = "ninja")){
                $this->redirect(301, 'chat:enter', $id);
            }*/
            $usrs = self::getChatroomOccupants($this->node->getConnection());
            //dump($usrs);
            if(array_search($this->getUser()->getId(), $usrs[$id]) === false){
                $this->redirect('enter', $id);
            }
            $room = $this->context->database->chatrooms('id', $id)->fetch();
            $t = $this->getTemplate();
            $t->param = $param;
            $t->rid = $id;
            $t->title = $room["name"];
            $t->type = $room['type'];
        }

        public function actionEnter($id, $param){
            $user = $this->getUser();
            $db = $this->context->database;
            switch($this->canEnter($id, $param)){
                case 'OK':
                    $usr = $db->users('id', $user->getId())->fetch();
                    $prefs = $db->users_preferences('id', $user->getId())->fetch();
                    $this->node->getConnection()->writeReadClose(json_encode(array("command" => "chat",
                        "data" => array(
                            "uid" => $user->getId(),
                            "name" => $user->getIdentity()->username,
                            "room" => $id,
                            "action" => "enter",
                            "info" => array(
                                "permissions" => array(
                                    "delete" => $user->isAllowed('chat', 'delete')
                                ),
                                "icon" => 'http://' . $this->context->parameters['servers']['userContent'] . '/i/' . $usr['icon'],
                                "status" => $usr['status'],
                                "color" => $prefs['chatcolor'],
                            )
                        )
                    )), 4096);
                    $this->redirect(301, 'chat:room', $id);
                    break;
                case 'BAD_PASSWD':
                    $this->flashMessage('Špatné heslo.');
                    $this->redirect(301, 'chat:');
                    break;
                case 'NOT_FOUND':
                    $this->redirect(301, 'chat:');
                    break;
            }
        }

        public function actionNinja($id, $param){
            if($this->getUser()->isAllowed('chat', 'ninja'))
                $this->redirect(301, 'chat:room', array('id' => $id, 'param' => 'ninja'));
            else
                $this->redirect(301, 'chat:');
        }

        private function canEnter($id, $passwd = null){
            $db = $this->getContext()->database;
            $user = $this->getUser();
            $r = $db->chatrooms('id', $id)->select('id,password,max');
            if($r->count()){
                $r = $r->fetch();
                if(!$r["password"] || $user->isAllowed('chat', 'override_password') || $r["password"] == sha1($passwd)){
                    if($r["max"] && $user->isAllowed('chat', 'override_limit') /*&& $db->chatroom_occupants("idroom", $id)->count() >= $r["max"]*/){
                        return 'NOT_FOUND';
                    }
                    return 'OK';
                }else{
                    return 'BAD_PASSWD'; 
                }
            }else{
                return 'NOT_FOUND';
            }
        }

        public function actionLeave($id, $param = null){
            $user = $this->getUser();
            $this->node->getConnection()->writeReadClose(json_encode(array("command" => "chat",
                "data" => array(
                    "uid" => $user->getId(),
                    "name" => $user->getIdentity()->username,
                    "room" => $id,
                    "action" => "leave",
                    "silent" => $param == "silent" ? true : false
                )
            )), 4096);
            $this->redirect(301, 'default');
        }
    }
}
