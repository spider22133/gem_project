<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale;
use Bitrix\Main;
use Bitrix\Sale\PaySystem\ServiceResult;

Main\Localization\Loc::loadMessages(__FILE__);

class AlfaBankB2BHandler extends PaySystem\BaseServiceHandler implements PaySystem\IRequested
{
	/**
	 * @param Sale\Payment $payment
	 * @param Main\Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Sale\Payment $payment, Main\Request $request = null)
	{
		$serviceResult = new ServiceResult();

		if ($request === null)
		{
			$instance = Main\Application::getInstance();
			$context = $instance->getContext();
			$request = $context->getRequest();
		}

		if ($request->get('initiate_pay') !== null)
		{
			$template = '';
			$params = $this->getParamsBusValue($payment);
			$params['CODE'] = $params['PAYMENT_ID'];

			$params['PAYMENT_ID'] = $payment->getId();
			/**
			 * from AlfaBank documentation:
			 * document number(in our system PAYMENT_ID) must be > 0 and < 99999, can not end on 000
			 */
			if ($params['PAYMENT_ID'] < 99999)
			{
				if ($params['PAYMENT_ID'] % 1000 === 0)
					$params['PAYMENT_ID']++;
			}
			else
			{
				$params['PAYMENT_ID'] %= 99999;
			}

			$params['PAYMENT_SHOULD_PAY'] = roundEx($params['PAYMENT_SHOULD_PAY'], 2)*100;

			/** @var Main\Type\DateTime $date */
			$date = $params['PAYMENT_DATE_INSERT'];
			$params['PAYMENT_DATE_INSERT'] = $date->format('Y-m-d');

			$this->setInitiateMode(PaySystem\BaseServiceHandler::STRING);
			$this->setExtraParams($params);

			$result = $this->showTemplate($payment, 'payment');
			if ($result->isSuccess())
				$template = $result->getTemplate();

			$data = $this->createRequest($template);

			$url = $this->getUrl($payment, 'create');
			$result = $this->sendRequest($url, 'WSCreatePaymentDocRURAdd', $data);
			if ($result->isSuccess())
			{
				$data = $result->getData();

				$xml = new \CDataXML();
				$xml->LoadString($data['RESPONSE']);
				$response = $xml->GetArray();

				if (is_array($response['Envelope']['#']['Body'][0]['#']) && array_key_exists('Fault', $response['Envelope']['#']['Body'][0]['#']))
				{
					$detailErrors = $response['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['detail'][0]['#'];
					foreach ($detailErrors as $error)
					{
						$errorCode = $error[0]['#']['errorCode'][0]['#'];
						$errorString = Main\Localization\Loc::getMessage('SALE_HPS_ALFABANK_ERROR_'.$errorCode);
						if (strlen($errorString) === 0)
							$errorString = $error[0]['#']['errorString'][0]['#'];

						$serviceResult->addError(new Main\Error($errorString));
					}
				}
				else if (is_array($response) && $this->verifySign($response))
				{
					$docId = $response['Envelope']['#']['Body'][0]['#']['WSCreatePaymentDocRURAddResponse'][0]['#']['outParms'][0]['#']['docId'][0]['#'];
					if ($docId !== '')
						$serviceResult->setPsData(array('PS_INVOICE_ID' => $docId));
				}
			}

			$templateName = $serviceResult->isSuccess() ? 'success' : 'failure';
			$result = $this->showTemplate($payment, $templateName);
			$serviceResult->setTemplate($result->getTemplate());
		}
		else
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $payment->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			$order = $paymentCollection->getOrder();

			$this->setExtraParams(
				array(
					'ORDER_ID' => $order->getId(),
					'ACCOUNT_NUMBER' => $order->getField('ACCOUNT_NUMBER'),
					'PAYSYSTEM_ID' => $this->service->getField('ID')
				)
			);

			$serviceResult = $this->showTemplate($payment, 'template');
		}

		return $serviceResult;
	}

	/**
	 * @param $data
	 * @return string
	 */
	private function createRequest($data)
	{
		$dom = new \DOMDocument('1.0', 'utf-8');

		$data = $this->convertToUtf8($data);
		$dom->loadXML($data);

		$xp = new \DOMXPath($dom);
		$xp->registerNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
		$headerNode = $xp->query('/soapenv:Envelope/soapenv:Header')->item(0);

		/** @var \DOMElement $bodyNode */
		$bodyNode = $xp->query('/soapenv:Envelope/soapenv:Body')->item(0);
		if ($bodyNode)
		{
			$bodyNode->setAttributeNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd', 'wsu:Id', 'reqBody');
			$secNode = $headerNode->appendChild($dom->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:Security'));

			$domSecToken = $this->buildSecurityToken($dom);
			$secNode->appendChild($domSecToken);

			$signNode = $secNode->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Signature'));
			$signInfo = $signNode->appendChild($this->buildSignedInfo($dom, array('reqBody')));

			$canonizedData = $signInfo->C14N(true, true);
			$signature = $this->sign($canonizedData);

			$signNode->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignatureValue', base64_encode($signature)));
			$keyInfo = $signNode->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:KeyInfo'));
			$secTokRef = $keyInfo->appendChild($dom->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:SecurityTokenReference'));

			/** @var \DOMElement $keyRef */
			$keyRef = $secTokRef->appendChild($dom->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:Reference'));
			$keyRef->setAttribute('URI', "#Security-Token-1");
		}

		return $dom->saveXML();
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	private function verifySign(array $data)
	{

		$signedInfo = $data['Envelope']['#']['Header'][0]['#']['Security'][0]['#']['Signature'][0]['#']['SignedInfo'];
		$signatureValue = $data['Envelope']['#']['Header'][0]['#']['Security'][0]['#']['Signature'][0]['#']['SignatureValue'][0]['#'];
		$cert = $data['Envelope']['#']['Header'][0]['#']['Security'][0]['#']['BinarySecurityToken'][0]['#'];

		// openssl verify

		return true;
	}

	/**
	 * @param $url
	 * @param $action
	 * @param $data
	 * @return ServiceResult
	 */
	private function sendRequest($url, $action, $data)
	{
		$serviceResult = new ServiceResult();

		$urlObj = new Main\Web\Uri($url);
		if ($urlObj->getScheme() === 'https')
		{
			$scheme = 'ssl://';
			$port = 443;
		}
		else
		{
			$scheme = '';
			$port = 80;
		}

		$header = "POST ".$urlObj->getPath()." HTTP/1.0\r\n";
		$header .= "Host: ".$urlObj->getHost()."\r\n";
		$header .= "Content-Type: text/xml; charset=utf-8\r\n";
		$header .= "Connection: Keep-Alive\r\n";
		$header .= "SOAPAction: ".$action."\r\n";
		$header .= sprintf("Content-length: %s\r\n", strlen($data));
		$header .= "\r\n";

		$errNo = '';
		$errStr = '';

		$fp = stream_socket_client($scheme.$urlObj->getHost().':'.$port.$urlObj->getPath(), $errNo, $errStr, 30);

		if ($fp !== false)
		{
			fputs($fp, $header.$data);
			$response = @stream_get_contents($fp);
			list($header, $body) = explode("\r\n\r\n", $response);
			$serviceResult->setData(array('RESPONSE' => $body));
			fclose($fp);
		}
		else
		{
			$serviceResult->addError(new Main\Error($errNo.' - '.$errStr));
		}

		return $serviceResult;
	}

	/**
	 * @param $data
	 * @return false|string
	 */
	private function sign($data)
	{
		if (IsModuleInstalled('bitrix24') && function_exists('bx_alfabank_sign'))
			return bx_alfabank_sign($data);

		return false;
	}

	/**
	 * @param \DOMDocument $dom
	 * @return mixed
	 */
	public function buildSecurityToken($dom)
	{
		$cert = '';
		if (IsModuleInstalled('bitrix24') && function_exists('bx_alfabank_cert'))
			$cert = bx_alfabank_cert();
		$cert = str_replace('-----BEGIN CERTIFICATE-----', '', $cert);
		$cert = str_replace('-----END CERTIFICATE-----', '', $cert);
		$cert = trim($cert);

		$token = $dom->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:BinarySecurityToken', $cert);
		$token->setAttribute('ValueType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');
		$token->setAttribute('EncodingType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary');
		$token->setAttributeNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd', 'wsu:Id', 'Security-Token-1');

		return $token;
	}

	/**
	 * @param \DOMDocument $dom
	 * @param array $ids
	 * @return \DOMNode
	 */
	public function buildSignedInfo($dom, $ids)
	{
		$xp = new \DOMXPath($dom);
		$xp->registerNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
		$xp->registerNamespace('wsu', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd');
		$xp->registerNamespace('wsse', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');
		$xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

		$signedInfo = $dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignedInfo');

		/** @var \DOMElement $method */
		$method = $signedInfo->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:CanonicalizationMethod'));
		$method->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');

		$method = $signedInfo->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignatureMethod'));
		$method->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#gostr34102001-gostr3411');

		foreach ($ids as $id)
		{
			$nodes = $xp->query("//*[(@wsu:Id='{$id}')]");

			if ($nodes->length == 0)
				continue;

			$canonized = $nodes->item(0)->C14N(true, true);

			/** @var \DOMElement $reference */
			$reference = $signedInfo->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Reference'));
			$reference->setAttribute('URI', "#{$id}");

			$transforms = $reference->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Transforms'));

			/** @var \DOMElement $transform */
			$transform = $transforms->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Transform'));
			$transform->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');

			$method = $reference->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod'));
			$method->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#gostr3411');
			$reference->appendChild($dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue', base64_encode(hash('gost-crypto', $canonized, true))));
		}

		return $signedInfo;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	/**
	 * @return array
	 */
	protected function getUrlList()
	{
		return array(
			'create' => array(
				self::TEST_URL => 'https://testjmb.alfabank.ru/CS/ALBO/WSCreatePaymentDocRUR/WSCreatePaymentDocRUR11',
				self::ACTIVE_URL => 'https://link.alfabank.ru/CS/ALBO/WSCreatePaymentDocRUR/WSCreatePaymentDocRUR11'
			),
			'request' => array(
				self::TEST_URL => 'https://testjmb.alfabank.ru/CS/ALBO/WSCreateAccountMovementListRequest/WSCreateAccountMovementListRequest',
				self::ACTIVE_URL => 'https://link.alfabank.ru/CS/ALBO/WSCreateAccountMovementListRequest/WSCreateAccountMovementListRequest'
			),
			'list' => array(
				self::TEST_URL => 'https://testjmb.alfabank.ru/CS/ALBO/WSGetAccountMovementList/WSGetAccountMovementList',
				self::ACTIVE_URL => 'https://link.alfabank.ru/CS/ALBO/WSGetAccountMovementList/WSGetAccountMovementList'
			),
		);
	}

	/**
	 * @param Main\Request $request
	 * @return ServiceResult
	 */
	public function createMovementListRequest(Main\Request $request)
	{
		$serviceResult = new ServiceResult();
		$template = '';

		$date = $request->get('DATE_START');
		$dateStart = new Main\Type\Date($date);
		if (empty($date))
			$dateStart->add('-10d');

		$date = $request->get('DATE_END');
		$dateEnd = new Main\Type\Date($date);
		if (empty($date))
			$dateEnd->add('-1d');

		$this->setInitiateMode(PaySystem\BaseServiceHandler::STRING);
		$this->setExtraParams(array('START_DATE' => $dateStart->format('Y-m-d'), 'END_DATE' => $dateEnd->format('Y-m-d')));

		$result = $this->showTemplate(null, 'listrequest');
		if ($result->isSuccess())
			$template = $result->getTemplate();

		$data = $this->createRequest($template);
		$result = $this->sendRequest($this->getUrl(null, 'request'), 'WSCreateAccountMovementListRequestAdd', $data);
		if ($result->isSuccess())
		{
			$data = $result->getData();

			$xml = new \CDataXML();
			$xml->LoadString($data['RESPONSE']);
			$response = $xml->GetArray();

			if (is_array($response['Envelope']['#']['Body'][0]['#']) && array_key_exists('Fault', $response['Envelope']['#']['Body'][0]['#']))
			{
				$detailErrors = $response['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['detail'][0]['#'];
				foreach ($detailErrors as $error)
				{
					$errorCode = $error[0]['#']['errorCode'][0]['#'];
					$errorString = Main\Localization\Loc::getMessage('SALE_HPS_ALFABANK_ERROR_'.$errorCode);
					if (strlen($errorString) === 0)
						$errorString = $error[0]['#']['errorString'][0]['#'];

					$serviceResult->addError(new Main\Error($errorString));
				}
			}
			else if (is_array($response) && $this->verifySign($response))
			{
				$requestId = $response['Envelope']['#']['Body'][0]['#']['WSCreateAccountMovementListRequestAddResponse'][0]['#']['outParms'][0]['#']['requestId'][0]['#'];
				if (strlen($requestId) > 0)
					$serviceResult->setData(array('requestId' => $requestId));
			}
		}
		else
		{
			$serviceResult->addErrors($result->getErrors());
		}

		return $serviceResult;
	}

	/**
	 * @param $requestId
	 * @return ServiceResult
	 */
	public function getMovementListStatus($requestId = null)
	{
		$serviceResult = new ServiceResult();

		$status = false;
		$time = '';
		$template = '';

		$this->setInitiateMode(PaySystem\BaseServiceHandler::STRING);
		$this->setExtraParams(array('REQUEST_ID' => $requestId));

		$result = $this->showTemplate(null, 'movementstatus');
		if ($result->isSuccess())
			$template = $result->getTemplate();

		$data = $this->createRequest($template);
		$result = $this->sendRequest($this->getUrl(null, 'list'), 'WSGetAccountMovementListStatus', $data);
		if ($result->isSuccess())
		{
			$data = $result->getData();

			$xml = new \CDataXML();
			$xml->LoadString($data['RESPONSE']);
			$response = $xml->GetArray();

			if (is_array($response['Envelope']['#']['Body'][0]['#']) && array_key_exists('Fault', $response['Envelope']['#']['Body'][0]['#']))
			{
				$detailErrors = $response['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['detail'][0]['#'];
				foreach ($detailErrors as $error)
				{
					$errorCode = $error[0]['#']['errorCode'][0]['#'];
					$errorString = Main\Localization\Loc::getMessage('SALE_HPS_ALFABANK_ERROR_'.$errorCode);
					if (strlen($errorString) === 0)
						$errorString = $error[0]['#']['errorString'][0]['#'];

					$serviceResult->addError(new Main\Error($errorString));
				}
			}
			else if (is_array($response) && $this->verifySign($response))
			{
				$status = $response['Envelope']['#']['Body'][0]['#']['WSGetAccountMovementListStatusResponse'][0]['#']['outParms'][0]['#']['status'][0]['#'];
				$status = ($status === 'true') ? true : false;
				if ($status !== true)
					$time = $response['Envelope']['#']['Body'][0]['#']['WSGetAccountMovementListStatusResponse'][0]['#']['outParms'][0]['#']['estimatedTime'][0]['#'];
			}
		}

		$serviceResult->setData(array('status' => $status, 'estimatedTime' => $time));

		return $serviceResult;
	}

	/**
	 * @param $requestId
	 * @return ServiceResult
	 */
	public function getMovementList($requestId = null)
	{
		$serviceResult = new ServiceResult();
		$template = '';

		$this->setInitiateMode(PaySystem\BaseServiceHandler::STRING);
		$this->setExtraParams(array('REQUEST_ID' => $requestId));

		$result = $this->showTemplate(null, 'movementlist');
		if ($result->isSuccess())
			$template = $result->getTemplate();

		$data = $this->createRequest($template);
		$result = $this->sendRequest($this->getUrl(null, 'list'), 'WSGetAccountMovementListDoc', $data);
		if ($result->isSuccess())
		{
			$data = $result->getData();

			$xml = new \CDataXML();
			$xml->LoadString($data['RESPONSE']);
			$response = $xml->GetArray();

			if (is_array($response['Envelope']['#']['Body'][0]['#']) && array_key_exists('Fault', $response['Envelope']['#']['Body'][0]['#']))
			{
				$detailErrors = $response['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['detail'][0]['#'];
				foreach ($detailErrors as $error)
				{
					$errorCode = $error[0]['#']['errorCode'][0]['#'];
					$errorString = Main\Localization\Loc::getMessage('SALE_HPS_ALFABANK_ERROR_'.$errorCode);
					if (strlen($errorString) === 0)
						$errorString = $error[0]['#']['errorString'][0]['#'];

					$serviceResult->addError(new Main\Error($errorString));
				}
			}
			else if (is_array($response) && $this->verifySign($response))
			{
				$dataRow = $response['Envelope']['#']['Body'][0]['#']['WSGetAccountMovementListDocResponse'][0]['#']['outParms'][0]['#']['resultSet'][0]['#']['row'];
				if ($dataRow)
				{
					$itemList = array();
					foreach ($dataRow as $row)
					{
						$document = $row['#'];
						$itemList[] = array(
							'PAYMENT_ID' => $document['code'][0]['#'],
							'CONTRACTOR_INN' => $document['contractorInn'][0]['#'],
							'CONTRACTOR_KPP' => $document['contractorKpp'][0]['#'],
							'OPERATION' => $document['operation'][0]['#'],
							'SUM' => (int)$document['amount'][0]['#'] / 100,
							'CHARGE_DATE' => new Main\Type\Date($document['chargeDate'][0]['#'], 'Y-m-d'),
							'DOC_DATE' => new Main\Type\Date($document['docDate'][0]['#'], 'Y-m-d'),
							'INVOICE_ID' => $document['documentId'][0]['#']
						);
					}

					$serviceResult->setData(array('ITEMS' => $itemList));
				}
			}
		}
		else
		{
			$serviceResult->addErrors($result->getErrors());
		}

		return $serviceResult;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	private function convertToUtf8($data)
	{
		static $isUtf = null;

		if ($isUtf === null)
			$isUtf = defined('BX_UTF');

		if ($isUtf === true)
			return $data;

		if (is_array($data))
		{
			foreach ($data as $key => $value)
				$data[$key] = Main\Text\Encoding::convertEncoding($data[$key], LANG_CHARSET, 'UTF-8');
		}
		elseif (!is_numeric($data) && is_string($data))
		{
			$data = Main\Text\Encoding::convertEncoding($data, LANG_CHARSET, 'UTF-8');
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	public function isTuned()
	{
		$userCode = $this->getBusinessValue(null, 'ALFABANK_EXTERNAL_USER_CODE');
		return !empty($userCode);
	}

	/**
	 * @param Payment $payment
	 * @param $code
	 * @return mixed
	 */
	protected function getBusinessValue(Payment $payment = null, $code)
	{
		if ($payment === null)
		{
			$paySystemId = $this->service->getField('ID');
			$personTypeList = PaySystem\Manager::getPersonTypeIdList($paySystemId);
			$personTypeId = array_shift($personTypeList);

			return Sale\BusinessValue::get($code, $this->service->getConsumerName(), $personTypeId);
		}

		return parent::getBusinessValue($payment, $code);
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return false;
	}

}