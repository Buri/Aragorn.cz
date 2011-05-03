<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Loaders
 */



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @author     David Grudl
 */
class NNetteLoader extends NAutoLoader
{
	/** @var NNetteLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'argumentoutofrangeexception' => '/common/exceptions.php',
		'deprecatedexception' => '/common/exceptions.php',
		'directorynotfoundexception' => '/common/exceptions.php',
		'fatalerrorexception' => '/common/exceptions.php',
		'filenotfoundexception' => '/common/exceptions.php',
		'iannotation' => '/Reflection/IAnnotation.php',
		'iauthenticator' => '/Security/IAuthenticator.php',
		'iauthorizator' => '/Security/IAuthorizator.php',
		'icachejournal' => '/Caching/Storages/IJournal.php',
		'icachestorage' => '/Caching/IStorage.php',
		'icomponent' => '/ComponentModel/IComponent.php',
		'icomponentcontainer' => '/ComponentModel/IContainer.php',
		'iconfigadapter' => '/Config/IAdapter.php',
		'icontext' => '/DI/IContext.php',
		'idebugpanel' => '/Diagnostics/IPanel.php',
		'ifiletemplate' => '/Templating/IFileTemplate.php',
		'iformcontrol' => '/Forms/IControl.php',
		'iformrenderer' => '/Forms/IFormRenderer.php',
		'ifreezable' => '/common/IFreezable.php',
		'ihttprequest' => '/Http/IRequest.php',
		'ihttpresponse' => '/Http/IResponse.php',
		'iidentity' => '/Security/IIdentity.php',
		'imailer' => '/Mail/IMailer.php',
		'invalidstateexception' => '/common/exceptions.php',
		'ioexception' => '/common/exceptions.php',
		'ipartiallyrenderable' => '/Application/UI/IPartiallyRenderable.php',
		'ipresenter' => '/Application/IPresenter.php',
		'ipresenterfactory' => '/Application/IPresenterFactory.php',
		'ipresenterresponse' => '/Application/IResponse.php',
		'irenderable' => '/Application/UI/IRenderable.php',
		'iresource' => '/Security/IResource.php',
		'irole' => '/Security/IRole.php',
		'irouter' => '/Application/IRouter.php',
		'isessionstorage' => '/Http/ISessionStorage.php',
		'isignalreceiver' => '/Application/UI/ISignalReceiver.php',
		'istatepersistent' => '/Application/UI/IStatePersistent.php',
		'isubmittercontrol' => '/Forms/ISubmitterControl.php',
		'isupplementaldriver' => '/Database/ISupplementalDriver.php',
		'itemplate' => '/Templating/ITemplate.php',
		'itranslator' => '/Localization/ITranslator.php',
		'iuser' => '/Http/IUser.php',
		'memberaccessexception' => '/common/exceptions.php',
		'nabortexception' => '/Application/exceptions.php',
		'nambiguousserviceexception' => '/DI/AmbiguousServiceException.php',
		'nannotation' => '/Reflection/Annotation.php',
		'nannotationsparser' => '/Reflection/AnnotationsParser.php',
		'nappform' => '/Application/UI/Form.php',
		'napplication' => '/Application/Application.php',
		'napplicationexception' => '/Application/exceptions.php',
		'narrayhash' => '/common/ArrayHash.php',
		'narraylist' => '/common/ArrayList.php',
		'narraytools' => '/common/ArrayUtils.php',
		'nauthenticationexception' => '/Security/AuthenticationException.php',
		'nautoloader' => '/Loaders/AutoLoader.php',
		'nbadrequestexception' => '/Application/exceptions.php',
		'nbadsignalexception' => '/Application/UI/BadSignalException.php',
		'nbutton' => '/Forms/Controls/Button.php',
		'ncache' => '/Caching/Cache.php',
		'ncachinghelper' => '/Caching/OutputHelper.php',
		'ncallback' => '/common/Callback.php',
		'ncallbackfilteriterator' => '/Iterators/Filter.php',
		'ncheckbox' => '/Forms/Controls/Checkbox.php',
		'nclassreflection' => '/Reflection/ClassType.php',
		'nclirouter' => '/Application/Routers/CliRouter.php',
		'nclosurefix' => '/common/Framework.php',
		'ncomponent' => '/ComponentModel/Component.php',
		'ncomponentcontainer' => '/ComponentModel/Container.php',
		'nconfig' => '/Config/Config.php',
		'nconfigadapterini' => '/Config/IniAdapter.php',
		'nconfigadapterneon' => '/Config/NeonAdapter.php',
		'nconfigurator' => '/DI/Configurator.php',
		'nconnection' => '/Database/Connection.php',
		'ncontext' => '/DI/Context.php',
		'ncontrol' => '/Application/UI/Control.php',
		'ncriticalsection' => '/Utils/CriticalSection.php',
		'ndatabasepanel' => '/Database/Diagnostics/ConnectionPanel.php',
		'ndatabasereflection' => '/Database/Reflection/DatabaseReflection.php',
		'ndatetime53' => '/common/DateTime.php',
		'ndebug' => '/Diagnostics/Debugger.php',
		'ndebughelpers' => '/Diagnostics/Helpers.php',
		'ndebugpanel' => '/Diagnostics/Panel.php',
		'ndefaultformrenderer' => '/Forms/Rendering/DefaultFormRenderer.php',
		'ndownloadresponse' => '/Application/Responses/FileResponse.php',
		'ndummystorage' => '/Caching/Storages/DevNullStorage.php',
		'nenvironment' => '/common/Environment.php',
		'nextensionreflection' => '/Reflection/Extension.php',
		'nfilejournal' => '/Caching/Storages/FileJournal.php',
		'nfilestorage' => '/Caching/Storages/FileStorage.php',
		'nfiletemplate' => '/Templating/FileTemplate.php',
		'nfileupload' => '/Forms/Controls/UploadControl.php',
		'nfinder' => '/Utils/Finder.php',
		'nforbiddenrequestexception' => '/Application/exceptions.php',
		'nform' => '/Forms/Form.php',
		'nformcontainer' => '/Forms/Container.php',
		'nformcontrol' => '/Forms/Controls/BaseControl.php',
		'nformgroup' => '/Forms/ControlGroup.php',
		'nforwardingresponse' => '/Application/Responses/ForwardResponse.php',
		'nframework' => '/common/Framework.php',
		'nfreezableobject' => '/common/FreezableObject.php',
		'nfunctionreflection' => '/Reflection/GlobalFunction.php',
		'ngenericrecursiveiterator' => '/Iterators/Recursor.php',
		'ngroupedtableselection' => '/Database/Table/GroupedSelection.php',
		'nhiddenfield' => '/Forms/Controls/HiddenField.php',
		'nhtml' => '/Utils/Html.php',
		'nhttpcontext' => '/Http/Context.php',
		'nhttprequest' => '/Http/Request.php',
		'nhttprequestfactory' => '/Http/RequestFactory.php',
		'nhttpresponse' => '/Http/Response.php',
		'nhttpuploadedfile' => '/Http/FileUpload.php',
		'nidentity' => '/Security/Identity.php',
		'nimage' => '/common/Image.php',
		'nimagebutton' => '/Forms/Controls/ImageButton.php',
		'ninstancefilteriterator' => '/Iterators/InstanceFilter.php',
		'ninvalidlinkexception' => '/Application/UI/InvalidLinkException.php',
		'ninvalidpresenterexception' => '/Application/exceptions.php',
		'njson' => '/Utils/Json.php',
		'njsonexception' => '/Utils/Json.php',
		'njsonresponse' => '/Application/Responses/JsonResponse.php',
		'nlatteexception' => '/Latte/ParseException.php',
		'nlattefilter' => '/Latte/Engine.php',
		'nlattemacros' => '/Latte/DefaultMacros.php',
		'nlimitedscope' => '/Utils/LimitedScope.php',
		'nlink' => '/Application/UI/Link.php',
		'nmail' => '/Mail/Message.php',
		'nmailmimepart' => '/Mail/MimePart.php',
		'nmapiterator' => '/Iterators/Mapper.php',
		'nmemcachedstorage' => '/Caching/Storages/MemcachedStorage.php',
		'nmemorystorage' => '/Caching/Storages/MemoryStorage.php',
		'nmethodreflection' => '/Reflection/Method.php',
		'nmimetypedetector' => '/Utils/MimeTypeDetector.php',
		'nmultirouter' => '/Application/Routers/RouteList.php',
		'nmultiselectbox' => '/Forms/Controls/MultiSelectBox.php',
		'nneon' => '/Utils/Neon.php',
		'nneonexception' => '/Utils/Neon.php',
		'nnetteloader' => '/Loaders/NetteLoader.php',
		'nobject' => '/common/Object.php',
		'nobjectmixin' => '/common/ObjectMixin.php',
		'notimplementedexception' => '/common/exceptions.php',
		'notsupportedexception' => '/common/exceptions.php',
		'npaginator' => '/Utils/Paginator.php',
		'nparameterreflection' => '/Reflection/Parameter.php',
		'npdomssqldriver' => '/Database/Drivers/MsSqlDriver.php',
		'npdomysqldriver' => '/Database/Drivers/MySqlDriver.php',
		'npdoocidriver' => '/Database/Drivers/OciDriver.php',
		'npdoodbcdriver' => '/Database/Drivers/OdbcDriver.php',
		'npdopgsqldriver' => '/Database/Drivers/PgSqlDriver.php',
		'npdosqlite2driver' => '/Database/Drivers/Sqlite2Driver.php',
		'npdosqlitedriver' => '/Database/Drivers/SqliteDriver.php',
		'npermission' => '/Security/Permission.php',
		'npresenter' => '/Application/UI/Presenter.php',
		'npresentercomponent' => '/Application/UI/PresenterComponent.php',
		'npresentercomponentreflection' => '/Application/UI/PresenterComponentReflection.php',
		'npresenterfactory' => '/Application/PresenterFactory.php',
		'npresenterrequest' => '/Application/Request.php',
		'npropertyreflection' => '/Reflection/Property.php',
		'nradiolist' => '/Forms/Controls/RadioList.php',
		'nrecursivecallbackfilteriterator' => '/Iterators/RecursiveFilter.php',
		'nrecursivecomponentiterator' => '/ComponentModel/RecursiveComponentIterator.php',
		'nredirectingresponse' => '/Application/Responses/RedirectResponse.php',
		'nregexpexception' => '/common/StringUtils.php',
		'nrenderresponse' => '/Application/Responses/TextResponse.php',
		'nrobotloader' => '/Loaders/RobotLoader.php',
		'nroute' => '/Application/Routers/Route.php',
		'nroutingdebugger' => '/Application/Diagnostics/RoutingPanel.php',
		'nrow' => '/Database/Row.php',
		'nrule' => '/Forms/Rule.php',
		'nrules' => '/Forms/Rules.php',
		'nsafestream' => '/Utils/SafeStream.php',
		'nselectbox' => '/Forms/Controls/SelectBox.php',
		'nsendmailmailer' => '/Mail/SendmailMailer.php',
		'nsession' => '/Http/Session.php',
		'nsessionnamespace' => '/Http/SessionNamespace.php',
		'nsimpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'nsimplerouter' => '/Application/Routers/SimpleRouter.php',
		'nsmartcachingiterator' => '/Iterators/CachingIterator.php',
		'nsmtpexception' => '/Mail/SmtpMailer.php',
		'nsmtpmailer' => '/Mail/SmtpMailer.php',
		'nsqlliteral' => '/Database/SqlLiteral.php',
		'nsqlpreprocessor' => '/Database/SqlPreprocessor.php',
		'nstatement' => '/Database/Statement.php',
		'nstring' => '/common/StringUtils.php',
		'nsubmitbutton' => '/Forms/Controls/SubmitButton.php',
		'ntablerow' => '/Database/Table/ActiveRow.php',
		'ntableselection' => '/Database/Table/Selection.php',
		'ntemplate' => '/Templating/Template.php',
		'ntemplatecachestorage' => '/Templating/PhpFileStorage.php',
		'ntemplateexception' => '/Templating/FilterException.php',
		'ntemplatehelpers' => '/Templating/DefaultHelpers.php',
		'ntextarea' => '/Forms/Controls/TextArea.php',
		'ntextbase' => '/Forms/Controls/TextBase.php',
		'ntextinput' => '/Forms/Controls/TextInput.php',
		'ntokenizer' => '/Utils/Tokenizer.php',
		'ntokenizerexception' => '/Utils/Tokenizer.php',
		'nuri' => '/Http/Url.php',
		'nuriscript' => '/Http/UrlScript.php',
		'nuser' => '/Http/User.php',
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NNetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			NLimitedScope::load(NETTE_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}
