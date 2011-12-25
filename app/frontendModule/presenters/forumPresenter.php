<?php
namespace frontendModule{
    use \DB;
    class forumPresenter extends \BasePresenter {
        public function actionDefault($id = "") {
            $this->template->id = $id;
            $this->template->url = $id;
        }
        public static function getSubtopics($id){
            return DB::forum_topic('parent', $id)->order('name');
        }
        
    }
    
    class DiscussionComponent extends \Nette\Application\UI\Control{
        public function __construct(){
        }
        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }
        
        public function render($url = null){
            $user = \Nette\Environment::getUser();           
            $this->template->setFile(__DIR__ . '/../templates/forum/discussion.latte');
            $info = DB::forum_topic('urlfragment', $url)->fetch();
            $opt = $info['options'];
            if($opt & ForumComponent::$HAS_CUSTOM_PERMISSIONS){
                
            }else{
                $admins = DB::forum_admins('forum', $info['id']);
                $adms = array();
                foreach($admins as $admin){
                    $aname = DB::users($admin['user'])->fetch();
                    $adms[] = $aname['username'];
                }
                $adm = ($user->getId() == $info['owner']) || ($user->getIdentity() ? in_array($user->getIdentity()->name, $adms) : false);
                $this->template->read = $adm ||$user->isAllowed('discussion', 'read');
                $this->template->write = $adm || $user->isAllowed('discussion', 'write');
                $this->template->administrate = $adm || $user->isAllowed('discussion', 'admin');
            }
            $d = DB::forum_posts('forum', $info['id'])->order('time desc');
            $this->template->discuss = array();
            $authors = array();
            foreach($d as $r){
                if(empty($authors[$r['author']])){
                    $x = DB::users('id', $r['author'])->select('username')->fetch();
                    $authors[$r['author']] = $x['username'];
                }
                $this->template->discuss[] = $r;
            }
            $this->template->authors = $authors;
            $this->template->id = $url;
            $this->template->render();
        }
        
        public function createComponentForumForm(){
            $form = new \Nette\Application\UI\Form;
            $form->addTextArea('post', 'Nová zpráva')->addRule(\Nette\Application\UI\Form::FILLED);
            $form->addSubmit('send', 'Přidat příspěvek');
            $form->onSuccess[] = callback($this, 'addPost');
            return $form;
        }
        public function addPost($form){
            $this->vals = $form->getValues();
        }
    }
        
    class ForumComponent extends \Nette\Application\UI\Control{        
        static $SUBTOPIC_ALLOWED = 1, $POSTS_ALLOWED = 2, $HAS_CUSTOM_PERMISSIONS = 4;
        function __construct($id = null, $url = null){
            $this->template->id = $id;
            $this->template->url = $url;
            parent::__construct();
        }
        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }
        
        private function prepare($id, $url){
            $nav = array();
            $this->template->discussion = false;
            if(is_null($id) || !is_numeric($id)){
                if(isset($url) && $url != ""){
                    $p = DB::forum_topic('urlfragment', $url)->fetch();
                    $this->template->discussion = $p['options'] & self::$POSTS_ALLOWED;
                    $data = DB::forum_topic('parent', $p['id'])->order('name');
                    do{
                        $nav[] = array("url" => $p["urlfragment"], "name"=> $p["name"]);
                        $p = DB::forum_topic('id', $p['parent'])->fetch();
                    }while($p["parent"]);
                }else{
                    $data = DB::forum_topic('parent', 0)->order('name');
                }
            }else{
                $data = DB::forum_topic('id', $id)->order('name');
            }
            $nav[] = array("name"=>"Diskuze", "url"=>"");
            $this->getTemplate()->topics = $data;
            $this->getTemplate()->n = $nav;
        }
  
        public function render($id = null, $url = null){
            $p = $this->getPresenter();
            $this->template->forum = substr($p->name, strrpos($p->name, ':')+1).':';
            $this->template->id = $url;
            $this->template->setFile(__DIR__ . '/../templates/forum/forum.latte');
            $this->prepare($id, $url);
            $this->template->render();
        }
        
        public function createComponentDiscussion($name){
            return $this->presenter->createComponentDiscussion($name);
        }
    }
}