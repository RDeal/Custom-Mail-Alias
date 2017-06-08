<?php

/**
 * Mail Class
 * @author: axi
 * @version: 1.0
 */
class Mail
{
	private $_serv;
	private $_user;
	private $_pass;
	private $_imap;
	private $_overview;

	/**
	 * Mail constructor.
	 */
	public function __construct()
	{
		$this->_serv = '{imap.udag.de/imap}INBOX';
		$this->_user = 'username';
		$this->_pass = 'password';
	}

	/**
	 * Create a html form with mail content
	 * @param $address
	 * @return string panel-body
	 */
	public function getPanelBody($address)
	{
		$this->openIMAP();
		$this->getOverview($address);
		if(!$this->_overview) return '<div class="panel-body">Please refresh to receive emails.</div>';
		$panelBody = '<div class="panel-body">';
		foreach ($this->_overview as $mail)
		{
			$panelBody .=
				'<div>'.
					'<pre>'.
						'<div>'.
							'<p class="pull-right"><strong>' . date('d.m.Y H:i',$mail->udate) . '</strong></p>'.
							'<h4><strong>' . $mail->from . '</strong></h4>'.
						'</div>'.
						'<p><strong>' . $mail->subject . '</strong></p>'.
						'<hr style="border-top:1px solid #ccc;">'.
						$this->getMailBody($mail->uid).
					'</pre>'.
				'</div>';
		}
		$panelBody .= '</div>';
		$this->closeIMAP();
		return $panelBody;
	}

	/**
	 * Open an IMAP stream to a mailbox
	 * @set IMAP-stream $_imap
	 */
	private function openIMAP()
	{
		try
		{
			$this->_imap = imap_open($this->_serv, $this->_user, $this->_pass, OP_READONLY);
		}
		catch(Exception $e)
		{
			$this->sendError('openIMAP Error');
		}
	}

	/**
	 * Read an overview of the information in the headers
	 * @set array $_overview
	 */
	private function getOverview($address)
	{
		$overview = imap_fetch_overview($this->_imap, "1:*", FT_UID);
		$this->_overview = array();
		foreach ($overview as $mail)
		{
			if(preg_match("/$address/i",$mail->to)) //TODO: timestamp < 5 min ($mail->udate > timestamp()-OFFSET)
			{
				array_push($this->_overview, $mail);
			}
		}
		rsort($this->_overview);
	}

	/**
	 * Get the mail body of a given mail
	 * @param int $uid
	 * @return string MailBody
	 */
	private function getMailBody($uid)
	{
		$body = imap_body($this->_imap, $uid, FT_UID);
		//TODO: refactor (new mime fetch function)
		//TODO: decode function
		//1 - 8bit - no need
		//2 - Binary - no need
		//3 - Base64 - base64_decode()
		//4 - Quoted-Printable - quoted_printable_decode()
		if (preg_match('/This is a multipart message in MIME format/', $body))
		{
			$body = imap_fetchbody($this->_imap, $uid, 1.1, FT_UID | FT_PEEK);
			$body = quoted_printable_decode($body);
			$body = utf8_encode($body);
		}
		else
		{
			//$body = htmlentities(stripslashes($body));
			$body = quoted_printable_decode($body);
			$body = utf8_encode($body);
		}
		$body = $this->makeLinksClickable($body);
		return $body;
	}

	/**
	 * Close an IMAP stream $_imap
	 */
	private function closeIMAP()
	{
		try
		{
			imap_close($this->_imap);
		}
		catch(Exception $e)
		{
			$this->sendError('closeIMAP Error');
		}
	}

	/**
	 * Error Handling
	 * @param $subject
	 */
	private function sendError($subject)
	{
		$to = 'error@axi.wtf';
		$message = '';
		$headers = 'From: mail@axi.wtf';
		mail($to, $subject, $message, $headers);
	}

	/**
	 *
	 * @param $text
	 * @return mixed
	 */
	private function makeLinksClickable($text)
	{
        return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $text);
	}
}