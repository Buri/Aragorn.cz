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
        protected $url = null;
        protected $forumId = null;
        private $context;
        private $cache;
        protected $lastVisit;
        protected $handleUrl = null;
        protected $prefix = "";

        public $usePresenterLink = false;

        /**
         *
         * @param \Nette\DI\Container $context
         * @return \Components\ForumComponent
         */
        public function setContext(\Nette\DI\Container $context) {
            $this->context = $context;
            $this->cache = new \Nette\Caching\Cache($context->cacheStorage, 'forum');
            $this->template->cache = $this->cache;
            $this->template->db = $context->database;
            return $this;
        }
        
        public function render($url, $prefix = ""){
            if($this->handleUrl != null)
                $this->url = $this->handleUrl;
            else
                $this->url = $url;
            $this->prefix = $prefix;
            $this->prepare($this->url);
            $p = $this->getPresenter();
            $this->template->forum = substr($p->name, strrpos($p->name, ':')+1).':';
            $this->template->prefix = $this->prefix;
            $this->template->url = $this->url;
            $this->template->usePresenterLink = $this->usePresenterLink;
            $this->template->setFile(__DIR__ . '/forum.latte');
            $this->template->render();
        }

        public function handlerender($url){
            $this->handleUrl = $url;
        }

        protected function prepare($url){
            $this->template->discussion = false;
            $this->template->newforum = false;
            $this->template->noticeboard = "";
            $this->template->parent = $this;
            $nav = array();
            $model = new \Components\Models\ForumControl($this->context->database, $this->context->authorizator, $this->context->cacheStorage);
            $model->forum->userlink = callback($this->presenter, 'userLink');
            $db = $this->context->database;
            if(isset($url) && $url != ""){
                $p = $db->forum_topic('urlfragment', $url)->fetch();
                if($p){
                    $fid = $p['id'];
                    $this->forumId = $fid;
                    $this->template->noticeboard = $p['noticeboard'];
                    $this->template->discussion = !(($p['options'] & self::POSTS_ALLOWED) & self::LOCKED);
                    $data = $db->forum_topic('parent', $p['id'])->order('sticky DESC', 'name ASC');
                    $parent = $fid;
                    while($parent > 0){
                        $p = $db->forum_topic('id', $parent)->fetch();
                        $nav[] = array("url" => $p["urlfragment"], "name"=> $p["name"]);
                        $parent = $p['parent'];
                    }
                    $info = $db->forum_topic('id', $fid)->fetch();
                    $this->template->info = $info;
                    $this->template->fid = $fid;

                    $this->template->moderators = $model->forum->setID($fid)->getModerators();

                    $locked = $model->forum->isLocked();
                    $this->template->locked = $model->forum->isLocked();
                    if($locked)
                        $this->presenter->flashMessage ('Forum je uzamčené');

                    //$model->forum->increaseViews();
                }else{
                    $parent = 0;
                    $data = array();
                    $model->forum->setID(-1);
                    $this->getTemplate()->locked = false;
                }
            }else{
                $data = $db->forum_topic('parent', 0)->order('sticky DESC', 'name ASC');
                $this->template->newforum = $this->context->user->isAllowed('forum','create');
                $parent = 0;
                $model->forum->setID(-1);
                $this->getTemplate()->locked = false;
            }
            if($this->handleUrl == null && $parent != -1){
                $nav[] = array("name"=>"Diskuze", "url"=>"");
            }
            $this->getTemplate()->topics = $data;
            $this->getTemplate()->n = $nav;
            $this->lastVisit = $model->forum->setLastVisit();
            $this->getTemplate()->model = $model;
            $this->template->isLoggedIn = $this->presenter->getUser()->isLoggedIn();
        }

        /**
         *
         * @param int $id
         * @return \Components\ForumComponent
         */
        public function setForumId($id){
            $this->forumId = $id;
            return $this;
        }

        /**
         *
         * @todo: Dodělat poslední příspěvek a počet nepřečtených příspěvků
         */

        /**
         *
         * @param integer $forum
         * @return boolean|array
         */
        public function getLastPost($forum){
            $db = $this->context->database;
            $p = $db->forum_topic('id', $forum)->fetch();
            $pid = $p['lastpost'];
            if(intval($pid) == 0) return false;
            $r = $db->forum_posts('id', $pid)->fetch();
            /*if(!$r->count()) return false;
            $r = $r->fetch();*/
            return array('time' => $r['time'], 
                'author'=>$this->presenter->userLink($r['author'])
                    );
        }

        /**
         *
         * @param string $target
         * @param mixed $args
         * @return string
         */
        public function link($target, $args = array()){
            if($this->usePresenterLink)
                return $this->getPresenter()->link($target, $args);
            else
                return parent::link($target, $args);
        }

        public function createComponentDiscussion(){
            $c = new \Components\DiscussionComponent; //($this, $name, "hola");
            return $c->setCache($this->context->cacheStorage)
                    ->setUser($this->context->user)
                    ->setDB($this->context->database)
                    ->setLastVisit($this->lastVisit);
        }

        public static function getIdByPath($path){
            if(!$path) throw new Exception ("Path is not defined");
            $p = DB::forum_topic('urlfragment', $path)->fetch();
            return $p['id'];
        }
        
        public function getHU(){
            return $this->handleUrl;
        }

    }
}