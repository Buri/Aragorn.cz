<?php

namespace ajaxModule{
    use \Nette\Environment;
    use \usock;
    use \Node;
    use \DB;
    
    class ajaxPresenter extends \BasePresenter {
        
        public function startup(){
            header("Content-type: application/xml");
            #header("Content-type: text/plain");
            header("Content-type: text/html");
            $this->setView('default');
            $this->template->data = "";
            parent::startup();
        }

        public function actionDefault(){
            $this->getTemplate()->data = "<version>1.0</version>";
        }
        
        public function actionHelp(){
            
        }
        
        public function actionLoginui(){
            $this->setView('loginui');
        }
        public function actionTestidentity($sid = 0){
            /* Sync with node.js */
            $this->getTemplate()->data = Node::userlogin($sid);
        }
        public function actionStatusupdate($id){
            $ok = DB::users_profiles('id', \Nette\Environment::getUser()->getId())->update(array('status' => $id)) ? true : false;
            try{
                //$ok = $ok &&  Node::changeStatus($id);
                $this->getTemplate()->data = $ok ? "ok" : "fail";
            }
            catch(\Exception $e){
                $this->getTemplate()->data = $e->getMessage();
            }
        }
        
        public function actionNewforum($id = null, $param = null){
            $parentid = 0;
            if($param != ""){
                $parent = DB::forum_topic('urlfragment', $param)->fetch();
                $parentid = $parent['id'];
            }
            try{
                echo DB::forum_topic()->insert(array(
                    "name"=>$id,
                    "owner"=>\Nette\Environment::getUser()->getId(),
                    "description" => "Nové forum",
                    "parent" => $parentid,
                    "urlfragment" => \Utilities::string2url($id),
                    "created" => time()
                ));
                $this->template->data = "Forum bylo založeno.";
            }
            catch(\Exception $e){
                $this->template->data = $e->getMessage();
            }
        }
        
        public function actionDeleteforum($id = null, $param = null){
            $forum = new \frontendModule\ForumComponent();
            if($forum->userIsAllowed('forum', 'delete', $id)){
                $forum->deleteForum($id);
                $this->template->data = "Forum smazáno.";
            }else{
                $this->template->data = "Nemáte oprávnění ke smazání fora.";
            }
        }
        
        public function actionForumdeletepost($id){
            $this->template->data = "fail";
            $forum = new \frontendModule\ForumComponent();
            if($forum->userIsAllowed('post', 'delete', $id)){
                $forum = DB::forum_posts('id', $id);
                $fid = $forum['id'];
                $ftime = $forum['time'];
                DB::forum_posts('id', $id)->delete();
                DB::forum_visit()->where('idforum = ? AND time() < ? AND iduser <> ?', array($fid, $ftime, \Nette\Environment::getUser()->getId()))
                        ->update(array('unread'=>new \NotORM_Literal("unread - 1")));
                DB::forum_visit('unread < ?', 0)->update(array('unread'=>0));
                $this->template->data = "ok";
            }
        }
        public function actionWait($id = 1000){
            usleep($id);
        }
        public function actionUpdateforumnoticeboard($id,$param,$description){
            $this->template->data = "fail";
            $forum = new \frontendModule\ForumComponent();
            if($forum->userIsAllowed('forum', 'admin', $id)){
                $this->template->data = "off";
                $row = DB::forum_topic('id', $id);
                $row->update(array("noticeboard"=>$param, "description" => $description));
                $this->template->data = "ok";
            }
        }
        
        public function actionChangeicon(){
            $this->template->data = \frontendModule\settingsPresenter::changeIcon($_POST);
        }
        
        public function actionFrontendupdatewidgetlist($list){
            $this->template->data = \frontendModule\dashboardPresenter::updateWidgetList($list);
        }
        
        public function actionAddwidget($id){
            $this->template->data = \frontendModule\settingsPresenter::addWidget($id);
        }
        
        public function actionBank($id, $params = ""){
            $this->template->data = \Bank::handleAjax($id, $params);
        }
        
        public function actionProfileinfo($id) {
            $r = DB::users_profiles('urlfragment', $id)->fetch();
            $info = DB::users('id', $r['id'])->fetch();
            $group = DB::groups('id', $info['groupid'])->fetch();
            $state = Node::isUserOnline($id);
            $state = $state == "online" ? "lime" : ($state == "away" ? "yellow" : "red");
            $this->template->data = "<div style=\"width:200px; min-height:90px; text-align:left;\">
                <div style=\"width:85px;display:inline:block;float:left;border-right:1px solid gray;\">
                    <img src=\"http://".\Nette\Environment::getVariable("userServer", "www.aragorn.cz")."/i/".$r['icon']."\" style=\"max-height:80px;max-width:80px;\"/>
                </div>
                <div style=\"width:100px;display:inline:block;float:left;padding-left:3px;\">
                    <span style=\"background-color:".
                    $state.";border-radius:50%;width:12px;display:inline-block;\">&nbsp</span>&nbsp;
                    <b>".$info['username']."</b>
                    <hr/>
                    <i>".$group['name']."</i>
                    <br/>
                    <span>".$r['status']."</span>
                </div>
            </div>";
        }


    }
}
