<?php
/**
 * Mail
 *	class tien ich dung cho vien send mail.
 * 	Tien ich nay se su dung open source PHPMailer {@link http://phpmailer.worxware.com/}
 */
class Mail {
	/**
	 * Subject
	 *	tieu de email
	 *
	 * @var string
	 */
	public $subject = '';

	/**
	 * Content
	 *	HTML string
	 *
	 * @var string
	 */
	public $content;

	/**
	 * To Email
	 * 	Email nguoi nhan
	 *
	 * @var string
	 */
	public $toEmail;

	/**
	 * To Name
	 * 	Ten nguoi nhan
	 *
	 * @var string
	 */
	public $toName;


	/**
	 * Form Name
	 * 	ten nguoi gui
	 *
	 * @var string
	 */
	public $fromName = '';

	/**
	 * From Email
	 * 	email nguoi gui
	 *
	 * @var string
	 */
	public $fromEmail = '';

	/**
	 * Constructor
	 *
	 * @param Array $info. Thong tin cua cac email can gui
	 */
	public function __construct($info = array()) {

	}

	/**
	 * Send
	 * 	send email
	 *
	 * @param string $services localhost/gmail
	 * @param int $key -1 => random
	 * @param Array $info, cac thong tin noi dung cua email
	 * @return boolean
	 * @throws phpmailerException neu viec gui mail gap loi
	 * @throws SystemException
	 */
	private function send() {
		require_once dirname(__FILE__).DS.'phpmailer'.DS.'class.phpmailer.php';
		$mail = new PHPMailer(true);
		$mail->CharSet ='utf-8';
		$mail->IsSMTP();
		try {
			$config = array(
			    'user' => 'no-reply',
			    'password' => 'JOC@MAIL',
			    'replyMail' => 'no-reply@xahoi.com.vn',
			    'replyName' => 'Xahoi.com.vn',
			    'SMTPAuth' => 0,
			    'lowest_priority' => 10,
			    'maximum_time' => 5,
				'services'	=> 'gmail'
				);
				if ($config['services'] == 'gmail') { // Gui email qua server cua gmail
					$mail->SMTPAuth   = true;				// enable SMTP authentication
					//$mail->SMTPSecure = "ssl";				// sets the prefix to the servier
					$mail->Host       = "ssl://smtp.gmail.com";	// sets GMAIL as the SMTP server
					$mail->Port       = 465;				// set the SMTP port
					$mail->Username   = 'htnamdd@gmail.com';			// GMAIL username
					$mail->Password   = 'dinhducnam';			// GMAIL password
				}
				else { // Gui email su dung server noi bo
					$mail->Host		= '';	// sets GMAIL as the SMTP server
					$mail->SMTPAuth	= (boolean) $config['SMTPAuth'];	// enable SMTP authentication
				}
				if ($this->fromEmail == '') {
					$this->fromEmail = $config['replyMail'];
				}
				if ($this->fromName == '') {
					$this->fromName = $config['replyName'];
				}
				$mail->From       = $this->fromEmail;
				$mail->FromName   = $this->fromName;
				$mail->Subject    = $this->subject;
				$mail->AltBody    = strip_tags($this->content); //Text Body
				$mail->WordWrap   = 50; // set word wrap
				$mail->MsgHTML($this->content);
				$mail->AddReplyTo($mail->From, $mail->FromName);
				$mail->AddAddress($this->toEmail, $this->toName);
				$mail->IsHTML(true); // send as HTML
				// send
				$mail->Send();
		}
		catch (phpmailerException $pme) {
			if (IN_DEBUG) {
				echo 'Sending mail fails to '
				. $this->toEmail .' using ' . $services .'!', 'system.utils.mail';
			}
			return false;
		}

		return true;
	}

	/**
	 * sendEmail
	 * 	send Email
	 *	@param string $toEmail: Email nhận
	 *	@param string $toName: Người  nhận
	 *	@param string $subject: Tiêu đề email
	 *	@param string $content: Nội dung email
	 *	@param string $useSMTP: SMTP gửi
	 *	@param string $From: email gửi
	 *	@param string $FromName: người gửi
	 */
	public function sendEmail($toEmail,$toName,$subject,$content,$From='',$FromName='')
	{
		if (!$toEmail)
		return 'EMAIL_EMPTY';
		if (!$subject)
		return 'SUBJECT EMPTY';
		if (!$content)
		return 'CONTENT EMPTY';
		$this->toEmail =$toEmail;
		$this->toName = $toName;
		$this->subject =$subject;
		$this->content =$content;
		if ($From!='')
		$this->From=$From;
		if ($FromName!='')
		$this->FromName=$FromName;
		return $this->send();
	}
}