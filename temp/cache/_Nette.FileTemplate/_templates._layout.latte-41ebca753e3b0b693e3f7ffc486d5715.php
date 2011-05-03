<?php //netteCache[01]000325a:2:{s:4:"time";s:21:"0.67186800 1304449785";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:36:"/var/www/app/templates/@layout.latte";i:2;i:1304422097;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"539fdec released on 2011-04-13";}}}?><?php

// source file: /var/www/app/templates/@layout.latte

?><?php
$_l = NLatteMacros::initRuntime($template, NULL, 'ciud5dr3yt'); unset($_extends);

if (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return NLatteMacros::renderSnippets($control, $_l, get_defined_vars());
}
?><!DOCTYPE html>
<html lang="cs" xml:lang="cs" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <title><?php echo NTemplateHelpers::escapeHtml($title) ?> | Aragorn.cz</title>
        <meta name="revisit-after" content="2 hours" />
        <meta name="robots" content="index, follow" />
        <meta name="author" content="Jakub Korál :: apophis, e-mail: apophis&#64;aragorn&#46;cz" />
        <meta name="keywords" content="online,on-line,fantasy,komunita,Dračí doupě,DrD,RPG,herna,články,galerie,ORP,Open Role Play" lang="cs" />
        <meta lang="cs" name="description" content="Aktuální úvodníky které obsahují novinky. Nejnovější články a naposledy schválené hry z herny. Náhodný obrázek z galerie." />
        <meta name="verify-v1" content="Ag52d+2UaYRLwlJy5DiR+SjkptPFk0nsSdKsIKNZpeI=" />
        <meta http-equiv="X-UA-Compatible" content="chrome=1" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta name="ICBM" content="50.075342, 14.412575" />
        <link rel="alternate" title="Aragorn.cz RSS" href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/rss/" type="application/rss+xml" />
        <link rel="alternate" title="Aragorn.cz RSS Diskuze" href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/rss/diskuze/" type="application/rss+xml" />
        <link rel="alternate" title="Aragorn.cz RSS Herna" href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/rss/herna/" type="application/rss+xml" />
        <link rel="alternate" title="Aragorn.cz RSS Články" href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/rss/clanky/" type="application/rss+xml" />
        <link rel="alternate" title="Aragorn.cz RSS Galerie" href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/rss/galerie/" type="application/rss+xml" />
        <link rel="shortcut icon" type="image/x-icon" href="http://www.aragorn.cz/favicon.ico" />
        <link rel="stylesheet" media="print" type="text/css" href="<?php echo $staticPath ?>/css/printing.css" />
        <link rel="stylesheet" media="screen,projection" type="text/css" href="<?php echo $staticPath ?>/css/screen.joined.css" />
        <script type="text/javascript" charset="utf-8" src="<?php echo $staticPath ?>/js/mootools-core-1.3.1-full-nocompat-yc.js"></script>
        <script type="text/javascript" charset="utf-8" src="<?php echo $staticPath ?>/js/mootools-more.js"></script>
        <script type="text/javascript" charset="utf-8" src="<?php echo $staticPath ?>/js/socket.io.min.js"></script>
        <script type="text/javascript" charset="utf-8" src="<?php echo $staticPath ?>/js/aragorn-yc.js"></script>
        <script type="text/javascript" charset="utf-8">window.AUTHENTICATED = <?php echo NTemplateHelpers::escapeJs($isLogedIn = NEnvironment::getUser()->isLoggedIn()) ?>;</script>

        <!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="/assets/css/ie6.css" /><![endif]-->
    </head>
    <body class=''>
<?php NLatteMacros::includeTemplate('topmenu.latte', $template->getParams(), $_l->templates['ciud5dr3yt'])->render() ?>
        <div class="holder">
<?php NLatteMacros::includeTemplate('logo.latte', $template->getParams(), $_l->templates['ciud5dr3yt'])->render() ?>
            <div class="sidebar">
            </div>
            <div class="content">
<?php NLatteMacros::callBlock($_l, 'content', $template->getParams()) ?>
            </div>
        </div>
    </body>
</html>