<?php //netteCache[01]000346a:2:{s:4:"time";s:21:"0.25268000 1304423545";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:57:"/var/www/app/ajaxModule/templates/Ajax/testidentity.latte";i:2;i:1304422098;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"bb2b723 released on 2011-02-06";}}}?><?php

// source file: /var/www/app/ajaxModule/templates/Ajax/testidentity.latte

?><?php
$_l = NLatteMacros::initRuntime($template, true, 'xud5ygtcut'); unset($_extends);

if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
$_l->extends = '../@layout.latte' ;
if ($_l->extends) {
	ob_end_clean();
	NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
