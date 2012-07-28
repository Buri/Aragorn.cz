<?php

namespace adminModule{
    
    define('UTILITY_DIR', APP_DIR . '/../utility');
    define('FOREVER_BIN', '/usr/local/bin/forever ');

    class maitenancePresenter extends \BasePresenter {

        protected function runScript($path){
            $path .= ' 2>&1';
            $this->setView('bash');
            $this->template->path = $path;
            $this->template->shell = shell_exec('export PATH=$PATH:/usr/local/bin;' . $path);
        }
        
        public function renderDefault() {
        }
        
        
        public function actionCacheclean(){
            $this->runScript(UTILITY_DIR . '/clearcache.sh');
            $this->flashMessage($this->template->shell);
            $this->redirect('default');
        }
        
        public function actionNodestart(){
            $this->runScript(FOREVER_BIN . 'start ' . APP_DIR . '/../node/app.js');
        }
        public function actionNodestop(){
            $this->runScript(FOREVER_BIN . 'stop /var/www/node/app.js');
        }
        public function actionNoderestart(){
            $this->runScript(FOREVER_BIN . 'restart /var/www/node/app.js');
        }
        
       public function actionNodelist(){
            $this->runScript(FOREVER_BIN . 'list');
        }
    }
}