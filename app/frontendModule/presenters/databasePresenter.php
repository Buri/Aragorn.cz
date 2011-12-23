<?php

namespace frontendModule{
    class databasePresenter extends \BasePresenter {

        public function renderDefault() {
            \Nette\Diagnostics\Debugger::dump(new \Permissions());
            exit;
        }
    }
}