<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of ForumModel
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components\Models{
    class ForumControl extends \Nette\Object{
        /**
         *
         * @var NotORM
         */
        protected $database;

        /**
         *
         * @var \Userauthorizator
         */
        protected $authorizator;

        /**
         *
         * @var ForumModel
         */
        public $forum;
        /**
         *
         * @var DiscussionModel
         */
        public $post;

        /**
         *
         * @var \Nette\Caching\IStorage
         */
        protected $storage;

        /**
         *
         * @param \NotORM $db
         */
        public function __construct(\NotORM $db, \UserAuthorizator $perms, \Nette\Caching\IStorage $storage){
            $this->database = $db;
            $this->storage = $storage;
            $this->forum = new ForumModel($db, $perms, $storage);
            $this->post = new PostModel($db, $perms, $storage);
            $this->authorizator = $perms;
        }

    }

    class ForumModel extends \Nette\Object{
        /**
         *
         * @var NotORM
         */
        protected $database;

        /**
         *
         * @var Permissions
         */
        protected $authorizator;

        protected $id = null;
        protected $permID = "";

        protected $cache;

        /**
         *
         * @param \NotORM $db
         */
        public function __construct(\NotORM $db, \UserAuthorizator $perms, \Nette\Caching\IStorage $storage){
            $this->database = $db;
            $this->authorizator = $perms;
            $this->cache = new \Nette\Caching\Cache($storage, 'forum-model');
        }

        /**
         *
         * @param int $id
         * @return \Components\Models\ForumModel
         */
        public function setID($id){
            $this->id = $id;
            $this->permID = "forum-thread-" . $id;
            return $this;
        }

        /**
         *@return array
         */
        public function getModerators(){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');

            $ckey = 'forum-moderators-' + $this->id;
            $out = $this->cache->load($ckey);
            if($out === null){
                $db = $this->database;
                $id = $this->id;
                foreach($db->forum_moderator('forumid', $id) as $mod){
                        $out[] = $mod['userid'];
                }
                $forum = $db->forum_topic('id', $id)->fetch();
                $out[] = $forum['owner'];
                if($forum['parent'] > 0){
                    $model = new ForumControl($db, $this->authorizator, $this->cache->getStorage());
                    $out = array_unique(array_merge($out, $model->forum->setID($forum['parent'])->getModerators()));
                }
                $tags = array();
                do{
                    $tags[] = "forum-moderators-" . $forum['id'];
                    $forum = $db->forum_topic('id', $forum['parent'])->fetch();
                }while($forum['parent'] > 0);
                $this->cache->save($ckey,
                        $out,
                        array('tags' => $tags)
                        );
            }
            return $out;

        }

        /**
         *  Operations:
         *   - read
         *   - write
         *   - create-topic
         *   - admin
         *
         * @param string $operation
         * @return bool
         * @throws \Exception
         */
        public function isAllowed($operation){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');

            $perm = $this->authorizator->getPermissionsInstance();

            if(!$perm->hasPermissionSet($this->permID)){
                //dump('Perm lookup');
                $db = $this->database;
                $model = new ForumControl($db, $this->authorizator, $this->cache->getStorage());
                $uid =  $perm->getUID();

                /* Load post metadata */
                $topic = $db->forum_topic('id', $this->id)->fetch();
                /* Fail if post doesnt exists*/
                if($topic === null) throw new \Exception('Forum ID invalid');

                /* Determine if user owns this post */
                if($topic['owner'] == $uid){
                    $perm->setOwner ($this->permID);
                }else{
                    /* Is user moderator? */
                    $mods = $model->forum->setID($this->id)->getModerators();
                    if(array_search("$uid", $mods) !== false)
                            $perm->setResource ($this->permID, array('_ALL'=>true), true);
                }

                if(!$perm->hasPermissionSet($this->permID)){
                    //dump('Perm not found.');
                    $perm->setResource($this->permID, array(
                        $operation => false
                    ));
                }
            }

            return $this->authorizator->allowed($this->permID, $operation);
        }

        public function create($name, $parentUrl = "", $prefix = ""){
            $db = $this->database;

            $parentid = 0;
            $prepend = "";
            if($parentUrl != -1){            
                if($parentUrl != "" && $parentUrl !== 0){
                    $parent = $db->forum_topic('urlfragment', $parentUrl)->fetch();
                    
                    if(!$parent) return 'Invalid parent thread.';
                    $parentid = $parent['id'];
                    $prnt = array("parent" => $parentid);
                    do{
                        $prnt = $db->forum_topic('id', $prnt['parent'])->fetch();
                        $prepend = $prnt['urlfragment'] . ":" . $prepend;
                    }while($prnt['parent'] > 0);
                }

                $this->setID($parentid);
                if(!$this->isAllowed('create'))
                    return "You are not allowed to create thread here.";
            }
            
            try{
                $row = $db->forum_topic()->insert(array(
                    "name"=>$name,
                    "owner"=>  $this->authorizator->getPermissionsInstance()->getUID(),
                    "description" => "Nové forum",
                    "parent" => $parentid,
                    "urlfragment" => $prepend . \Utilities::string2url($prefix.$name),
                    "created" => time()
                ));
                $this->setID($row['id']);
                return "ok";
            }
            catch(\Exception $e){
                return $e->getMessage();
            }
            return "ok";
        }

        public function delete(){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');

            $db = $this->database;

            $discussions = $db->forum_topic('parent', $this->id);
            $model = new ForumControl($this->database, $this->authorizator, $this->cache->getStorage());

            foreach($discussions as $topic){
                $tid = $topic['id'];
                //self::deleteForum($tid);
                $model->forum->setID($tid)->delete();
                $db->forum_posts('forum', $tid)->delete();
                $db->forum_moderator('forumid', $tid)->delete();
                $db->forum_visit('idforum', $tid)->delete();
                $db->forum_topic('id', $tid)->delete();
            }
            foreach($db->forum_posts('forum', $this->id) as $cpost){
                $db->forum_posts_data('id', $cpost['id'])->delete();
                $cpost->delete();
            }
            $db->forum_topic('id', $this->id)->delete();
            return "ok";

        }

        public function getViews() {
            if($this->id === null) throw new \Exception('ID of forum is not defined.');
            $row = $this->database->forum_topic('id', $this->id); //->update(array('views' => new \NotORM_Literal('+ 1')));
            return $row['views'];
        }

        /**
         *
         * @return \Components\Models\ForumModel
         * @throws \Exception
         */
        public function increaseViews() {
            if($this->id === null) throw new \Exception('ID of forum is not defined.');
            $this->database->forum_topic('id', $this->id)->update(array('views' => new \NotORM_Literal('views + 1')));
            return $this;
        }

        /**
         *
         * @return boolean
         * @throws \Exception
         */
        public function isLocked() {
            if($this->id === null) throw new \Exception('ID of forum is not defined.');
            $locked = false;

            $parent = $this->id;
            do{
                $f = $this->database->forum_topic('id', $parent)->fetch();
                if($f['options'] & \Components\ForumComponent::LOCKED){
                    $locked = true;
                    break;
                }
                $parent = $f['parent'];
            }while($parent > 0);
            return $locked;
        }

        public function setLastVisit($time = null){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');
            if($time === null) $time = time();

            $lastVisit = 0;
            if($this->isAllowed('read')){
                $db = $this->database;
                $uid = $this->authorizator->getPermissionsInstance()->getUID();
                $key = array('iduser' => $uid, 'idforum' => intval($this->id));
                $val = array('time'=>$time, 'unread'=>0);

                $r = $db->forum_visit($key)->fetch();
                $lastVisit = $r['time'];
                $db->forum_visit()->insert_update(
                        $key,
                        $val,
                        $val
                    );
                $this->increaseViews();
                return $lastVisit;
           }
        }


        public function propagateNewPost($postId){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');
            $parent = $this->id;
            $ids = array();
            do{
                $ids[] = $parent;
                $f = DB::forum_topic('id', $parent);
                $f->update(array(
                    "postcount" => new \NotORM_Literal('postcount + 1'),
                    "lastpost" => $postId
                    ));
                $f = $f->fetch();
                $this->cache->clean(array(
                    \Nette\Caching\Cache::TAGS => array('forum/'.$parent),
                ));
                $parent = intval($f['parent']);
            }while($parent != 0 && $parent != -1 );
            /* And clear top level forum cache */
            DB::forum_visit('idforum',  $ids)->update(array(
              //  "time" => new \NotORM_Literal('unix_timestamp()'),
                "unread" => new \NotORM_Literal('unread + 1')
            ));
            $this->cache->clean(array(
                    \Nette\Caching\Cache::TAGS => array('forum-root'),
                ));
        }

        public function propagateDeletePost(){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');
            
        }

    }

    class PostModel extends \Nette\Object{
        /**
         *
         * @var NotORM
         */
        protected $database;

        /**
         *
         * @var \UserAuthorizator
         */
        protected $authorizator;

        /**
         *
         * @var ForumModel
         */
        protected $forum;

        /**
         *
         * @param \NotORM $db
         */

        /**
         * @param int Id of the post
         */
        protected $id = null;

        /**
         *
         * @var string Permission identifier
         */
        protected $permID;

        /**
         *
         * @var \Nette\Caching\Cache
         */
        protected  $cache;
        public function __construct(\NotORM $db, \UserAuthorizator $perms, \Nette\Caching\IStorage $storage){
            $this->database = $db;
            $this->authorizator = $perms;
            $this->cache = new \Nette\Caching\Cache($storage, 'post-model');
        }

        /**
         *
         * @param int $id
         * @return \Components\Models\PostModel
         */
        public function setID($id){
            $this->id = $id;
            $this->permID = "forum-post-" . $id;
            return $this;
        }

        /**
         * Operations
         *  - edit
         *  - delete
         *
         * @param string $operation
         * @return bool
         * @throws \Exception
         */
        public function isAllowed($operation){
            if($this->id === null) throw new \Exception('ID of post is not defined.');

            $db = $this->database;
            $model = new ForumControl($db, $this->authorizator, $this->cache->getStorage());
            $uid =  $this->authorizator->getPermissionsInstance()->getUID();

            /* Load post metadata */
            $post = $db->forum_posts('id', $this->id)->fetch();
            /* Fail if post doesnt exists*/
            if($post === null) throw new \Exception('Post ID invalid');

            /* Determine if user owns this post */
            if($post['author'] == $uid){
                $this->authorizator->getPermissionsInstance ()->setOwner ($this->permID);
            }else{
                /* Is user moderator? */
                $mods = $model->forum->setID($post['forum'])->getModerators();
                if(array_search("$uid", $mods) !== false)
                        $this->authorizator->getPermissionsInstance ()->setResource ($this->permID, array('_ALL'=>true), true);
            }
            return $this->authorizator->allowed($this->permID, $operation);
        }

        public function add(\Nette\ArrayHash $post){
            if($this->id === null) throw new \Exception('ID of post is not defined.');

            $model = new ForumControl($db, $this->authorizator, $this->cache->getStorage());
            $uid =  $this->authorizator->getPermissionsInstance()->getUID();

        }

        public function edit(\Nette\ArrayHash $post){
            if($this->id === null) throw new \Exception('ID of post is not defined.');

            if(!$this->isAllowed('edit')){
                return false;
            }

            $db = $this->database;
            $postdb = $db->forum_posts('id', $this->id)->fetch();
            /* Fail if post doesnt exists*/
            if($postdb === false) throw new Exception('Post ID invalid');

            $data = $post['post'];
            unset($post['post']);
            //$post['forumid'] = $post['discussionid'];
            unset($post['discussionid']);
            unset($post['action']);
            unset($post['id']);

            $out = array();
            foreach($post as $k => $v)
                $out[$k] = $v;

            $postdb->update($out);
            $db->forum_posts_data('id', $this->id)->update(array('post' => $data));
            return true;
        }

        public function delete(){
            if($this->id === null) throw new \Exception('ID of post is not defined.');

            if(!$this->isAllowed('delete')){
                return false;
            }

            $db = $this->database;

            $post = $db->forum_posts('id', $this->id)->fetch();
            /* Fail if post doesnt exists*/
            if($post === false) throw new Exception('Post ID invalid');

            /* Update post count for all those who havent seen this post yet */
            $db->forum_visit()->where('idforum = ? AND time() < ? AND iduser <> ?',
                    array($post['forum'],
                        $post['time'],
                        $this->authorizator->getPermissionsInstance()->getUID()
                    ))
                    ->update(array('unread'=>new \NotORM_Literal("unread - 1")));
            $db->forum_visit('unread < ?', 0)->update(array('unread'=>0));

            $post->delete();
            $db->forum_posts_data('id', $this->id)->delete();


            $model = new ForumControl($db, $this->authorizator, $this->cache->getStorage());
            $model->forum->setID($post['forum'])->propagateDeletePost();

            return true;
        }

    }

}
