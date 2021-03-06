<?php

namespace frontendModule{
    use \DB;
    class usersPresenter extends \BasePresenter {
        public function actionDefault($filter = array()) {
            $filter = array(
                "page" => 1,
                "perpage" => 3,
                "user" => "%",
                "sort" => "users.username ASC",
                "fulldump" => true
            ) + $filter;
            
            $this->template->sortabletable = $filter['fulldump'];
            
            $users = $this->context->database->users()->order($filter['sort'])->where('username LIKE ?', $filter['user']);
            $users = $filter['fulldump'] ? $users : $users->limit(($filter['page']-1).",".$filter["perpage"]);
            $users->select('users.*');
            $this->template->users = $users;
        }
        
        public function getUserProfile($id){
            return $this->context->database->users('id', $id)->fetch();
        }
        
        public function actionProfile($id){
            $this->template->id = $id;
        }

        public function actionView($id) {
            $this->template->id = $id;
        }
    }
}
