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
        /**
         *
         * @var IStorage 
         */
        protected $storage;

        /** @persistent */
        protected $forumId;

        /**
         *
         * @var Nette\Security\User
         */
        protected $user;

        /**
         *
         * @var int
         */
        protected $lastVisit = 0;

        public function link($target, $args = array()){
            return $this->getPresenter()->link($target, $args);
        }

        /**
         *
         * @param \Nette\Caching\IStorage $st
         * @return \Components\DiscussionComponent
         */
        public function setCache(\Nette\Caching\IStorage $st){
            $this->storage = $st;
            $this->cache = new \Nette\Caching\Cache($st, 'discussion');
            return $this;
        }
        /**
         *
         * @param \Nette\Security\User $user
         * @return \Components\DiscussionComponent
         */
        public function setUser(\Nette\Security\User $user){
            $this->user = $user;
            return $this;
        }

        /**
         *
         * @param int $visit
         * @return \Components\DiscussionComponent
         */
        public function setLastVisit($visit){
            $this->lastVisit = $visit;
            return $this;
        }

        /**
         *
         * @param string|null $url
         */
        public function render($url = null){
            #$this->redirect('this');
            $this->template->staticPath = $this->presenter->template->staticPath;
            $user = $this->user;
            $this->template->setFile(__DIR__ . '/discussion.latte');

            $info = DB::forum_topic('urlfragment', $url)->fetch();
            $opt = $info['options'];

            //$visit = DB::forum_visit(array('idforum' => $info['id'], 'iduser' => $user->getId()))->select('time')->fetch();
            $this->template->lastvisit = $this->lastVisit;
            
            $vp = new \VisualPaginator($this, 'vp');
            $paginator = $vp->getPaginator();
            $paginator->setItemsPerPage(20);
            $paginator->setItemCount(DB::forum_posts('forum', $info['id'])->count());
            $this->template->vp = $vp;
            //dump($this->getParent()->context->request);
            if(isset($this->getParent()->getParent()->getRequest()->parameters["forum-discussion-vp-page"]))
                $paginator->setPage($this->getParent()->getParent()->getRequest()->parameters["forum-discussion-vp-page"]);

            $this->forumId = $info['id'];

            $this->template->info = $info;
            $this->template->read = false;
            $this->template->write = false;
            $this->template->administrate = false;

            if($opt & ForumComponent::HAS_CUSTOM_PERMISSIONS){
                // TODO: custom permissions in discussions
            }else{
                $admins = DB::forum_moderator('forumid', $info['id']);
                $adms = array();
                foreach($admins as $admin){
                    $aname = DB::users('id', $admin['userid'])->fetch();
                    $adms[] = $aname['username'];
                }
                $adm = ($user->getId() == $info['owner']) || ($user->getIdentity() ? in_array($user->getIdentity()->name, $adms) : false);
                $this->template->read = $adm ||$user->isAllowed('discussion', 'read');
                $this->template->write = $adm || ($user->isAllowed('discussion', 'write') && !($info['options'] & ForumComponent::LOCKED));
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
            $form->addHidden('discussionid');
            $form->setDefaults(array(
                'discussionid' => $this->forumId
            ));
            $form->addText('whisper', 'Šeptat');
            $form->onSuccess[] = callback($this, 'addPost');
            return $form;
        }

        /**
         *
         * @param \Nette\Application\UI\Form $form
         */
        public function addPost($form){
            $v = $form->getValues();
            $url = $v['discussionid'];
            $postdata = array(
                "author"=>\Nette\Environment::getUser()->getId(),
                "forum"=> $url,
                "time"=>time()
                );
            $post = $v["post"];
            unset($v["post"]);
            $pst = DB::forum_posts()->insert($postdata);
            DB::forum_posts_data()->insert(array('post'=>$post));
            DB::forum_visit('idforum', $this->postdata['forum'])->update(array('unread'=>new \NotORM_Literal('unread + 1')));
            //$this->parent->setLastAccess();
            $cache = new \Nette\Caching\Cache($this->storage, 'Nette.Templating.Cache');
            $u = DB::forum_topic('id', $url)->fetch();
            $urlf = $u['urlfragment'];
            $cache->clean(array(
                \Nette\Caching\Cache::TAGS => array('discussion/'.$urlf),
            ));
            /* Propagate new post all the way up */
            $this->getParent()->propagateNewPost($url, $pst['id']);
            $this->redirect('this');
        }

        /**
         *
         * @param integer $postid
         */
        public function removePost($postid){
            /** @todo Check if user can delete post */
            if(true){
                $p = DB::forum_posts('id', $postid)->fetch();
                $f = DB::forum_topic('id', $p['forum'])->fetch();
                $url = $f['urlfragment'];
                $p->delete();
                DB::forum_posts_data('id', $postid)->delete();
                $cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\MemcachedStorage);
                $cache->remove(array(\Nette\Caching\Cache::TAGS => array('discussion/'.$url)));
                $this->getParent()->propagatePostDeletion($f['id']);
            }
        }

        /**
         *
         * @param string $text
         * @return string
         */
        public function parse($text){
            $cache = $this->cache;
            if(false && $cache){
                $t = $cache->load($text);
                if($t !== null){
                    return $t;
                }
            }
            // Find Tags
            $ts = $this->presenter->link('search:');
            $out = preg_replace('/\#(([a-zA-Z0-9]|-|_){3,})/i', '<a href="'.$ts.'$1/">$0</a>', $text);

            $out = self::parseBB($out);


            // Find users


            if($cache)
                $cache->save($text, $out);
            return $out;
        }
        /**
         *
         * @param string $text
         * @return string
         */
        public static function parseBB($text){
            
            //dump($text);

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
                                'open_tag'=>'<a class="citation" href="#{PARAM}">{CONTENT}</a>' .
                                                     '<cite class="cite-msg-{PARAM}" data-msg="{PARAM}">&quot;', 'close_tag'=>'&quot;</cite>',
                                'childs'=>''),
                'spoiler'=>       array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<div class="spoiler">', 'close_tag'=>'</div>'),
            ));
            $bbout = bbcode_parse($bb, $text);

            return $bbout;
        }
    }
}