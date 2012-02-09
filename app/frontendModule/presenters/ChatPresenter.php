<?php



namespace frontendModule{
    use \DB;
    use Nette\Environment;
    use \usock;
    class chatPresenter extends \BasePresenter {

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
                    "max" => $row["max"],
                    "occupants" => $users);
            }
        }
        public function actionRoom($id, $param = null){
            $room = DB::chatrooms('id', $id);
            if(!$room->count() || \Nette\Environment::getUser()->getId() == null){
                $this->redirect(301, 'chat:');
                exit;
            }
            $room = $room->fetch();
            if(!DB::chatroom_occupants('idroom = ? AND idusers = ?', array($id, Environment::getUser()->getId()))->count() && !(Environment::getUser()->isAllowed('chat', 'ninja') || $param = "ninja")){
                $this->redirect(301, 'chat:enter', $id);
            }
            $t = $this->getTemplate();
            $t->param = $param;
            $t->rid = $id;
            $t->title = $room["name"];
        }

        public function actionEnter($id, $param){
            $user = Environment::getUser();
            switch($this->canEnter($id, $param)){
                case 'OK':
                    $r = DB::chatrooms('id', $id)->select('id,password,max')->fetch();
                    if(!DB::chatroom_occupants()->where('idusers = ?', Environment::getUser()->getId())->count())
                        DB::chatroom_occupants()->insert(array("id"=>0, "idroom"=>$id, "idusers"=>Environment::getUser()->getId(), "activity"=>time()));
                    $usr = DB::users_profiles('id', $user->getId())->fetch();
                    usock::writeReadClose(json_encode(array("command" => "chat",
                        "data" => array(
                            "uid" => Environment::getUser()->getId(),
                            "name" => Environment::getUser()->getIdentity()->username,
                            "room" => $r["id"],
                            "action" => "enter",
                            "info" => array(
                                "permissions" => array(
                                    "delete" => $user->isAllowed('chat', 'delete')
                                ),
                                "icon" => 'http://' . Environment::getVariable('userServer', 'user.aragorn.cz') . '/i/' . $usr['icon'],
                                "status" => $usr['status'],
                                "id" => $user->getId()
                            )
                        )
                    )), 4096);
                    $this->redirect(301, 'chat:room', $id);
                    break;
                case 'BAD_PASSWD':
                    $this->redirect(301, 'chat:badpasswd');
                    break;
                case 'NOT_FOUND':
                    $this->redirect(301, 'chat:');
                    break;
            }
        }

        public function actionNinja($id, $param){
            if(Environment::getUser()->isAllowed('chat', 'ninja'))
                $this->redirect(301, 'chat:room', array('id' => $id, 'param' => 'ninja'));
            else
                $this->redirect(301, 'chat:');
        }

        private function canEnter($id, $passwd = null){
            $r = DB::chatrooms('id', $id)->select('id,password,max');
            if($r->count()){
                $r = $r->fetch();
                if(!$r["password"] || Environment::getUser()->isAllowed('chat', 'override_password') || $r["password"] == sha1($passwd)){
                    if($r["max"] && !Environment::getUser()->isAllowed('chat', 'override_limit') && DB::chatroom_occupants("idroom", $id)->count() >= $r["max"]){
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
        public function actionBadpasswd(){
            $this->setView('default');
            $this->getTemplate()->message = "Zadali jste špatné heslo.";
            $this->actionDefault();
        }

        public function actionLeave($id, $param = null){
            $u = DB::chatroom_occupants('idroom = ? AND idusers = ?', array($id, Environment::getUser()->getId()));
            if($u->count()){
                $u->delete();
                usock::writeReadClose(json_encode(array("command" => "chat",
                        "data" => array(
                            "uid" => Environment::getUser()->getId(),
                            "name" => Environment::getUser()->getIdentity()->username,
                            "room" => $id,
                            "action" => "leave",
                            "silent" => $param == "silent" ? true : false
                        )
                    )), 4096);
            }
            $this->redirect(301, 'default');
        }
    }
}
