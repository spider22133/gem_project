<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_SECTION_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_SECTION_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/slider.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 30,
	"PATH" => array(
		"ID" => "web360",
		"CHILD" => array(
			"ID" => "slider",
			"NAME" => GetMessage("T_IBLOCK_DESC_CATALOG"),
			"SORT" => 30,
			"CHILD" => array(
				"ID" => "catalog_cmpx",
			),
		),
	),
);

?>