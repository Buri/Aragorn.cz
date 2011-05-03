<?php //netteCache[01]000351a:2:{s:4:"time";s:21:"0.79299400 1304350016";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:62:"/var/www/app/frontendModule/templates/registrace/default.latte";i:2;i:1304336077;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"bb2b723 released on 2011-02-06";}}}?><?php

// source file: /var/www/app/frontendModule/templates/registrace/default.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, '5oj7rcsx07'); unset($_extends);


//
// block innercontent
//
if (!function_exists($_l->blocks['innercontent'][] = '_lbc1ac166b4f_innercontent')) { function _lbc1ac166b4f_innercontent($_l, $_args) { extract($_args)
?>
    <div class="pre eula">
<?php NLatteMacros::includeTemplate('../../../../db/eula.txt', $template->getParams(), $_l->templates['5oj7rcsx07'])->render() ?>
    </div>
    <?php $_ctrl = $control->getWidget("registerForm"); if ($_ctrl instanceof IPartiallyRenderable) $_ctrl->validateControl(); $_ctrl->render() ;
}}

//
// end of blocks
//

if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
echo NTemplateHelpers::escapeHtml($title='Registrace') ?>

<?php if (!$_l->extends) { call_user_func(reset($_l->blocks['innercontent']), $_l, get_defined_vars()); }  
if ($_l->extends) {
	ob_end_clean();
	NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
