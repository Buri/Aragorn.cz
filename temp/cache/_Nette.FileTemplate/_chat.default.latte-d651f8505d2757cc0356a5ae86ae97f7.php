<?php //netteCache[01]000345a:2:{s:4:"time";s:21:"0.94832900 1304424043";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:56:"/var/www/app/frontendModule/templates/chat/default.latte";i:2;i:1304422099;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"bb2b723 released on 2011-02-06";}}}?><?php

// source file: /var/www/app/frontendModule/templates/chat/default.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, '2rqdxq906r'); unset($_extends);


//
// block innercontent
//
if (!function_exists($_l->blocks['innercontent'][] = '_lb4a236c9e06_innercontent')) { function _lb4a236c9e06_innercontent($_l, $_args) { extract($_args)
;if (isset($message)): echo NTemplateHelpers::escapeHtml($message) ;endif ?>

<div>
<?php foreach ($iterator = $_l->its[] = new NSmartCachingIterator($chatrooms) as $room): ?>
    <div>
        <?php if (NEnvironment::getUser()->isLoggedIn()): ?><a href="<?php echo NTemplateHelpers::escapeHtml($control->link("chat:roomenter", array($room['id']))) ?>"<?php if ($room['password']): ?> title="Uzamčená místnost - nutné heslo" onclick="var p = prompt('Zadejte heslo:'); if(!p) return false; this.href += p.sha1();" class="lock"<?php endif ?>>
        <?php echo NTemplateHelpers::escapeHtml($room["name"]) ?></a><?php else: ?><span<?php if ($room['password']): ?> title="Uzamčená místnost - nutné heslo" class="lock"<?php endif ?>><?php echo NTemplateHelpers::escapeHtml($room["name"]) ?></span><?php endif ?>

        - <?php echo NTemplateHelpers::escapeHtml($room["description"]) ?> - 
<?php foreach ($iterator = $_l->its[] = new NSmartCachingIterator($room["occupants"]) as $occupant): ?>
            <?php echo NTemplateHelpers::escapeHtml($occupant) ?>, 
<?php endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
    </div>
<?php endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
</div><?php
}}

//
// end of blocks
//

if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
if (!$_l->extends) { call_user_func(reset($_l->blocks['innercontent']), $_l, get_defined_vars()); }  
if ($_l->extends) {
	ob_end_clean();
	NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
