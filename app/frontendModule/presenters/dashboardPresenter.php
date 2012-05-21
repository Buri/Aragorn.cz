<?php

namespace frontendModule{
    use \DB;
    class dashboardPresenter extends \BasePresenter {

        public function renderDefault() {
            //throw new \Exception;
        }
        public function createComponentWidgets(){
            return new WidgetsControl();
        }
        
        public static function updateWidgetList($list) {
            //$list = json_decode($list);
            $lst = array('widgets' => $list);
            DB::users_preferences()->insert_update(array('id' => \Nette\Environment::getUser()->getId()), $lst, $lst);
            return "OK";
        }
        
    }
    class WidgetsControl extends \Nette\Application\UI\Control
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
            $template->setFile(__DIR__ . '/../templates/dashboard/widgets.latte');
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
    class WidgetControl extends \Nette\Application\UI\Control
    {
        public function render($name){
            $name = strtolower($name);
            $wp = __DIR__ . '/../../widgets/'. $name . '/';
            
            /* Load up widget */
            $xml = simplexml_load_file($wp .'widget.xml');
            
            /* Prepare template */
            $r = $xml->xpath('/widget/system/template');
            $templatesrc = $wp . $r[0];
            $this->template->setFile(APP_DIR . '/frontendModule/templates/dashboard/widget.latte');
            $this->template->widgetTemplate = $templatesrc;
            
            $cap_r = $xml->xpath('/widget/info/id');
            $this->template->widgetId = $cap_r[0];
            
            $cap_r = $xml->xpath('/widget/info/title');
            $this->template->widgetTitle = $cap_r[0];
            
            
            /* Prepare sandbox */
            $r = $xml->xpath('/widget/system/script');
            $scriptsrc = $wp . $r[0];
            if(!$this->exec($scriptsrc, $wp, __DIR__ . "/../../../db/$name/")) return;
            
            $this->template->render();
        }   
        private function exec($src, $basepath, $datapath = '../../../db/'){
            if(!file_exists($src))
                return false;
            try{
                require_once($src);
                return true;
            }
            catch(\Exception $e){
                return false;
            }
        }
    }
}

