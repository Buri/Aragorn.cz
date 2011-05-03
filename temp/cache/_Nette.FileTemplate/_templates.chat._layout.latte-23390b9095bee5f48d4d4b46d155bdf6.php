<?php //netteCache[01]000345a:2:{s:4:"time";s:21:"0.97233400 1304424043";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:56:"/var/www/app/frontendModule/templates/chat.@layout.latte";i:2;i:1304422099;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"bb2b723 released on 2011-02-06";}}}?><?php

// source file: /var/www/app/frontendModule/templates/chat.@layout.latte

?><?php
$_l = NLatteMacros::initRuntime($template, true, 'bw4co5b6gl'); unset($_extends);


//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lbc5745984ed_content')) { function _lbc5745984ed_content($_l, $_args) { extract($_args)
;NLatteMacros::callBlock($_l, 'innercontent', $template->getParams()) ;
}}

//
// end of blocks
//

if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
$_l->extends = '../../templates/@layout.latte' ;echo NTemplateHelpers::escapeHtml($title='Chat') ?>

<?php
if ($_l->extends) {
	ob_end_clean();
	NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
