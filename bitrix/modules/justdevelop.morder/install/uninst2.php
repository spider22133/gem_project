<?
if(!check_bitrix_sessid()) return;

if($ex = $APPLICATION->GetException()){
	echo CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_UNINST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true
	));
}
else{
	echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
}
	
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG;?>">
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK");?>">
<form>
