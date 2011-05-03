<?php //netteCache[01]000351a:2:{s:4:"time";s:21:"0.80053800 1304350016";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:62:"/var/www/app/frontendModule/templates/registrace.@layout.latte";i:2;i:1304336078;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"bb2b723 released on 2011-02-06";}}}?><?php

// source file: /var/www/app/frontendModule/templates/registrace.@layout.latte

?><?php
$_l = NLatteMacros::initRuntime($template, true, 'pfstkyfqag'); unset($_extends);


//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lb273eac61bb_content')) { function _lb273eac61bb_content($_l, $_args) { extract($_args)
?>
    <h1>Registrace</h1>
<?php if (NEnvironment::getVariable('registrationEnabled')): NLatteMacros::callBlock($_l, 'innercontent', $template->getParams()) ;else: ?>
<div>Registrace je uzavřena.</div>
<div><?php echo NTemplateHelpers::escapeHtml($closeReason = NEnvironment::getVariable('registrationCloseReason')) ?></div>
<?php endif ;
}}

//
// end of blocks
//

if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
$_l->extends = '../../templates/@layout.latte' ;echo NTemplateHelpers::escapeHtml($title='Registrace') ?>

<?php
if ($_l->extends) {
	ob_end_clean();
	NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
