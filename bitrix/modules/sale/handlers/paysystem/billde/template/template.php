<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Rechnung</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.sign td { vertical-align: bottom }
	table.header td { padding: 0pt; vertical-align: top; }
</style>
</head>

<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if ($params['BILLDE_BACKGROUND'])
{
	$path = $params['BILLDE_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILLDE_BACKGROUND_STYLE'];
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
	'top' => intval($params['BILLDE_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLDE_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLDE_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLDE_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">

<table class="header">
	<tr>
		<? if ($params["BILLDE_PATH_TO_LOGO"]) { ?>
		<td style="padding-right: 5pt; ">
			<? $imgParams = CFile::_GetImgParams($params['BILLDE_PATH_TO_LOGO']); ?>
			<? $imgWidth = $imgParams['WIDTH'] * 96 / (intval($params['BILLDE_LOGO_DPI']) ?: 96); ?>
			<img src="<?=$imgParams['SRC']; ?>" width="<?=$imgWidth; ?>" />
		</td>
		<? } ?>
		<td style="font-size: 3em; ">
			<b><?=$params["SELLER_COMPANY_NAME"]; ?></b>
		</td>
	</tr>
</table>
<br>

<span style="text-decoration: underline">
	<small>
		<b><?=$params["SELLER_COMPANY_NAME"]; ?><?
		if ($params["SELLER_COMPANY_ADDRESS"])
		{
			$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
			if (is_array($sellerAddr))
				$sellerAddr = implode(', ', $sellerAddr);
			else
				$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
			?> - <?=$sellerAddr?><?
		}
?></b></small></span>
<br>
<br>
<br>

<? if ($params["BUYER_PERSON_COMPANY_NAME"]) { ?>
	<b><?=$params["BUYER_PERSON_COMPANY_NAME"]; ?></b>
	<br><?
    if ($params["BUYER_PERSON_COMPANY_PAYER_NAME"])
	{
		?><?=$params["BUYER_PERSON_COMPANY_PAYER_NAME"]?><?
		?><br><?
	}
	if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
	{
		$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
		if (is_array($buyerAddress))
		{
			if (!empty($buyerAddress))
			{
				$addrValue = implode('<br>', $buyerAddress)
				?><div style="display: inline-block; vertical-align: top;"><?= $addrValue ?></div><?
				?><br><?
				unset($addrValue);
			}
		}
		else
		{
			?><?= nl2br($buyerAddress) ?><?
			?><br><?
		}
		unset($buyerAddress);
	} ?>
<? } ?>

<br>
<br>
<br>
<br>

<span style="font-size: 2em"><b><?=$params['BILLDE_HEADER']?></b></span>

<br>
<br>
<br>
<? if ($params['BILLDE_HEADER_SHOW'] == 'Y'):?>
	<table width="100%" style="font-weight: bold">
		<tr>
			<td><?=sprintf(
				$params['BILLDE_HEADER'].' Nr. %s',
				htmlspecialcharsbx($params["ACCOUNT_NUMBER"])
			); ?></td>
			<td><? if ($params["BUYER_PERSON_COMPANY_ID"]) {
			echo sprintf(
				'Kunden-Nr.: %s',
				$params["BUYER_PERSON_COMPANY_ID"]
			); } ?></td>
			<td align="right"><?=sprintf(
				'Datum: %s',
				$params["DATE_INSERT"]
			); ?></td>
		</tr>
		<? if ($params["DATE_PAY_BEFORE"]) { ?>
		<tr>
			<td></td>
			<td></td>
			<td align="right"><?=sprintf(
				'Bezahlen bis: %s',
				ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
					?: $params["DATE_PAY_BEFORE"]
			); ?></td>
		</tr>
		<? } ?>
	</table>
	<small><b>Bitte bei Zahlungen und Schriftverkehr angeben!</b></small>
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
	if ($params['BILLDE_COLUMN_'.$column.'_SHOW'] == 'Y')
		$arColsCaption[$column] = $params['BILLDE_COLUMN_'.$column.'_TITLE'];
}
$arColumnKeys = array_keys($arColsCaption);
$columnCount = count($arColumnKeys);

$arCells = array();
$items = 0;

if (count($basket->getBasketItems()) > 0)
{
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

		$productName = $basketItem->getField('NAME');
		if ($productName == "OrderDelivery")
			$productName = "Schifffahrt";
		else if ($productName == "OrderDiscount")
			$productName = "Rabatt";

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
					$data = $basketItem->getField("MEASURE_NAME") ? htmlspecialcharsbx($basketItem->getField("MEASURE_NAME")) : 'St.';
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
		$sDeliveryItem = "Schifffahrt";
		if (strlen($shipment->getDeliveryName()) > 0)
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
					$data = roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION);
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

		$sum += roundEx($shipment->getPrice() / (1 + $vat), SALE_VALUE_PRECISION);

		if ($vat > 0)
			$vats[$vat] += roundEx($shipment->getPrice() * $vat / (1 + $vat), SALE_VALUE_PRECISION);
	}

	if ($vat <= 0)
	{
		unset($arColsCaption['VAT_RATE']);
		$columnCount = count($arColsCaption);
		$arColumnKeys = array_keys($arColsCaption);
		foreach ($arCells as $i => $cell)
			unset($arCells[$i]['VAT_RATE']);
	}

	$items = $n;
	if ($params['BILLDE_TOTAL_SHOW'] == 'Y')
	{
		if ($sum < $payment->getSum())
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = "Nettobetrag:";
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

				$arCells[$n][$arColumnKeys[$columnCount-2]] = sprintf("zzgl. %s%% MwSt:", roundEx($vatRate * 100, SALE_VALUE_PRECISION));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($vatSum, $order->getCurrency(), false);
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
						($tax["IS_IN_PRICE"] == "Y") ? "inkl." : "zzgl.",
						sprintf(' %s%% ', roundEx($tax["VALUE"], SALE_VALUE_PRECISION)),
						$tax["TAX_NAME"]
					));
					$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($tax["VALUE_MONEY"], $order->getCurrency(), false);
				}
			}
		}

		$sumPaid = $paymentCollection->getPaidSum();
		if (DoubleVal($sumPaid) > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = "Payment made:";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($sumPaid, $order->getCurrency(), false);
		}

		if (DoubleVal($order->getDiscountPrice()) > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = "Rabatt:";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($order->getDiscountPrice(), $order->getCurrency(), false);
		}

		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = "Gesamtbetrag:";
		$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($payment->getSum(), $order->getCurrency(), false);
	}
}

?>
<table class="it" width="100%">
	<tr align="center">
		<?foreach ($arColsCaption as $columnId => $caption):?>
			<td><nobr><?=$caption;?></nobr></td>
		<?endforeach;?>
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

<? if ($params["BILLDE_COMMENT1"] || $params["BILLDE_COMMENT2"]) { ?>
	<? if ($params["BILLDE_COMMENT1"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLDE_COMMENT1"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if ($params["BILLDE_COMMENT2"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLDE_COMMENT2"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<br>

<? if (!$blank) { ?>
<div style="position: relative; "><?=CFile::ShowImage(
	$params["BILLDE_PATH_TO_STAMP"],
	160, 160,
	'style="position: absolute; left: 40pt; "'
); ?></div>
<? } ?>

<div style="position: relative">
	<table class="sign">
		<? if ($params["SELLER_COMPANY_DIRECTOR_POSITION"]) { ?>
		<tr>
			<td style="width: 150pt; "><?=$params["SELLER_COMPANY_DIRECTOR_POSITION"]; ?></td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<? if (!$blank) { ?>
				<?=CFile::ShowImage($params["SELLER_COMPANY_DIR_SIGN"], 200, 50); ?>
				<? } ?>
			</td>
			<td>
				<? if ($params["SELLER_COMPANY_DIRECTOR_NAME"]) { ?>
				(<?=$params["SELLER_COMPANY_DIRECTOR_NAME"]; ?>)
				<? } ?>
			</td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<? } ?>
		<? if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]) { ?>
		<tr>
			<td style="width: 150pt; "><?=$params["SELLER_COMPANY_ACCOUNTANT_POSITION"]; ?></td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<? if (!$blank) { ?>
				<?=CFile::ShowImage($params["SELLER_COMPANY_ACC_SIGN"], 200, 50); ?>
				<? } ?>
			</td>
			<td>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"]) { ?>
				(<?=$params["SELLER_COMPANY_ACCOUNTANT_NAME"]; ?>)
				<? } ?>
			</td>
		</tr>
		<? } ?>
	</table>
</div>

<br>
<br>
<br>


<div style="text-align: center">

<?

$sellerName = $params["SELLER_COMPANY_NAME"];
$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
if (is_array($sellerAddr))
	$sellerAddr = implode(', ', $sellerAddr);
else
	$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));

$sellerData = array();

if ($sellerName)
	$sellerData[] = $sellerName;
if ($sellerAddr)
	$sellerData[] = $sellerAddr;

if (!empty($sellerData))
{
	?><small><?=join(' - ', $sellerData); ?></small>
	<br><?
}


$sellerPhone = $params["SELLER_COMPANY_PHONE"];
$sellerEmail = $params["SELLER_COMPANY_EMAIL"];

$sellerData = array();

if ($sellerPhone)
	$sellerData[] = sprintf('Telefon: %s', $sellerPhone);
if ($sellerEmail)
	$sellerData[] = sprintf('Mail: %s', $sellerEmail);

if (!empty($sellerData))
{
	?><small><?=join(' - ', $sellerData); ?></small>
	<br><?
}


$bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"];
$bankBlz   = $params["SELLER_COMPANY_BANK_BIC"];
$bankIban  = $params["SELLER_COMPANY_BANK_IBAN"];
$bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"];
$bank      = $params["SELLER_COMPANY_BANK_NAME"];

$bankData = array();

if ($bankAccNo)
	$bankData[] = sprintf('Konto Nr.: %s', $bankAccNo);
if ($bankBlz)
	$bankData[] = sprintf('BLZ: %s', $bankBlz);
if ($bankIban)
	$bankData[] = sprintf('IBAN: %s', $bankIban);
if ($bankSwift)
	$bankData[] = sprintf('BIC/SWIFT: %s', $bankSwift);
if ($bank)
	$bankData[] = $bank;

if (!empty($bankData))
{
	?><small><?=join(' - ', $bankData); ?></small>
	<br><?
}


$sellerEuInn = $params["SELLER_COMPANY_EU_INN"];
$sellerInn   = $params["SELLER_COMPANY_INN"];
$sellerReg   = $params["SELLER_COMPANY_REG"];
$sellerDir   = $params["SELLER_COMPANY_DIRECTOR_NAME"];

$sellerData = array();

if ($sellerEuInn)
	$sellerData[] = sprintf('USt-IdNr.: %s', $sellerEuInn);
if ($sellerInn)
	$sellerData[] = sprintf('Steuernummer: %s', $sellerInn);
if ($sellerReg)
	$sellerData[] = $sellerReg;
if ($sellerDir)
	$sellerData[] = $sellerDir;

if (!empty($sellerData))
{
	?><small><?=join(' - ', $sellerData); ?></small>
	<br><?
}

?>

</div>

</div>

</body>
</html>