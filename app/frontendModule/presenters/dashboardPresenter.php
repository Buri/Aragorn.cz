<?php

namespace frontendModule{
    class dashboardPresenter extends \BasePresenter {

        public function renderDefault() {
        }
        public function createComponentWidgets(){
            return new WidgetsControl();
        }
        
    }
    class WidgetsControl extends \Nette\Application\UI\Control
    {
        public function render(){
            $wlist = $this->getList();
            if(!$wlist)$wlist = array("news");
            $template = $this->template;
            $template->setFile(__DIR__ . '/../templates/dashboard/widgets.latte');
            $template->widgetList = $wlist;
            $template->render();
        }
        
        public function getList(){
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
            
            $xml = simplexml_load_file($wp .'widget.xml');
            $r = $xml->xpath('/widget/system/template');
            $templatesrc = $wp . $r[0];
            $this->template->setFile($templatesrc);
            
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

