<?php //netteCache[01]000336a:2:{s:4:"time";s:21:"0.25789700 1304450132";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:47:"/var/www/app/ajaxModule/templates/@layout.latte";i:2;i:1304422097;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"539fdec released on 2011-04-13";}}}?><?php

// source file: /var/www/app/ajaxModule/templates/@layout.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, 'qlyhwb1oy8'); unset($_extends);

if (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
?>
<?xml version="1.0" encoding="utf-8"?>
<ajax><?php echo $data ?></ajax>