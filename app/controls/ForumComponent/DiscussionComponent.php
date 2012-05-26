<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of DiscussionComponent
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components{
    use \DB;
    class DiscussionComponent extends \Nette\Application\UI\Control{
        /**
         *
         * @var array Variable used to store data for later use when sending new post
         */
        protected $postdata = null;

        /**
         *
         * @var Nette\Caching\Cache
         */
        protected $cache;
        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }

        /**
         *
         * @param \Nette\Caching\IStorage $st
         * @return \Components\DiscussionComponent
         */
        public function setCache(\Nette\Caching\IStorage $st){
            $this->cache = new \Nette\Caching\Cache($st, 'discussion');
            return $this;
        }

        /**
         *
         * @param string|null $url
         */
        public function render($url = null){
            $this->template->staticPath = $this->presenter->template->staticPath;
            $user = \Nette\Environment::getUser();
            $this->template->setFile(__DIR__ . '/discussion.latte');

            $info = DB::forum_topic('urlfragment', $url)->fetch();
            $opt = $info['options'];
            $this->template->info = $info;
            $this->template->read = false;
            $this->template->write = false;
            $this->template->administrate = false;

            if($this->postdata){
                $this->addPostFinish ($url);
            }

            if($opt & ForumComponent::HAS_CUSTOM_PERMISSIONS){
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
                $this->template->write = $adm || ($user->isAllowed('discussion', 'write') && !($info['options'] & ForumComponent::$LOCKED));
                $this->template->administrate = $adm || $user->isAllowed('discussion', 'admin');
            }
            $this->template->id = $url;
            $this->template->render();
        }

        /**
         *
         * @return \Nette\Application\UI\Form
         */
        public function createComponentForumForm(){
            $form = new \Nette\Application\UI\Form;
            $form->addTextArea('post', 'Nová zpráva')->addRule(\Nette\Application\UI\Form::FILLED);
            $form->addSubmit('send', 'Přidat příspěvek');
            $form->onSuccess[] = callback($this, 'addPost');
            return $form;
        }
        /**
         *
         * @param \Nette\Application\UI\Form $form
         */
        public function addPost($form){
            $v = $form->getValues();
            $this->postdata = array(
                "author"=>\Nette\Environment::getUser()->getId(),
                "forum"=> -1,
                "time"=>time(),
                "post"=> $v["post"]
                );
        }
        /**
         *
         * @param integer $postid
         */
        public function removePost($postid){
            /* Todo: Check if user can delete post */
            if(true){
                $p = DB::forum_posts('id', $postid)->fetch();
                $f = DB::forum_topic('id', $p['forum'])->fetch();
                $url = $f['urlfragment'];
                $p->delete();
                DB::forum_posts_data('id', $postid)->delete();
                $cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\MemcachedStorage);
                $cache->remove(array(\Nette\Caching\Cache::TAGS => array('discussion/'.$url)));
            }
        }
        /**
         *
         * @param string $text
         * @return string
         */
        public static function parseBB($text){
            $bb = bbcode_create(array(
                ''=>         array('type'=>BBCODE_TYPE_ROOT),
                'i'=>        array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<i>',
                                'close_tag'=>'</i>', 'childs'=>'*'),
                'b'=>        array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<b>',
                                'close_tag'=>'</b>'),
                'u'=>        array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<u>',
                                'close_tag'=>'</u>'),
                'url'=>      array('type'=>BBCODE_TYPE_OPTARG,
                                'open_tag'=>'<a href="{PARAM}" title="Externí odkaz :: {PARAM}">', 'close_tag'=>'</a>',
                                'default_arg'=>'{CONTENT}',
                                'childs'=>'b,i,img'),
                'img'=>      array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<img src="', 'close_tag'=>'" class="ll" />',
                                'childs'=>''),
                'cite'=>      array('type'=>BBCODE_TYPE_OPTARG,
                                'open_tag'=>'<a class="anchor" href="#{PARAM}">^</a><cite>', 'close_tag'=>'</cite>',
                                'childs'=>''),
                'spoiler'=>       array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<div class="spoiler">', 'close_tag'=>'</div>'),
            ));
            return bbcode_parse($bb, $text);
        }
        /**
         *
         * @param string $url
         */
        private function addPostFinish($url){
            $this->postdata["forum"] = (int)ForumComponent::getIdByPath($url);
            $post = $this->postdata["post"];
            unset($this->postdata["post"]);
            DB::forum_posts()->insert($this->postdata);
            DB::forum_posts_data()->insert(array('post'=>$post));
            DB::forum_visit('idforum', $this->postdata['forum'])->update(array('unread'=>new \NotORM_Literal('unread + 1')));
            $this->parent->setLastAccess();
            /*dump($this);
            dump('discussion/'.$url);
            dump($this);
            $cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\MemcachedStorage,  'Nette.Templating.Cache');
            $cache->remove(array(\Nette\Caching\Cache::TAGS => array('discussion/'.$url)));
            $cache = Environment::getCache('Nette.Templating.Cache');
            $this->parent->parent->cache->remove(array(\Nette\Caching\Cache::TAGS => array('discussion/'.$url)));*/
        }
    }
}