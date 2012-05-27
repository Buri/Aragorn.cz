<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of WidgetControl
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components{
    class WidgetControl extends \Nette\Application\UI\Control
    {
        public function render($name){
            $name = strtolower($name);
            $wp = APP_DIR . '/widgets/'. $name . '/';

            /* Load up widget */
            $xml = simplexml_load_file($wp .'widget.xml');

            /* Prepare template */
            $r = $xml->xpath('/widget/system/template');
            $templatesrc = $wp . $r[0];
            $this->template->setFile(__DIR__ . '/widget.latte');
            $this->template->widgetTemplate = $templatesrc;

            $cap_r = $xml->xpath('/widget/info/id');
            $this->template->widgetId = $cap_r[0];

            $cap_r = $xml->xpath('/widget/info/title');
            $this->template->widgetTitle = $cap_r[0];


            /* Prepare sandbox */
            $r = $xml->xpath('/widget/system/script');
            $scriptsrc = $wp . $r[0];
            if(!$this->exec($scriptsrc, $wp, __DIR__ . "/../../../db/widgets/$name/")) return;

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