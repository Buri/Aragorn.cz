<?php

namespace ajaxModule{
    use \Nette\Environment;
    use \Node;
    use \DB;
    
    class ajaxPresenter extends \BasePresenter {
        
        public function startup(){
            parent::startup();
            header("Content-type: application/xml");
            //header("Content-type: text/plain");
            header("Content-type: text/html");
            $this->node->setUser($this->context->user);
            $this->setView('default');
            $this->template->data = "";
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
            $this->getTemplate()->data = $this->node->userlogin($sid, $this->permissions, $this->context->user);
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

        /*
         *
         * SECTION USER
         *
         */
        public function actionUserLookup($search)
        {
            $db = $this->context->database;
            $this->setView('json');
            header('Content-type: application/json');
            $out = array();
            foreach($db->users("username like ? ", $search . '%') as $user){
                $name = $user['username'];
                $p = $db->users_profiles('id', $user['id'])->fetch();
                $out[] = array($p['urlfragment'], $name);
            }
            $this->template->out = json_encode($out);
        }

        /* SECTION SETTINGS */
        public function actionSettingsChatColorChange($color = null){ //Changechatcolor
            $this->context->database->users_preferences('id', $this->user->getId())->update(array("chatcolor"=>$color));
            $p = $this->context->preferences->get($this->getUser()->getId());
            $this->getUser()->getIdentity()->preferences = $p['preferences'];
            $this->template->data = "OK";
        }

        public function actionSettingsIconChange(){
            $this->template->data = \frontendModule\settingsPresenter::changeIcon($_POST);
        }



        /*
         *
         * SECTION FORUM
         *
         */
        public function actionForumThreadDelete($id){
            /*$forum = new \Components\ForumComponent($this, 'tempForum');
            $forum->setContext($this->context);
            if($forum->userIsAllowed('forum', 'delete', $id)){
                $forum->deleteForum($id);
                $this->template->data = "Forum smazĂˇno.";
            }else{
                $this->template->data = "NemĂˇte oprĂˇvnÄ›nĂ­ ke smazĂˇnĂ­ fora.";
            }*/
            $model = new \Components\Models\ForumControl($this->context->database, $this->context->authorizator, $this->context->cacheStorage);
            $this->template->data = $model->forum->setID($id)->delete();
        }
        
        public function actionForumThreadAdd($id = null, $param = null, $prefix = ""){
            $model = new \Components\Models\ForumControl($this->context->database, $this->context->authorizator, $this->context->cacheStorage);
            $this->template->data = $model->forum->create($id, $param, $prefix);
        }
        

        public function actionForumPostDelete($id){
            $this->template->data = "fail";
            $model = new \Components\Models\ForumControl($this->context->database, $this->context->authorizator, $this->context->cacheStorage);
            if($model->post->setID($id)->delete())
                $this->template->data = "ok";
        }

        public function actionForumPostGetSingle($id){
            $this->setView('forum-post');
            $this->template->post = $this->context->database->forum_posts('id', $id)->fetch();
            $this->template->postd = $this->context->database->forum_posts_data('id', $id)->fetch();
        }

        public function actionForumPostGetRaw($postid){
            $this->setView('forum-post-raw');
            $p = $this->context->database->forum_posts_data('id', $postid)->fetch();
            $this->template->data = $p['post'];
        }
        public function actionForumOptionsUpdate($id,$noticeboard,$description){
            $this->template->data = "fail";
            $model = new \Components\Models\ForumControl($this->context->database, $this->context->authorizator, $this->context->cacheStorage);
            if($model->forum->setID($id)->isAllowed('admin')){
                $this->template->data = "off";
                $row = $this->context->database->forum_topic('id', $id);
                $row->update(array("noticeboard"=>$noticeboard, "description" => $description));
                $this->template->data = "ok";
            }
        }
        public function actionForumBookmarkToggle($id){
            $this->template->data = "fail";

            if(!$this->user->isLoggedIn()){
                return;
            }

            if($this->context->database->forum_visit(array(
                "iduser" => $this->user->getId(),
                "idforum" => $id
                    ))->update(array(
                "bookmark" => new \NotORM_Literal("!bookmark")
            )) !== false)
                $this->template->data = "ok";
        }



        public function actionWait($id = 1000){
            usleep($id);
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
            $state = $this->node->isUserOnline($r['id']);
            $state = $state ? "lime" : ($state == "away" ? "yellow" : "red");
            $userver = $this->context->parameters['servers']['userContent'];
            $this->template->data = "<div style=\"width:200px; min-height:90px; text-align:left;\">
                <div style=\"width:85px;display:inline:block;float:left;border-right:1px solid gray;\">
                    <img src=\"http://".$userver."/i/".$r['icon']."\" style=\"max-height:80px;max-width:80px;\"/>
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
