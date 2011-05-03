<?php //netteCache[01]000325a:2:{s:4:"time";s:21:"0.70163100 1304449785";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:36:"/var/www/app/templates/topmenu.latte";i:2;i:1304422097;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"539fdec released on 2011-04-13";}}}?><?php

// source file: /var/www/app/templates/topmenu.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, 'rsoc1kdbfa'); unset($_extends);

if (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
?>
        <div class="menu main">
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("chat:")) ?>" class="middle">Chat</a> |
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("napoveda:")) ?>" class="middle">Nápověda</a> |
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("uzivatele:")) ?>" class="middle">Uživatelé</a> |
<?php if ($user->isLoggedIn()): ?>
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("nastaveni:")) ?>" class="middle">Nastavení</a> |
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("posta:")) ?>" class="middle">Pošta</a> |
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("logout")) ?>" class="middle">Odhlásit</a>
            <?php if (true): ?>             | <a href="<?php echo NTemplateHelpers::escapeHtml($control->link(":admin:dashboard:")) ?>" class="middle">RS</a>
<?php endif ;else: ?>
            <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("registrace:")) ?>" class="middle">Registrace</a> |
<?php $_ctrl = $control->getWidget("logInForm"); if ($_ctrl instanceof IPartiallyRenderable) $_ctrl->validateControl(); $_ctrl->render() ;endif ?>
        </div>
        <div id="buddylist">
            Seznam buddíků
        </div>