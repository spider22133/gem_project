<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?=Loc::getMessage('SALE_HPS_BILLLA_TITLE')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.inv td, table.sign td { padding: 0pt; }
	table.sign td { vertical-align: top; }
	table.header td { padding: 0pt; vertical-align: top; }
</style>
</head>

<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if ($params['BILLLA_BACKGROUND'])
{
	$path = $params['BILLLA_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILLLA_BACKGROUND_STYLE'];
	if (!in_array($backgroundStyle, array('none', 'tile', 'stretch')))
		$backgroundStyle = 'none';

	if ($path)
	{
		switch ($backgroundStyle)
		{
			case 'none':
				$background = "url('" . $path . "') 0 0 no-repeat";
				break;
			case 'tile':
				$background = "url('" . $path . "') 0 0 repeat";
				break;
			case 'stretch':
				$background = sprintf(
					"url('%s') 0 0 repeat-y; background-size: %.02fpt %.02fpt",
					$path, $pageWidth, $pageHeight
				);
				break;
		}
	}
}

$margin = array(
	'top' => intval($params['BILLLA_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLLA_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLLA_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLLA_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">

<table class="header">
	<tr>
		<? if ($params["BILLLA_PATH_TO_LOGO"]) { ?>
		<td style="padding-right: 5pt; ">
			<? $imgParams = CFile::_GetImgParams($params['BILLLA_PATH_TO_LOGO']); ?>
			<? $imgWidth = $imgParams['WIDTH'] * 96 / (intval($params['BILLLA_LOGO_DPI']) ?: 96); ?>
			<img src="<?=$imgParams['SRC']; ?>" width="<?=$imgWidth; ?>" />
		</td>
		<? } ?>
		<td>
			<b><?=$params["SELLER_COMPANY_NAME"]; ?></b><br><?
			if ($params["SELLER_COMPANY_ADDRESS"]) {
				$sellerAddress = $params["SELLER_COMPANY_ADDRESS"];
				if (is_array($sellerAddress))
				{
					if (!empty($sellerAddress))
					{
						$addrValue = implode('<br>', $sellerAddress)
						?><div style="display: inline-block; vertical-align: top;"><b><?= $addrValue ?></b></div><?
						unset($addrValue);
					}
				}
				else
				{
					?><b><?= nl2br($sellerAddress) ?></b><?
				}
				unset($sellerAddress);
				?><br><?
			} ?>
			<? if ($params["SELLER_COMPANY_PHONE"]) { ?>
			<b><?=sprintf(Loc::getMessage('SALE_HPS_BILLLA_COMPANY_PHONE').": %s", $params["SELLER_COMPANY_PHONE"]); ?></b><br>
			<? } ?>
		</td>
	</tr>
</table>
<br>
<?if($params['BILLLA_HEADER']):?>
	<div style="text-align: center; font-size: 2em"><b><?=$params['BILLLA_HEADER'];?></b></div>

	<br>
	<br>
<?endif;?>
<table width="100%">
	<tr>
		<? if ($params["BUYER_PERSON_COMPANY_NAME"]) { ?>
		<td>
			<b><?=Loc::getMessage('SALE_HPS_BILLLA_FOR')?></b><br>
			<?=$params["BUYER_PERSON_COMPANY_NAME"]; ?><br><?
			if ($params["BUYER_PERSON_COMPANY_ADDRESS"]) {
				$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
				if (is_array($buyerAddress))
				{
					if (!empty($buyerAddress))
					{
						$addrValue = implode('<br>', $buyerAddress)
						?><div style="display: inline-block; vertical-align: top;"><?= $addrValue ?></div><?
						unset($addrValue);
					}
				}
				else
				{
					?><?= nl2br($buyerAddress) ?><?
				}
				unset($buyerAddress);
			} ?>
		</td>
		<? } ?>
		<td align="right">
			<?if ($params['BILLLA_PAYER_SHOW'] === 'Y'):?>
				<table class="inv">
					<tr align="right">
						<td><b><?=$params['BILLLA_HEADER'];?> #&nbsp;</b></td>
						<td><?=htmlspecialcharsbx($params["ACCOUNT_NUMBER"]); ?></td>
					</tr>
					<tr align="right">
						<td><b><?=Loc::getMessage('SALE_HPS_BILLLA_DATE_INSERT')?>:&nbsp;</b></td>
						<td><?=$params["DATE_INSERT"]; ?></td>
					</tr>
					<? if ($params["DATE_PAY_BEFORE"]) { ?>
					<tr align="right">
						<td><b><?=Loc::getMessage('SALE_HPS_BILLLA_DATE_PAY_BEFORE')?>:&nbsp;</b></td>
						<td><?=(
							ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
								?: $params["DATE_PAY_BEFORE"]
						); ?></td>
					</tr>
					<? } ?>
				</table>
			<?endif;?>
		</td>
	</tr>
</table>

<?if ($params['BILLLA_PAYER_SHOW'] === 'Y' || $params["BUYER_PERSON_COMPANY_NAME"]):?>
	<br>
	<br>
	<br>
<?endif;?>
<?

$basketItems = array();

/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
$paymentCollection = $payment->getCollection();

/** @var \Bitrix\Sale\Order $order */
$order = $paymentCollection->getOrder();

/** @var \Bitrix\Sale\Basket $basket */
$basket = $order->getBasket();

$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arColsCaption = array();
foreach ($columnList as $column)
{
	if ($params['BILLLA_COLUMN_'.$column.'_SHOW'] == 'Y')
		$arColsCaption[$column] = $params['BILLLA_COLUMN_'.$column.'_TITLE'];
}
$arColumnKeys = array_keys($arColsCaption);
$columnCount = count($arColumnKeys);

if (count($basket->getBasketItems()) > 0)
{
	$arCells = array();
	$arProps = array();

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	$vats = array();

	/** @var \Bitrix\Sale\BasketItem $basketItem */
	foreach ($basket->getBasketItems() as $basketItem)
	{
		// @TODO: replace with real vatless price
		if ($basketItem->isVatInPrice())
			$vatLessPrice = roundEx($basketItem->getPrice() / (1 + $basketItem->getVatRate()), SALE_VALUE_PRECISION);
		else
			$vatLessPrice = $basketItem->getPrice();

		$productName = $basketItem->getField("NAME");
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILLLA_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILLLA_DISCOUNT');

		$arCells[++$n] = array();
		foreach ($arColsCaption as $columnId => $caption)
		{
			$data = null;

			switch ($columnId)
			{
				case 'NUMBER':
					$data = $n;
					break;
				case 'NAME':
					$data = htmlspecialcharsbx($productName);
					break;
				case 'QUANTITY':
					$data = roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION);
					break;
				case 'MEASURE':
					$data = $basketItem->getField("MEASURE_NAME") ? htmlspecialcharsbx($basketItem->getField("MEASURE_NAME")) : Loc::getMessage('SALE_HPS_BILLLA_MEASURE');
					break;
				case 'PRICE':
					$data = SaleFormatCurrency($vatLessPrice, $basketItem->getCurrency(), false);
					break;
				case 'VAT_RATE':
					$data = roundEx($basketItem->getVatRate()*100, SALE_VALUE_PRECISION) . "%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($vatLessPrice * $basketItem->getQuantity(), $basketItem->getCurrency(), false);
					break;
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$arProps[$n] = array();

		/** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
		foreach ($basketItem->getPropertyCollection() as $basketPropertyItem)
		{
			if ($basketPropertyItem->getField('CODE') == 'CATALOG.XML_ID' || $basketPropertyItem->getField('CODE') == 'PRODUCT.XML_ID')
				continue;
			$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem->getField("NAME"), $basketPropertyItem->getField("VALUE")));
		}

		$sum += doubleval($vatLessPrice * $basketItem->getQuantity());
		$vat = max($vat, $basketItem->getVatRate());
		if ($basketItem->getVatRate() > 0)
		{
			if (!isset($vats[$basketItem->getVatRate()]))
				$vats[$basketItem->getVatRate()] = 0;

			if ($basketItem->isVatInPrice())
				$vats[$basketItem->getVatRate()] += ($basketItem->getPrice() - $vatLessPrice) * $basketItem->getQuantity();
			else
				$vats[$basketItem->getVatRate()] += ($basketItem->getPrice()*(1 + $basketItem->getVatRate()) - $vatLessPrice) * $basketItem->getQuantity();
		}
	}

	if ($vat <= 0)
	{
		unset($arColsCaption['VAT_RATE']);
		$columnCount = count($arColsCaption);
		$arColumnKeys = array_keys($arColsCaption);
		foreach ($arCells as $i => $cell)
			unset($arCells[$i]['VAT_RATE']);
	}

	/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
	$shipmentCollection = $order->getShipmentCollection();

	$shipment = null;

	/** @var \Bitrix\Sale\Shipment $shipmentItem */
	foreach ($shipmentCollection as $shipmentItem)
	{
		if (!$shipmentItem->isSystem())
		{
			$shipment = $shipmentItem;
			break;
		}
	}

	if ($shipment && (float)$shipment->getPrice() > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILLLA_DELIVERY');
		if ($shipment->getDeliveryName())
			$sDeliveryItem .= sprintf(" (%s)", $shipment->getDeliveryName());
		$arCells[++$n] = array();
		foreach ($arColsCaption as $columnId => $caption)
		{
			$data = null;

			switch ($columnId)
			{
				case 'NUMBER':
					$data = $n;
					break;
				case 'NAME':
					$data = htmlspecialcharsbx($sDeliveryItem);
					break;
				case 'QUANTITY':
					$data = 1;
					break;
				case 'MEASURE':
					$data = '';
					break;
				case 'PRICE':
					$data = SaleFormatCurrency($shipment->getPrice() / (1 + $vat), $shipment->getCurrency(), false);
					break;
				case 'VAT_RATE':
					$data = roundEx($vat*100, SALE_VALUE_PRECISION) . "%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($shipment->getPrice() / (1 + $vat), $shipment->getCurrency(), false);
					break;
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$sum += roundEx(
			$shipment->getPrice() / (1 + $vat),
			SALE_VALUE_PRECISION
		);

		if ($vat > 0)
			$vats[$vat] += roundEx(
				$shipment->getPrice() * $vat / (1 + $vat),
				SALE_VALUE_PRECISION
			);
	}

	$items = $n;
	if ($params['BILLLA_TOTAL_SHOW'] == 'Y')
	{
		if ($sum < $payment->getSum())
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLLA_SUB_TOTAL').":";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($sum, $order->getCurrency(), false);
		}

		if (!empty($vats))
		{
			// @TODO: remove on real vatless price implemented
			$delta = intval(roundEx(
				$payment->getSum() - $sum - array_sum($vats),
				SALE_VALUE_PRECISION
			) * pow(10, SALE_VALUE_PRECISION));

			if ($delta)
			{
				$vatRates = array_keys($vats);
				rsort($vatRates);

				$ful = intval($delta / count($vatRates));
				$ost = $delta % count($vatRates);

				foreach ($vatRates as $vatRate)
				{
					$vats[$vatRate] += ($ful + $ost) / pow(10, SALE_VALUE_PRECISION);

					if ($ost > 0)
						$ost--;
				}
			}

			foreach ($vats as $vatRate => $vatSum)
			{
				$arCells[++$n] = array();
				for ($i = 0; $i < $columnCount; $i++)
					$arCells[$n][$i] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = sprintf(Loc::getMessage('SALE_HPS_BILLLA_TAX')." (%s%%):", roundEx($vatRate * 100, SALE_VALUE_PRECISION));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($vatSum, $payment->getField('CURRENCY'), false);
			}
		}
		else
		{
			$taxes = $order->getTax();

			$taxesList = $taxes->getTaxList();
			if ($taxesList)
			{
				foreach ($taxesList as $tax)
				{
					$arCells[++$n] = array();
					for ($i = 0; $i < $columnCount; $i++)
						$arCells[$n][$arColumnKeys[$i]] = null;

					$arCells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(sprintf(
						"%s%s%s:",
						($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLLA_TAX_IN') : "",
						$tax["TAX_NAME"],
						sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
					));
					$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($tax["VALUE_MONEY"], $payment->getField('CURRENCY'), false);
				}
			}
		}

		$sumPaid = $paymentCollection->getPaidSum();
		if (DoubleVal($sumPaid) > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLLA_SUM_PAID').":";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($sumPaid, $order->getCurrency(), false);
		}

		if (DoubleVal($order->getDiscountPrice()) > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLLA_DISCOUNT').":";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($order->getDiscountPrice(), $order->getCurrency(), false);
		}

		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLLA_TOTAL').":";
		$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($payment->getSum(), $order->getCurrency(), false);
	}
}

?>
<table class="it" width="100%">
	<tr>
		<?foreach ($arColsCaption as $columnId => $caption):?>
			<td><nobr><?=$caption;?></nobr></td>
		<?endforeach;?>
	</tr>
<?

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$accumulated = 0;

?>
	<tr valign="top">
		<?foreach ($arColsCaption as $columnId => $caption):?>
			<?
				if (!is_null($arCells[$n][$columnId]))
				{
					if ($columnId === 'NUMBER')
					{?>
						<td align="center"><?=$arCells[$n][$columnId];?></td>
					<?}
					elseif ($columnId === 'NAME')
					{
					?>
						<td align="<?=($n > $items) ? 'right' : 'left';?>"
							style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
							<? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
							<?=$arCells[$n][$columnId]; ?>
							<? if (isset($props[$n]) && is_array($props[$n])) { ?>
							<? foreach ($props[$n] as $property) { ?>
							<br>
							<small><?=$property; ?></small>
							<? } ?>
							<? } ?>
						</td>
					<?}
					else
					{
						if (!is_null($arCells[$n][$columnId]))
						{
							if ($columnId != 'VAT_RATE' || $vat > 0 || is_null($arCells[$n][$columnId]) || $n > $items)
							{ ?>
								<td align="right"
									<? if ($accumulated) { ?>
									style="border-width: 0pt 1pt 0pt 0pt"
									colspan="<?=(($columnId == 'VAT_RATE' && $vat <= 0) ? $accumulated : $accumulated+1); ?>"
									<? $accumulated = 0; } ?>>
									<nobr><?=$arCells[$n][$columnId]; ?></nobr>
								</td>
							<? }
						}
						else
						{
							$accumulated++;
						}
					}
				}
				else
				{
					$accumulated++;
				}
			?>
		<?endforeach;?>
	</tr>
<?

}

?>
</table>
<br>
<br>
<br>
<br>

<? if ($params["BILLLA_COMMENT1"] || $params["BILLLA_COMMENT2"]) { ?>
<b><?=Loc::getMessage('SALE_HPS_BILLLA_COMMENT')?></b>
<br>
	<? if ($params["BILLLA_COMMENT1"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLLA_COMMENT1"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if ($params["BILLLA_COMMENT2"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLLA_COMMENT2"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<br>
<br>
<br>

<? $bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"]; ?>
<? $bankRouteNo = $params["SELLER_COMPANY_BANK_ACCOUNT_CORR"]; ?>
<? $bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"]; ?>

<table class="sign" style="width: 100%; ">
	<tr>
		<td style="width: 50%; ">

		<? if ($bankAccNo && $bankRouteNo && $bankSwift) { ?>

			<b><?=Loc::getMessage('SALE_HPS_BILLLA_COMPANY_BANK_DETAIL')?></b>
			<br>

			<? if ($params["SELLER_COMPANY_NAME"]) { ?>
				<?=Loc::getMessage('SALE_HPS_BILLLA_COMPANY_NAME')?>: <?=$params["SELLER_COMPANY_NAME"]; ?>
				<br>
			<? } ?>

			# <?=Loc::getMessage('SALE_HPS_BILLLA_COMPANY_BANK')?>: <?=$bankAccNo; ?>
			<br>

			<? $bank = $params["SELLER_COMPANY_BANK_NAME"]; ?>
			<? $bankAddr = $params["SELLER_COMPANY_BANK_ADDR"]; ?>
			<? $bankPhone = $params["SELLER_COMPANY_BANK_PHONE"]; ?>

			<? if ($bank || $bankAddr || $bankPhone) { ?>
				<?=Loc::getMessage('SALE_HPS_BILLLA_COMPANY_BANK_2')?>: <? if ($bank) { ?><?=$bank; ?><? } ?>
				<br>

				<? if ($bankAddr) { ?>
					<?= nl2br($bankAddr) ?>
					<br>
				<? } ?>

				<? if ($bankPhone) { ?>
					<?=$bankPhone; ?>
					<br>
				<? } ?>
			<? } ?>

			<?=Loc::getMessage('SALE_HPS_BILLLA_COMPANY_BANK_ROUTE_NO')?>: <?=$bankRouteNo; ?>
			<br>

			<?=Loc::getMessage('SALE_HPS_BILLLA_COMPANY_BANK_SWIFT')?>: <?=$bankSwift; ?>
			<br>
		<? } ?>

		</td>
		<td style="width: 50%; ">

			<? if (!$blank) { ?>
			<div style="position: relative; "><?=CFile::ShowImage(
				$params["BILLLA_PATH_TO_STAMP"],
				160, 160,
				'style="position: absolute; left: 30pt; "'
			); ?></div>
			<? } ?>

			<table style="width: 100%; position: relative; ">
				<colgroup>
					<col width="0">
					<col width="100%">
				</colgroup>
				<? if ($params["SELLER_COMPANY_DIRECTOR_POSITION"]) { ?>
				<? if ($params["SELLER_COMPANY_DIRECTOR_NAME"] || $params["SELLER_COMPANY_DIR_SIGN"]) { ?>
				<? if ($params["SELLER_COMPANY_DIRECTOR_NAME"]) { ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="2"><?=$params["SELLER_COMPANY_DIRECTOR_NAME"]; ?></td>
				</tr>
				<? } ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><nobr><?=$params["SELLER_COMPANY_DIRECTOR_POSITION"]; ?></nobr></td>
					<td style="border-bottom: 1pt solid #000000; text-align: center; ">
						<? if (!$blank && $params["SELLER_COMPANY_DIR_SIGN"]) { ?>
						<span style="position: relative; ">&nbsp;<?=CFile::ShowImage(
							$params["SELLER_COMPANY_DIR_SIGN"],
							200, 50,
							'style="position: absolute; margin-left: -75pt; bottom: 0pt; "'
						); ?></span>
						<? } ?>
					</td>
				</tr>
				<? } ?>
				<? } ?>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]) { ?>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"] || $params["SELLER_COMPANY_ACC_SIGN"]) { ?>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"]) { ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="2"><?=$params["SELLER_COMPANY_ACCOUNTANT_NAME"]; ?></td>
				</tr>
				<? } ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><nobr><?=$params["SELLER_COMPANY_ACCOUNTANT_POSITION"]; ?></nobr></td>
					<td style="border-bottom: 1pt solid #000000; text-align: center; ">
						<? if (!$blank && $params["SELLER_COMPANY_ACC_SIGN"]) { ?>
						<span style="position: relative; ">&nbsp;<?=CFile::ShowImage(
							$params["SELLER_COMPANY_ACC_SIGN"],
							200, 50,
							'style="position: absolute; margin-left: -75pt; bottom: 0pt; "'
						); ?></span>
						<? } ?>
					</td>
				</tr>
				<? } ?>
				<? } ?>
			</table>

		</td>
	</tr>
</table>

</div>

</body>
</html>