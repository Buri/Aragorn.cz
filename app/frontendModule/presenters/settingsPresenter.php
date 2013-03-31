<?php

namespace frontendModule{
    use \Nette\Image;
    use \DB;
    class settingsPresenter extends \BasePresenter {
        public function renderDefault() {
        }
        
        public function actionWidgets($options = "{}"){
            $options = json_decode($options);
            $wl = new \Components\WidgetListControl;
            $installed = $wl->getList();
            if(!$installed) $installed = array();
            $wi = array();
            foreach($installed as $widget){
                $wi[] = array("location" => $widget, 
                    'data' => simplexml_load_file(APP_DIR . '/widgets/' . $widget . '/widget.xml'),
                    'installed' => true);
            }
            $this->template->widgetsInstalled = $wi;
            $widgets = DB::widgets('state', 2); // States: 0 - dev, 1 - waiting for approval, 2 - approved
            $wa = array();
            foreach($widgets as $widget){
                $in = false;
                foreach($wi as $wiw){
                    if($wiw['location'] == $widget['location']){
                        $in = true;
                        break;
                    }
                }
                if(!$in)
                    $wa[] = array("location" => $widget["location"], 
                    "reviews" => DB::widget_reviews('widget', $widget['id']), 
                    'data' => simplexml_load_file(APP_DIR . '/widgets/' . $widget['location'] . '/widget.xml'),
                    'installed' => false);
            }
            $this->template->widgetsAvailable = $wa;
        }
        
        private static function isanimated($filename)
        {
            $filecontents=file_get_contents($filename);
            $str_loc=0;
            $count=0;
            while ($count < 2) # There is no point in continuing after we find a 2nd frame
            {
                    $where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
                    if ($where1 === FALSE)
                    {
                            break;
                    }
                    else
                    {
                            $str_loc=$where1+1;
                            $where2=strpos($filecontents,"\x00\x2C",$str_loc);
                            if ($where2 === FALSE)
                            {
                                    break;
                            }
                            else
                            {
                                    if ($where1+8 == $where2)
                                    {
                                            $count++;
                                    }
                                    $str_loc=$where2+1;
                            }
                    }
            }

            if ($count > 1)
            {
                    return(true);

            }
            else
            {
                    return(false);
            }
        }
        public static function changeIcon($d){
            $uid = \Nette\Environment::getUser()->getId();
            $file = WWW_DIR . '/../userspace/u/'.$d['file'];

            if(self::isanimated($file)){
                $fid = uniqid($uid . '_') . '.gif';
                $outputfile = WWW_DIR . '/../userspace/i/' . $fid;
                $image = new \Imagick($file);
                $image->cropImage($d['x'], $d['y'], $d['w'], $d['h']);
                $image->resizeImage(120, 120, null, 1, true); // nova velikost
                file_put_contents($outputfile, $image->getImageBlob());
            }else{
                $fid = uniqid($uid . '_') . '.png';
                $outputfile = WWW_DIR . '/../userspace/i/' . $fid;
                $icon = Image::fromFile($file);
                $icon->crop($d['x'], $d['y'], $d['w'], $d['h']);
                if($icon->width > $icon->height)
                    $icon->resize (($icon->width > 120 ? 120 : ($icon->width < 60 ? 60 : $icon->width)), null);
                else
                    $icon->resize (null, ($icon->width > 120 ? 120 : ($icon->width < 60 ? 60 : $icon->width)));
                $icon->save($outputfile);
            }
            $r = \DB::users('id', $uid)->fetch();

            if($r['icon'] != 'default.png') unlink(WWW_DIR . '/../userspace/i/' . $r['icon']);
            \DB::users('id', $uid)->update(array('icon' => $fid));
            
            /* Unlink will be done automatically every 24h by cron */
            //unlink($d['file']);
            return "OK";
            //$icon->send(Image::PNG);
        }
        
        public static function addWidget($id){
            $l = DB::users_preferences('id', \Nette\Environment::getUser()->getId())->fetch();
            $list = json_decode($l['widgets']);
            $list[] = $id;
            $l->update(array("widgets" => json_encode($list)));
            return "OK";
        }
    }
}
