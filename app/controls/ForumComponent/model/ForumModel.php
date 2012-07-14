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
         * @param \NotORM $db
         */
        public function __construct(\NotORM $db, \UserAuthorizator $perms){
            $this->database = $db;
            $this->forum = new ForumModel($db, $perms);
            $this->post = new PostModel($db, $perms);
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

        /**
         *
         * @param \NotORM $db
         */
        public function __construct(\NotORM $db, \UserAuthorizator $perms){
            $this->database = $db;
            $this->authorizator = $perms;
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

            $out = array();
            foreach($this->database->forum_moderator('forumid', $this->id) as $mod)
                    $out[] = $mod['userid'];
            return $out;
        }

        public function isAllowed($operation){
            return $this->authorizator->allowed($this->permID, $operation);
        }

        public function create($name, $parentUrl = "", $prefix = ""){
            $db = $this->database;

            $parentid = 0;
            if($parentUrl == -1){
            }else{
                if($parentUrl != ""){
                    $parent = $db->forum_topic('urlfragment', $parentUrl)->fetch();
                    if(!$parent) return 'Invalid parent thread.';
                    $parentid = $parent['id'];
                }

                $this->setID($parent);
                if(!$this->isAllowed('create'))
                    return "You are not allowed to create thread here.";
            }
            
            try{
                $row = $db->forum_topic()->insert(array(
                    "name"=>$name,
                    "owner"=>  $this->authorizator->getPermissionsInstance()->getUID(),
                    "description" => "Nové forum",
                    "parent" => $parentid,
                    "urlfragment" => \Utilities::string2url($prefix.$name),
                    "created" => time()
                ));
                $this->setID($row['id']);
                return "OK";
            }
            catch(\Exception $e){
                return $e->getMessage();
            }
            return "OK";
        }

        public function delete(){
            if($this->id === null) throw new \Exception('ID of forum is not defined.');

            $db = $this->database;

            $discussions = $db->forum_topic('parent', $this->id);
            $model = new ForumControl($this->database, $this->authorizator);

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
        public function __construct(\NotORM $db, \UserAuthorizator $perms){
            $this->database = $db;
            $this->authorizator = $perms;
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

        public function isAllowed($operation){
            if($this->id === null) throw new \Exception('ID of post is not defined.');

            $db = $this->database;
            $model = new ForumControl($db, $this->authorizator);
            $uid =  $this->authorizator->getPermissionsInstance()->getUID();

            /* Load post metadata */
            $post = $db->forum_posts('id', $this->id)->fetch();
            /* Fail if post doesnt exists*/
            if($post === null) throw new Exception('Post ID invalid');

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

            $model = new ForumControl($db, $this->authorizator);
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


            $model = new ForumControl($db, $this->authorizator);
            $model->forum->setID($post['forum'])->propagateDeletePost();

            return true;
        }

    }

}
