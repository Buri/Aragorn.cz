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

        /**
         *
         * @var NotORM
         */
        protected $db;

        protected $locked = false;

        /** @var bool */
        public $allowWhisper = false;

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

        public function setDB(\NotORM $d) {
            $this->db = $d;
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
            $this->template->setFile(__DIR__ . '/discussion.latte');
            $db = $this->db;

            $model = new \Components\Models\ForumControl($this->db, $this->presenter->context->authorizator, $this->presenter->context->cacheStorage);

            $info = $db->forum_topic('urlfragment', $url)->fetch();

            //$visit = $db->forum_visit(array('idforum' => $info['id'], 'iduser' => $user->getId()))->select('time')->fetch();
            $this->template->lastvisit = $this->lastVisit;
            
            $vp = new \VisualPaginator($this, 'vp');
            $paginator = $vp->getPaginator();
            $paginator->setItemsPerPage(20);
            $paginator->setItemCount($db->forum_posts('forum', $info['id'])->count());
            $this->template->vp = $vp;
            
            if(isset($this->getParent()->getParent()->getRequest()->parameters["forum-discussion-vp-page"]))
                $paginator->setPage($this->getParent()->getParent()->getRequest()->parameters["forum-discussion-vp-page"]);

            $this->forumId = $info['id'];
            $model->forum->setID($this->forumId);

            if(!$model->forum->isAllowed('read'))
                $this->template->setFile(__DIR__ . '/discussion-denied.latte');

            $this->template->info = $info;
            $locked = $this->template->locked = $model->forum->isLocked();
            
            /*if($opt & ForumComponent::HAS_CUSTOM_PERMISSIONS){
                // TODO: custom permissions in discussions
            }else{
                $admins = $db->forum_moderator('forumid', $info['id']);
                $adms = array();
                foreach($admins as $admin){
                    $aname = $db->users('id', $admin['userid'])->fetch();
                    $adms[] = $aname['username'];
                }
                $adm = ($user->getId() == $info['owner']) || ($user->getIdentity() ? in_array($user->getIdentity()->name, $adms) : false);
                $this->template->read = $adm ||$user->isAllowed('discussion', 'read');
                $this->template->write = $adm || ($user->isAllowed('discussion', 'write') && !($info['options'] & ForumComponent::LOCKED));
                $this->template->administrate = $adm || $user->isAllowed('discussion', 'admin');
            }*/

            if($locked)
                $this->flashMessage ('Diskuze je uzamčená.');
            
            $this->template->id = $url;
            $this->template->model = $model;
            $this->template->allowWhisper = $this->allowWhisper;
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
            $form->addHidden('action');
            $form->setDefaults(array(
                'discussionid' => $this->forumId,
                'action' => "add"
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
            $model = new \Components\Models\ForumControl($this->db, $this->presenter->context->authorizator, $this->presenter->context->cacheStorage);

            $db = $this->db;
            
            if($v['action'] == "add"){
                $url = $v['discussionid'];
                $postdata = array(
                    "author"=>\Nette\Environment::getUser()->getId(),
                    "forum"=> $url,
                    "time"=>time()
                    );
                $post = $v["post"];
                unset($v["post"]);
                $pst = $db->forum_posts()->insert($postdata);
                $db->forum_posts_data()->insert(array('post'=>$post));
                $db->forum_visit('idforum', $this->postdata['forum'])->update(array('unread'=>new \NotORM_Literal('unread + 1')));
                //$this->parent->setLastAccess();
                $cache = new \Nette\Caching\Cache($this->storage, 'Nette.Templating.Cache');
                $u = $db->forum_topic('id', $url)->fetch();
                $urlf = $u['urlfragment'];
                $cache->clean(array(
                    \Nette\Caching\Cache::TAGS => array('discussion/'.$urlf),
                ));
                /* Propagate new post all the way up */
                $this->getParent()->propagateNewPost($url, $pst['id']);
                if($this->parent->getHU())
                    $this->redirect('render!', $this->parent->getHU());
                $this->redirect('this');
            }else if(substr($v['action'], 0, 4) == "edit"){
                $id = substr($v['action'], 4);
                $v['id'] = $id;
                if($model->post->setID($id)->edit($v)){
                    $this->flashMessage('Editace OK');
                    $this->redirect('this');
                }
                $this->flashMessage('Editace příspěvku selhala.');
            }else{
                $this->flashMessage('Chyba během zpracovávání příspěvku.');
            }
        }

        /**
         *
         * @param string $text
         * @return string
         */
        public function parse($text){
            $cache = $this->cache;
            if($cache){
                $t = $cache->load($text);
                if($t !== null){
                    //dump("Cached post");
                    return $t;
                }
            }
            // Find Tags
            $tagPattern = '/\#(([a-zA-Z0-9]|-|_){3,})/i';
            $matches = array();
            preg_match_all($tagPattern, $text, $matches);
            $out = $text;
            if(count($matches[0]) > 0){
                foreach($matches[1] as $tag){
                    /*$ur = $db->users_profiles('urlfragment like ?', $user)->fetch();
                    if(!$ur)
                        continue;
                    $uid = $ur['id'];*/
                    //$out = preg_replace('/@'.$user.'/', $this->presenter->userLink($uid), $out);
                    $out = preg_replace('/#'.$tag.'/', '<a href="'.$this->presenter->link('search:', '#' .$tag).'">$0</a>', $out);
                }
            }

            // Parse BB code
            $out = self::parseBB($out);

            // Find users
            $userPattern = '/@(([a-zA-Z0-9]|-|_){4,})/';
            $matches = array();
            preg_match_all($userPattern, $out, $matches);
            $db = $this->db;
            if(count($matches[0]) > 0){
                foreach($matches[1] as $user){
                    $ur = $db->users_profiles('urlfragment like ?', $user)->fetch();
                    if(!$ur)
                        continue;
                    $uid = $ur['id'];
                    $out = preg_replace('/@'.$user.'/', substr($this->presenter->userLink($uid), 0, -1), $out);
                }
            }

            if($cache)
                $cache->save($text, $out);
            //dump("Generated post.");
            return $out;
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
                                'open_tag'=>'<a href="{PARAM}" title="ExternĂ­ odkaz :: {PARAM}">', 'close_tag'=>'</a>',
                                'default_arg'=>'{CONTENT}',
                                'childs'=>'b,i,img'),
                'img'=>      array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<img src="', 'close_tag'=>'" class="ll" />',
                                'childs'=>''),
                'mp3'=>      array('type'=>BBCODE_TYPE_OPTARG,
                                'open_tag'=>'<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="165" height="38" id="niftyPlayer1" align=""><param name=movie value="/niftyplayer.swf?file={PARAM}&as=0"><param name=quality value=high><param name=bgcolor value=#FFFFFF><embed src="/niftyplayer.swf?file={PARAM}&as=0" quality=high bgcolor=#FFFFFF width="165" height="38" name="niftyPlayer1" align="" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>',
                                'close_tag'=>'',
                                'childs'=>''),
                'cite'=>      array('type'=>BBCODE_TYPE_OPTARG,
                                'open_tag'=>'<a class="citation" href="#{PARAM}">{CONTENT}</a>' .
                                                     '<cite class="cite-msg-{PARAM}" data-msg="{PARAM}">&quot;', 'close_tag'=>'&quot;</cite>',
                                'childs'=>''),
                'spoiler'=>       array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<div class="spoiler">', 'close_tag'=>'</div>'),
                'youtube'=>       array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<iframe width="560" height="315" src="', 'close_tag'=>'" frameborder="0" allowfullscreen></iframe>' . "\n"),
                'list'=>      array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<ul class="normal-ul">', 'close_tag'=>'</ul>',
                                'childs'=>'li'),
                'li'=>      array('type'=>BBCODE_TYPE_NOARG,
                                'open_tag'=>'<li>', 'close_tag'=>'</li>'),
            ));
            
            $bbout = bbcode_parse($bb, $text);
            $matches = array();
            $repl = "http://www.youtube.com/embed/";
            $pattern = '/src="(?:(?:.*)youtube.com\/watch\?(?:.*)v=((?:[a-zA-Z0-9]|_)+)[^"]+)/i';
            preg_match_all($pattern, $bbout, $matches);
            if(count($matches[0]) > 0){
                foreach($matches[1] as $k => $m){
                    $pat = '~' . preg_quote($matches[0][$k]) . '~i';
                    $rep = 'src="'. $repl . $m;
                    $bbout = preg_replace($pat, $rep, $bbout);
                }
            }
            return $bbout;
        }

        /**
         *
         * @param bool $lock
         * @return \Components\DiscussionComponent
         */
        public function setLock($lock){
            $this->locked = $lock;
            return $this;
        }
    }
}