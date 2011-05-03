<?php //netteCache[01]000322a:2:{s:4:"time";s:21:"0.80784200 1304449916";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:33:"/var/www/app/templates/logo.latte";i:2;i:1304422097;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"539fdec released on 2011-04-13";}}}?><?php

// source file: /var/www/app/templates/logo.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, 'd7dpqxfkjy'); unset($_extends);

if (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
?>
            <div class="logo menu">
                <div class="left">
                    <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("clanky:")) ?>" class="top">Články</a>
                    <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("galerie:")) ?>" class="bottom">Galerie</a>
                </div>

                <div class="right">
                    <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("herna:")) ?>" class="top">Herna</a>
                    <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("diskuze:")) ?>" class="bottom">Diskuze</a>
                </div>

                <a href="<?php echo NTemplateHelpers::escapeHtml($control->link("dashboard:")) ?>" class="middle">Aragorn.cz</a>

            </div>
