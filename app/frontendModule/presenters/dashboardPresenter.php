<?php

namespace frontendModule{
    use \DB;
    class dashboardPresenter extends \BasePresenter {

        public function renderDefault() {
            //throw new \Exception;
        }
        public function createComponentWidgets(){
            return new \Components\WidgetListControl;
        }
        
        public static function updateWidgetList($list) {
            //$list = json_decode($list);
            $lst = array('widgets' => $list);
            DB::users_preferences()->insert_update(array('id' => \Nette\Environment::getUser()->getId()), $lst, $lst);
            return "OK";
        }
        
    }
}

