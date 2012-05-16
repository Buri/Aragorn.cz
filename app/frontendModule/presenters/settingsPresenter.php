<?php

namespace frontendModule{
    use \Nette\Image;
    class settingsPresenter extends \BasePresenter {
        public function renderDefault() {
        }
        
        public static function changeIcon($d){
            $uid = \Nette\Environment::getUser()->getId();
            $file = WWW_DIR . '/../userspace/u/'.$d['file'];

            $icon = Image::fromFile($file);
            $icon->crop($d['x'], $d['y'], $d['w'], $d['h']);
            if($icon->width > $icon->height)
                $icon->resize (($icon->width > 120 ? 120 : ($icon->width < 60 ? 60 : $icon->width)), null);
            else
                $icon->resize (null, ($icon->width > 120 ? 120 : ($icon->width < 60 ? 60 : $icon->width)));
            $fid = uniqid($uid . '_') . '.png';
            $outputfile = WWW_DIR . '/../userspace/i/' . $fid;
            $icon->save($outputfile);
            \DB::users_profiles('id', $uid)->update(array('icon' => $fid));
            
            /* Unlink will be done automatically every 24h by cron */
            //unlink($d['file']);
            return "OK";
            //$icon->send(Image::PNG);
        }
    }
}
