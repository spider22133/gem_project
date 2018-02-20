<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();$this->setFrameMode(true);?><?if ($arResult["HIDE_BUTTON"] != "Y"):?>	<a class="btn btn-fast-order js-popup-btn-form" title="<?=GetMessage("SALE_BTN")?>" href="#popup-window<?=$arResult["UNIQUE_CODE"]?>"><?=GetMessage("SALE_BTN")?></a><?endif;?><div style="display: none;">	<div id="popup-window<?=$arResult["UNIQUE_CODE"]?>" class="popup-hidden-window">		<form action="<?=$APPLICATION->GetCurPage()?>" name="popup-form<?=$arResult["UNIQUE_CODE"]?>" id="popup-form<?=$arResult["UNIQUE_CODE"]?>" method="post" role="form">			<input type="hidden" name="ajax" value="Y">			<input type="hidden" name="ajax_params" value='<?=$arResult["PARAMS"]?>'>			<input type="hidden" name="ajax_send" id="send-ajax<?=$arResult["UNIQUE_CODE"]?>" value="N">			<input type="hidden" name="product-id" id="js-product-id" value="<?=$arResult["ELEMENTS"]["ID"]?>">			<input type="hidden" name="url" id="js-url" value="<?=$arResult["AJAX_URL"]?>">			<input type="hidden" name="site_id" value="<?=SITE_ID?>">			<div id="popup-error<?=$arResult["UNIQUE_CODE"]?>"></div>			<div class="row">				<?if (!empty($arResult["ELEMENTS"])):?>					<div class="col-xs-12 popup-title"><?= html_entity_decode($arResult["ELEMENTS"]["NAME"])?></div>					<div class="col-sm-7 col-xs-12 popup-img">						<?if (strlen($arResult["ELEMENTS"]["RESIZE_PICTURE"]["src"]) > 0):?>							<img src="<?=$arResult["ELEMENTS"]["RESIZE_PICTURE"]["src"]?>" alt="">						<?else:?>							<img src="<?=$templateFolder?>/images/no_photo.gif" alt="">						<?endif;?>						<?if (!empty($arResult["DELIVERY"])):?>							<div class="popup-price">								<?=GetMessage("SALE_PRICE_DELIVERY")?>:								<span><?=$arResult["DELIVERY"]["PRICE_PRINT"]?></span>							</div>						<?endif;?>					</div>				<?endif;?>								<?if (!empty($arResult["ELEMENTS"])):?>				<div class="col-sm-5">				<?else:?>				<div class="col-sm-12">				<?endif;?>                    <div class="popup-price">                        <?if (is_array($arResult["ELEMENTS"]["PRICES_MIN_OFFERS"])                            && strlen($arResult["ELEMENTS"]["PRICES_MIN_OFFERS"]["VALUE"]) > 0):?>                            <span class="js-popup-price-val"><?=$arResult["ELEMENTS"]["PRICES_MIN_OFFERS"]["PRINT_DISCOUNT_VALUE"]?></span>                        <?elseif (is_array($arResult["ELEMENTS"]["PRICES"])):?>                            <span class="js-popup-price-val"><?=$arResult["ELEMENTS"]["PRICES"]["PRICE"]["PRINT_DISCOUNT_VALUE"]?></span>                        <?endif;?>                    </div>					<?if (!empty($arResult["ELEMENTS"]["OFFERS"])):?>					<div class="form-group">						<?if ($arParams["OFFERS_SHOW"] == "all"):?>							<?$key = 0;?>							<?foreach ($arResult["ELEMENTS"]["OFFERS"] as $val):?>								<div class="popup-offers">									<div>										<input type="radio" name="element_offers" value="<?=$val["ID"]?>" id="element_offers_<?=$val["ID"]?>" <?=($key == 0)?"checked":"";?>>										<label for="element_offers_<?=$val["ID"]?>"><?=$val["NAME"]?></label>									</div>									<?if (!empty($val["DISPLAY_PROPERTIES"])):?>										<?foreach ($val["DISPLAY_PROPERTIES"] as $item):?>											<div class="popup-offers-props"><?=$item["NAME"]?>: <?=$item["DISPLAY_VALUE"]?></div>										<?endforeach;?>									<?endif;?>									<div class="popup-offers-props price"><?=GetMessage("SALE_PRICE")?>: <?=$val["PRICE"]?></div>								</div>								<?$key = 1;?>							<?endforeach;?>						<?endif;?>						<?if ($arParams["OFFERS_SHOW"] == "list"):?>							<label for="popup-quantity"><span class="required">*</span> <?=GetMessage('SALE_SKU');?></label>							<select name="element_offers" class="form-control js-element_offers">							<?foreach ($arResult["ELEMENTS"]["OFFERS"] as $key => $val):?>								<option value="<?=$val["ID"]?>" data-price="<?=$val["PRICE"]?>">									<?=$val["NAME"]." ".$val["PRICE"]?>								</option>							<?endforeach;?>							</select>						<?endif;?>					</div>					<?endif;?>					<?include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/props.php");?>                    <?if ($arResult["HIDE_BUTTON"] != "Y"):?>                            <div class="popup-btn">                                <input type="submit" name="popup-order-confirm" class="js-popup-order-confirm checkout-btn" data-id="<?=$arResult["UNIQUE_CODE"]?>" value="<?=GetMessage('SALE_BTN_ORDER')?>">                            </div>                    <?endif;?>				</div>			</div>					</form>	</div></div><?$dir = str_replace('\\', '/', __DIR__);include($dir."/lang/".LANGUAGE_ID."/template.php");if (!empty($arParams["CONFIRM_ORDER"]))	$MESS["SALE_ORDER"] = $arParams["CONFIRM_ORDER"];?><script type="text/javascript">BX.message(<?=CUtil::PhpToJsObject($MESS)?>);</script>