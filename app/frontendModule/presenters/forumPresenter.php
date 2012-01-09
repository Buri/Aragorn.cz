<?php
namespace frontendModule{
    use \DB;
    use \Nette\Environment;
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
        private $postdata = null, $url = null;
        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }
        
        public function userIsAllowed($resource, $action, $target = null, $targettype = null){
            $user = \Nette\Environment::getUser();
            $uid = $user->getId();
            $info = DB::forum_topic('urlfragment', $this->url)->fetch();
            $opt = $info['options'];
            $p = new \Permissions ();
            if($target){
                switch($targettype){
                    case "post":
                        break;
                    case "forum":
                        if($uid == $info['owner']){
                            $p->setOwner('forum');
                        }
                        $adms = DB::forum_moderator('forumid', $info['id']);
                        foreach($adms as $adm){
                            if($adm['userid'] == $uid){
                                
                            }
                        }
                    default:
                        $p->rollback();
                        return false;
                }
            }
            if($opt & ForumComponent::$HAS_CUSTOM_PERMISSIONS){
                
            }else{
                $resource = 'forum';
            }
            $p->rollback();
            return \Nette\Environment::getUser()->isAllowed($resource, $action);
        }
        
        public function render($url = null){
            if($this->postdata){
                $this->addPostFinish ($url);
            }
            $this->url = $url;
            $this->template->staticPath = $this->presenter->template->staticPath;
            $user = \Nette\Environment::getUser();           
            $this->template->setFile(__DIR__ . '/../templates/forum/discussion.latte');
            $info = DB::forum_topic('urlfragment', $url)->fetch();
            $opt = $info['options'];
            $this->template->read = false;
            $this->template->write = false;
            $this->template->administrate = false;
            if($opt & ForumComponent::$HAS_CUSTOM_PERMISSIONS){
                // MUST BE COMPLETED!!!!!
            }else{
                $admins = DB::forum_moderator('forumid', $info['id']);
                $adms = array();
                foreach($admins as $admin){
                    $aname = DB::users('id', $admin['userid'])->fetch();
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
            $v = $form->getValues();
            $this->postdata = array(
                "author"=>\Nette\Environment::getUser()->getId(),
                "forum"=> -1,
                "time"=>time(),
                "post"=> $v["post"]
                );
        }
        public static function parseBB($text){
            $bb = bbcode_create(array(
                ''=>         array('type'=>BBCODE_TYPE_ROOT),
                'i'=>        array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<i>',
                                'close_tag'=>'</i>', 'childs'=>'b'),
                'url'=>      array('type'=>BBCODE_TYPE_OPTARG,
                                'open_tag'=>'<a href="{PARAM}" title="Externí odkaz :: {PARAM}">', 'close_tag'=>'</a>',
                                'default_arg'=>'{CONTENT}',
                                'childs'=>'b,i,img'),
                'img'=>      array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<img src="', 'close_tag'=>'" />',
                                'childs'=>''),
                'b'=>        array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<b>',
                                'close_tag'=>'</b>'),
                'list'=>     array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<ul>',
                                'close_tag'=>'</ul>',
                                'childs'=>'*'),
                '*'=>       array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<li>', 'close_tag'=>'</li>'),
            ));
            return bbcode_parse($bb, $text);
        }
        private function addPostFinish($url){
            $this->postdata["forum"] = (int)ForumComponent::getIdByPath($url);
            //echo DiscussionComponent::parseBB($this->postdata);
            DB::forum_posts()->insert($this->postdata);
        }
    }
        
    class ForumComponent extends \Nette\Application\UI\Control{        
        static $SUBTOPIC_ALLOWED = 1, $POSTS_ALLOWED = 2, $HAS_CUSTOM_PERMISSIONS = 4;
        function __construct($id = null, $url = null){
            $this->template->id = $id;
            $this->template->url = $url;
            parent::__construct();
        }
        
        public function getLastPost($forum){
            $r = DB::forum_posts('forum', $forum)->order('time DESC')->limit('0,1');
            if(!$r->count()) return false;
            $r = $r->fetch();
            return array('time' => $r['time'], 'author'=>$this->template->control->presenter->userLink($r['author']));
        }
        
        public static function getPostCount($forum){
            $c = DB::forum_posts('forum', $forum)->select('count(id) as count')->fetch();
            return $c['count'];
        }
        
        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }
        
        public static function deleteForum($id){
            $discussions = DB::forum_topics('parent', $id);
            foreach($discussions as $topic){
                $tid = $topic['id'];
                self::deleteForum($tid);
                DB::forum_posts('forum', $tid)->delete();
                DB::forum_moderator('forumid', $tid)->delete();
                DB::forum_visit('idforum', $tid)->delete();
                DB::forum_topic('id', $tid)->delete();
            }
        }
        
        private function prepare($id, $url){
            $nav = array();
            $this->template->discussion = false;
            $this->template->newforum = false;
            $this->template->noticeboard = "";
            if(is_null($id) || !is_numeric($id)){
                if(isset($url) && $url != ""){
                    $p = DB::forum_topic('urlfragment', $url)->fetch();
                    $this->template->noticeboard = $p['noticeboard'];
                    $this->template->discussion = $p['options'] & self::$POSTS_ALLOWED;
                    $this->template->newforum = ($p['options'] & self::$SUBTOPIC_ALLOWED) && Environment::getUser()->isAllowed('forum','create');
                    $data = DB::forum_topic('parent', $p['id'])->order('sticky DESC', 'name ASC');
                    do{
                        $nav[] = array("url" => $p["urlfragment"], "name"=> $p["name"]);
                        $p = DB::forum_topic('id', $p['parent'])->fetch();
                    }while($p["parent"]);
                }else{
                    $data = DB::forum_topic('parent', 0)->order('sticky DESC', 'name ASC');
                    $this->template->newforum = \Nette\Environment::getUser()->isAllowed('forum','create');
                }
            }else{
                $data = DB::forum_topic('id', $id)->order('sticky DESC', 'name ASC');
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
        
        public static function getIdByPath($path){
            if(!$path) throw new Exception ("Path is not defined");
            $p = DB::forum_topic('urlfragment', $path)->fetch();
            return $p['id'];
        }
    }
}