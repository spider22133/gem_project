<?
/**
 * Company developer: ALTASIB
 * Site: http://www.altasib.ru
 * E-mail: dev@altasib.ru
 * Copyright (c) 2006-2017 ALTASIB
 */

CModule::AddAutoloadClasses(
	"altasib.feedback",
	array(
		"AltasibFeedbackCRM" => "classes/crm.php",
		"AltasibFeedbackEvent" => "classes/events.php",
	)
)
?>