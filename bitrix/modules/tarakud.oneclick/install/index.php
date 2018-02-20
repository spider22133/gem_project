<?
IncludeModuleLangFile(__FILE__);
Class tarakud_oneclick extends CModule
{
	const MODULE_ID = 'tarakud.oneclick';
	var $MODULE_ID = 'tarakud.oneclick'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("TARAKUD_ONECLICK_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("TARAKUD_ONECLICK_DESC");
		$this->PARTNER_NAME = GetMessage("TARAKUD_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("TARAKUD_PARTNER_URI");
	}

	function checkModules()
	{
		if (!CModule::IncludeModule('iblock'))
			throw new SystemException("Module 'iblock' is not install.");
	}
	
	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function UnInstallFiles()
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		return true;
	}
	
	function InstallEvents()
	{	
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/events.php");
		
		return true;
	}

	function UnInstallEvents()
	{
		/*global $DB;

		$eventType = new CEventType;
		$eventType->Delete("SALE_NEW_ORDER");

		$eventM = new CEventMessage;
		$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", array("EVENT_NAME" => "SALE_NEW_ORDER"));
		while($arEvent = $dbEvent->Fetch())
			$eventM->Delete($arEvent["ID"]);*/

		return true;
	}
	
	function DoInstall()
	{
		global $APPLICATION;
		
		$this->checkModules();
		$this->InstallFiles();
		$this->InstallEvents();
		
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		
		$this->UnInstallFiles();
		$this->UnInstallEvents();
	}
}
?>