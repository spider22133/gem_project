<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsc="http://WSCreatePaymentDocRUR11.ALBO.CS.ws.alfabank.ru" xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru">
	<soapenv:Header>
	</soapenv:Header>
	<soapenv:Body>
		<wsc:WSCreatePaymentDocRURAdd>
			<inCommonParms>
				<externalSystemCode><?=$params['ALFABANK_EXTERNAL_SYSTEM_CODE'];?></externalSystemCode>
				<externalUserCode><?=$params['ALFABANK_EXTERNAL_USER_CODE'];?></externalUserCode>
			</inCommonParms>
			<inParms>
				<wsc1:docNumber><?=$params['PAYMENT_ID'];?></wsc1:docNumber>
				<wsc1:docDate><?=$params['PAYMENT_DATE_INSERT'];?></wsc1:docDate>
				<wsc1:docSum><?=$params['PAYMENT_SHOULD_PAY'];?></wsc1:docSum>
				<wsc1:payerAccount><?=$params['BUYER_PERSON_COMPANY_NAME_CONTACT'];?></wsc1:payerAccount>
				<wsc1:payerKpp><?=$params['BUYER_PERSON_COMPANY_KPP'];?></wsc1:payerKpp>
				<wsc1:payerInn><?=$params['BUYER_PERSON_COMPANY_INN'];?></wsc1:payerInn>
				<wsc1:recipientName><?=$params['SELLER_COMPANY_NAME'];?></wsc1:recipientName>
				<wsc1:recipientInn><?=$params['SELLER_COMPANY_INN'];?></wsc1:recipientInn>
				<wsc1:recipientKpp><?=$params['SELLER_COMPANY_KPP'];?></wsc1:recipientKpp>
				<wsc1:recipientAccount><?=$params['SELLER_COMPANY_BANK_ACCOUNT'];?></wsc1:recipientAccount>
				<wsc1:recipientBankBik><?=$params['SELLER_COMPANY_BANK_BIC'];?></wsc1:recipientBankBik>
				<wsc1:priority><?=$params['ALFABANK_PRIORITY'];?></wsc1:priority>
				<wsc1:paymentPurpose><?=$params['ALFABANK_PAYMENT_SUBJECT'];?></wsc1:paymentPurpose>
				<wsc1:budgetPayerStatus><?=$params['ALFABANK_BUDGET_PAYER_STATUS'];?></wsc1:budgetPayerStatus>
				<wsc1:budgetKbk><?=$params['ALFABANK_BUDGET_KBK'];?></wsc1:budgetKbk>
				<wsc1:budgetOkato><?=$params['ALFABANK_BUDGET_OKATO'];?></wsc1:budgetOkato>
				<wsc1:budgetOktmo><?=$params['ALFABANK_BUDGET_OKTMO'];?></wsc1:budgetOktmo>
				<wsc1:budgetPaymentBase><?=$params['ALFABANK_BUDGET_PAYMENT_BASE'];?></wsc1:budgetPaymentBase>
				<wsc1:budgetPeriod><?=$params['ALFABANK_BUDGET_PERIOD'];?></wsc1:budgetPeriod>
				<wsc1:budgetDocNumber><?=$params['ALFABANK_BUDGET_DOC_NUMBER'];?></wsc1:budgetDocNumber>
				<wsc1:budgetDocDate><?=$params['ALFABANK_BUDGET_DOC_DATE'];?></wsc1:budgetDocDate>
				<wsc1:budgetPaymentType><?=$params['ALFABANK_BUDGET_PAYMENT_TYPE'];?></wsc1:budgetPaymentType>
				<wsc1:code><?=$params['CODE'];?></wsc1:code>
			</inParms>
		</wsc:WSCreatePaymentDocRURAdd>
	</soapenv:Body>
</soapenv:Envelope>
