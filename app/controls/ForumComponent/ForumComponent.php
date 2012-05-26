<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of ForumComponent
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components{
    use \DB;
    class ForumComponent extends \Nette\Application\UI\Control{
        const SUBTOPIC_ALLOWED = 1, POSTS_ALLOWED = 2, HAS_CUSTOM_PERMISSIONS = 4, LOCKED = 8;
        private $url = null;
        private $context;
        private $cache;

        /**
         * @param integer $id
         * @param string $url
         */
        function __construct($id = null, $url = null){
            parent::__construct();
            $this->template->id = $id;
            $this->template->url = $url;
        }

        /**
         *
         * @param \Nette\DI\Container $context
         * @return \Components\ForumComponent
         */
        public function setContext(\Nette\DI\Container $context) {
            $this->context = $context;
            $this->cache = new \Nette\Caching\Cache($context->cacheStorage, 'forum');
            $this->template->cache = $this->cache;
            return $this;
        }

        /**
         *
         * @param integer $forum
         * @return boolean|array
         */
        public function getLastPost($forum){
            $r = DB::forum_posts('forum', $forum)->order('time DESC')->limit(1);
            if(!$r->count()) return false;
            $r = $r->fetch();
            return array('time' => $r['time'], 'author'=>$this->template->control->presenter->userLink($r['author']));
        }

        /**
         *
         * @param integer $forum
         * @return array
         */
        public static function getPostCount($forum){
            $c = DB::forum_posts('forum', $forum)->select('count(id) as count')->fetch();
            $u = DB::forum_visit('idforum = ? AND iduser = ?', array($forum, \Nette\Environment::getUser()->getId()))->fetch();
            return array('total' => $c['count'], 'unread' => $u == null ? (int)$c['count'] : (int)$u['unread']);
        }

        /**
         *
         * @param string $target
         * @param mixed $args
         * @return string
         */
        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }

        public static function deleteForum($id){
            if($id == 0) return false;
            $discussions = DB::forum_topic('parent', $id);
            //echo $discussions;
            foreach($discussions as $topic){
                $tid = $topic['id'];
                self::deleteForum($tid);
                DB::forum_posts('forum', $tid)->delete();
                DB::forum_moderator('forumid', $tid)->delete();
                DB::forum_visit('idforum', $tid)->delete();
                DB::forum_topic('id', $tid)->delete();
            }
            DB::forum_posts('forum', $id)->delete();
            DB::forum_topic('id', $id)->delete();
            return true;
        }

        private function prepare($id, $url){
            $nav = array();
            $this->template->discussion = false;
            $this->template->newforum = false;
            $this->template->noticeboard = "";
            if(is_null($id) || !is_numeric($id)){
                if(isset($url) && $url != ""){
                    $p = DB::forum_topic('urlfragment', $url)->fetch();
                    $fid = $p['id'];
                    $this->template->noticeboard = $p['noticeboard'];
                    $this->template->discussion = !(($p['options'] & self::POSTS_ALLOWED) & self::LOCKED);
                    $this->template->newforum = !(($p['options'] & self::SUBTOPIC_ALLOWED) & self::LOCKED) && $this->userIsAllowed('forum','create', $p['id']);
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
                $fid = $data['id'];
            }
            if(isset($fid)){
                $info = DB::forum_topic('id', $fid)->fetch();
                $this->template->info = $info;
                $this->template->fid = $fid;
            }
            $nav[] = array("name"=>"Diskuze", "url"=>"");
            $this->getTemplate()->topics = $data;
            $this->getTemplate()->n = $nav;
        }

        public function setLastAccess(){
            $db = DB::forum_topic('urlfragment', $this->url)->fetch();
            if($db['id'] && \Nette\Environment::getUser()->getId() != null){
                DB::forum_visit()->insert_update(
                        array('iduser'=>\Nette\Environment::getUser()->getId(), 'idforum'=>$db['id']),
                        array('time'=>time(), 'unread'=>0),
                        array('time'=>time(), 'unread'=>0)
                    );
            }
        }

        public function render($id = null, $url = null){
            $p = $this->getPresenter();
            $this->url = $url;
            $this->template->forum = substr($p->name, strrpos($p->name, ':')+1).':';
            $this->template->id = $url;
            $this->template->setFile(__DIR__ . '/forum.latte');
            $this->prepare($id, $url);
            $this->setLastAccess();
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
        public function userIsAllowed($resource, $action, $target = null){
            $user = $this->context->user;
            $uid = $user->getId();
            $info = DB::forum_topic('id = ? OR urlfragment = ? ', array($target, $this->url))->fetch();
            $opt = $info['options'];
            $p = $this->context->Permissions;
            if($opt & ForumComponent::HAS_CUSTOM_PERMISSIONS){
                // Load custom permissions
            }
            if($target){
                switch($resource){
                    case "post":
                    case "forum-post":
                        $post = DB::forum_posts('id', $target)->fetch();
                        if($post['author'] == $uid){
                            $p->setOwner ($resource.$target);
                        }
                        break;
                    case "forum":
                        if($uid == $info['owner']){
                            $p->setOwner($resource.$target);
                        }
                        $adms = DB::forum_moderator('forumid', $info['id']);
                        foreach($adms as $adm){
                            if($adm['userid'] == $uid){
                                $p->setResource('forum'.$target, array("_ALL" => true, 'owner'=>false));
                            }
                        }
                        break;
                    default:
                        return false;
                }
                return $user->isAllowed($resource.$target, $action);
            }
            return \Nette\Environment::getUser()->isAllowed($resource, $action);
        }

    }
}