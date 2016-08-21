<?php
namespace Friday\Core\Mail;

/**
 * PHPMailer exception handler
 * @package PHPMailer
 */
class MailException extends \Exception
{
	/**
	 * Prettify error message output
	 * @return string
	 */
	public function errorMessage()
	{
		$errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
		return $errorMsg;
	}
}
 
?>