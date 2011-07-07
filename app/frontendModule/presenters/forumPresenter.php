<?php

class frontend_forumPresenter extends BasePresenter {
    public function actionDefault($id = "") {
        $nav = array();
        if(!$id){
            $data = DB::forum_topic('parent', 0)->order('name');
        }else{
            $p = DB::forum_topic('urlfragment', $id)->fetch();
            $data = DB::forum_topic('parent', $p['id'])->order('name');
            do{
                $nav[] = array("url" => $p["urlfragment"], "name"=> $p["name"]);
                $p = DB::forum_topic('id', $p['parent'])->fetch();
            }while($p["parent"]);
        }
        $nav[] = array("name"=>"Diskuze", "url"=>"");
        $this->getTemplate()->topics = $data;
        $this->getTemplate()->n = $nav;
    }
    
    public static function getSubtopics($id){
        return DB::forum_topic('parent', $id)->order('name');
    }
}