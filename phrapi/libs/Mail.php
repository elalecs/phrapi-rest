<? defined("PHRAPI") or die("Direct access not allowed!");

final class Mail{

	static function makeEmail($config)
	{
		include_once 'libs/mail/PHPMailer.php';
		$mail = new mail_PHPMailer();


		$config_smtp = $GLOBALS['config']['smtp'];

		$mail->Host     = $config_smtp['host'];
		$mail->SMTPAuth = true;
		$mail->Username = $config_smtp['user']; //$this->config->smtp->user;
		$mail->Password = $config_smtp['pass']; //$this->config->smtp->pass;

		$mail->From     = $config_smtp['sender_mail']; //$this->config->smtp->sender_mail;
		$mail->FromName = $config_smtp['sender_name']; //$this->config->smtp->sender_name;

		if (!is_array($config))
			return false;

		if (isset($config['receivers']))
			foreach($config['receivers'] as $receiver) {
				$tmp = explode("<", $receiver);
				$mail->AddAddress(str_replace(">", "", $tmp[1]), trim($tmp[0]));
			}

		if (isset($config['receiver']))
		{
			$tmp = explode("<", $config['receiver']);
			$mail->AddAddress(str_replace(">", "", $tmp[1]), trim($tmp[0]));
		}

		$mail->Subject = getValueFrom($config, 'subject');

		$mail->Body = getValueFrom($config, 'body');

		$mail->IsHTML(getValueFrom($config, 'is_html', true));

		return $mail;
	}

}