<?
IncludeModuleLangFile(__FILE__);
?>
<form action="<?=$APPLICATION->GetCurPage();?>" name="form1">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>">
	<input type="hidden" name="id" value="justdevelop.morder">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<br>
		<?=GetMessage("JUSTDEVELOP_WARN_CHECK")?><input type="submit" name="inst" value="<?=GetMessage("MOD_INSTALL");?>">
</form>