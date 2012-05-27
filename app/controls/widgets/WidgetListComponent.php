<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of WidgetsComponent
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components{
    use \DB;
    class WidgetListControl extends \Nette\Application\UI\Control
    {
        public function render(){
            $user = \Nette\Environment::getUser();
            $wlist = $this->getList();
            if(!$wlist){
                $wlist = array("news", "sample");
                if($user->isLoggedIn())
                    $wlist[] = "help";
            }
            $template = $this->template;
            $template->setFile(__DIR__ . '/widgetList.latte');
            $template->widgetList = $wlist;
            $template->userLoggedIn = $user->isLoggedIn();
            $template->render();
        }

        public function getList(){
            $user = \Nette\Environment::getUser();
            if(!$user->isLoggedIn()) return false;
            $r = DB::users_preferences('id', $user->getId())->fetch();
            if($r) return json_decode($r['widgets']);
            return false;
        }
        public function createComponentWidget(){
            return new WidgetControl();
        }

    }
}