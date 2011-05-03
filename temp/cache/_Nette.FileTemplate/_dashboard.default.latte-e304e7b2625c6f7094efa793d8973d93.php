<?php //netteCache[01]000350a:2:{s:4:"time";s:21:"0.45710700 1304422784";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:61:"/var/www/app/frontendModule/templates/dashboard/default.latte";i:2;i:1304422099;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"bb2b723 released on 2011-02-06";}}}?><?php

// source file: /var/www/app/frontendModule/templates/dashboard/default.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, '6tm9jbc4ow'); unset($_extends);


//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lbd8ca9b5f29_content')) { function _lbd8ca9b5f29_content($_l, $_args) { extract($_args)
?>
Dashboard, dořešit, jakým způsobem bude vypadat a co tu vlastně má být.
<?php
}}

//
// end of blocks
//

if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
echo NTemplateHelpers::escapeHtml($title='Úvodníky') ?>

<?php if (!$_l->extends) { call_user_func(reset($_l->blocks['content']), $_l, get_defined_vars()); }  
if ($_l->extends) {
	ob_end_clean();
	NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
