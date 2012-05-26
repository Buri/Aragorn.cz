<?php
namespace frontendModule{
    use \DB;
    class forumPresenter extends \BasePresenter {
        public function actionDefault($id = "") {
            $this->template->id = $id;
            $this->template->url = $id;
        }
        public static function getSubtopics($id){
            return DB::forum_topic('parent', $id)->where('sticky > ?', 0)->order('name');
        }
        
    }
}