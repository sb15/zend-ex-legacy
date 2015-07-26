<?php
require_once 'Zend/Mail.php';

class Zend_Service_Notification_Beeline
{
	public static function notify($phone, $text)
	{
		try {
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyText($text);         
			$mail->setFrom('example@mail.com');
			$mail->addTo($phone . '@sms.beemail.ru');
			$mail->setSubject('No subject');
			$mail->send();
		} catch (Exception $e) {}
	}
}