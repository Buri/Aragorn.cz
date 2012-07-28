<?php
namespace frontendModule{
    use \Nette\Application\UI\Form;
    class calendarPresenter extends \BasePresenter {

        protected $filters = array();
        protected $id = null;

        public function actionDefault() {
            $db = $this->context->database;
            $time = time();
            $this->template->time = $time;
            $f = array(
                "public" => 1,
                "running" => 1,
                "my" => 1,
                "attending" => 1,
                "order" => "begin"
            );

            /*$f1 = array();
            $f2 = array();
            $f3 = array();
            if($f['public']){
                $f1['public'] = 1;
            }
            if($f['running']){

            }*/
            $attending = $db->calendar(array(
                        'id' => $db->calendar_attendant(array(
                            'iduser', $this->getUser()->getId(),
                            'rsvp' => array('y', 'm')
                        ))->select('idaction'),
                        "end > ?" => time()
                    )
                );
            $owned = $db->calendar(array(
                                    'owner' => $this->getUser()->getId(),
                                    "end > ?" => time()
                                )
                            );//->union($attending);
            $actions = $db->calendar(array(
                    "public" => 1,
                    "end > ?" => $time
                    ))->union($owned
            )->union($attending)->group('id')->order('begin');
            $this->template->actions = $actions;
        }

        public function actionView($id){
            $db = $this->context->database;
            $this->id = $id;
            $this->template->id = $id;

            $isAttendant = $db->calendar_attendant(array(
                "idaction" => $id,
                "iduser" => $this->getUser()->getId()
            ));
            $action = $db->calendar('id', $id)->fetch();

            if(!$action['public'] && !$isAttendant->count() && $action['owner'] != $this->getUser()->getId()){
                $this->flashMessage('Pokoušíte se zobrazit soukromou akci, na kterou vás nikdo nepozval.');
                $this->redirect('default');
            }
            $repeats = array(''=>'', 'D'=>'Denně', 'W'=>'Týdně','M'=>'Měsíčně','Y'=>'Ročně');
            $action['repeating'] = $repeats[$action['repeating']];
            $this->template->action = $action;
            $this->template->attendants = $db->calendar_attendant('idaction', $id)->order('rsvp');
            $this->template->moderators = $db->calendar_attendant(array(
                'idaction' => $id,
                'moderator' => 1
                ));
        }

        public function createComponentAddAction(){
            $form = new Form;
            $form->addGroup('Obecné informace');
            $form->addText('name', 'Název')
                    ->addRule(Form::FILLED, 'Akce musí mít název.')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 250);
            $form->addCheckbox('public', 'Veřejá akce');
            $form->addText('begin', 'Začátek')
                    ->setType('datetime-local')
                    ->addRule(Form::FILLED, 'Vyplňte začátek akce');
            $form->addText('end', 'Konec')
                    ->setType('datetime-local')
                    ->addRule(Form::FILLED, 'Vyplňte konec akce');
            $form->addTextArea('description', 'Popis')
                    ->addRule(Form::FILLED, 'Vyplňte popis.')
                    ->addRule(Form::MIN_LENGTH, 'Popis musí mít alespoň %d znaků', 30);

            $form->addGroup('Rozšiřující informace');
            $form->addText('location', 'Lokace')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka pole je %d znaků.', 250);
            $form->addText('capacity', 'Počet účastníků')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka pole je %d znaků.', 250);
            $form->addText('limits', 'Omezení')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka pole je %d znaků.', 250);
            $form->addText('price', 'Cena')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka pole je %d znaků.', 250);
            $form->addRadioList('repeating', 'Opakování', array(
                '' => 'Neopakovat',
                //'H' => 'Každou hodinu',
                'D' => 'Denně',
                'T' => 'Týdně',
                'M' => 'Měsíčně',
                'Y' => 'Ročně',
            ))->setValue('');

            $form->addGroup('Přidat akci');
            $form->addSubmit('send', 'Přidat akci');

            $form->onSuccess[] = callback($this, 'addAction');
            return $form;
        }

        public function addAction(Form $form){
            if($this->getUser()->isLoggedIn()){
                $db = $this->context->database;
                /*$db->debug = function($query, $params){
                    echo($query);
                    return true;
                };*/
                $v = $form->getValues(true);

                $v['owner'] = $this->getUser()->getId();
                //$v['public'] = $v['public'] ? '1' : '0';
                $v['begin'] = strtotime($v['begin']);
                $v['end'] = strtotime($v['end']);

                if($v['begin'] > $v['end']){
                    $form->addError('Začátek musí předcházet konci!');
                    return false;
                }

                $row = $db->calendar()->insert($v);
                $forum = array(
                        "name" => $v['name'],
                        "owner" => $this->getUser()->getId(),
                        "description" => "Diskuze k akci " . $v['name'],
                        "parent" => -1,
                        "urlfragment" => "calendar-forum-".$row['id'],
                        "created" => time()
                    );
                /*dump($forum);
                $db->debug = function($q,$p){
                    echo($q);
                    return true;
                };*/
                $dis = $db->forum_topic()->insert($forum);
                /*dump($dis);
                exit;*/
                if($row){
                    $this->flashMessage('Akce byla přidána do kalendáře.');
                    $this->redirect('default');
                }else{
                    $this->flashMessage('Při zápisu do databáze nastala chyba.');
                }
            }else{
                $this->flashMessage('Pro přidání akce musíte být přihlášený');
                $this->redirect('default');
            }
        }

        public function actionRsvp($id, $rsvp) {
            if(!$this->getUser()->isLoggedIn()){
                $this->flashMessage('Nejste přihlášen.');
                $this->redirect('default');
            }
            if(!array_search($rsvp, array("", "y", "n", "m"))){
                $this->flashMessage('Neplatná volba.');
                $this->redirect('default');
            }
            $uid = $this->getUser()->getId();
            $this->context->database->calendar_attendant()->insert_update(
                    array('iduser'=>$uid, 'idaction'=>$id),
                    array('rsvp' => $rsvp),
                    array('rsvp'=>$rsvp)
            );
            $this->flashMessage('Vaše účast na akci byla změněna.');
            /*$this->actionView($id);
            $this->setView('view');*/
            if($this->isAjax()){
                $this->template->forceReload = true;
            }else{
                $this->redirect('view', $id);
            }
        }

        /*public function createComponentForum(){

        }*/
    }
}