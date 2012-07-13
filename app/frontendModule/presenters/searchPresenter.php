<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of SearchPresenter
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace frontendModule{
    class searchPresenter extends \BasePresenter{

        public function actionDefault($q = null) {
            $this->template->q = $q;
            if($q !== null){
                $db = $this->context->database;
                $results = $db->forum_posts_data()
                        ->where(
                                'MATCH(post) AGAINST (?)', $q
                            )
                        ->order(new \NotORM_Literal('MATCH(post) AGAINST (?) DESC', $q));
                $results->union(
                        $db->users('username LIKE ? ', $q)
                        );
                $this->template->results = $results;
            }
        }
    }
}
