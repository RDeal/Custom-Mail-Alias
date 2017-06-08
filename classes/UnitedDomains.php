<?php

/**
 * United Domains Class
 * @author: axi
 * @version: 1.0
 */
class UnitedDomains
{
	private static $_COOKIE = 'cookie.txt';
	private $_user;
	private $_pass;
	private $_curl;

	/**
	 * UnitedDomains constructor.
	 * @set string $_user
	 * @set string $_pass
	 */
	public function __construct()
	{
		$this->_user = 'user-email';
		$this->_pass = 'password';
	}

	/**
	 * @param $address
	 * @return int 1 for success : 2 for already exist : 0 for fail
	 */
	public function createAlias($address)
	{
		$page = $this->getMailboxPage();
		if (!$page) return 0;
		if (preg_match("/$address/i",$page)) return 2;

		curl_setopt ($this->_curl, CURLOPT_URL, "https://www.united-domains.de/portfolio/mailaccount/createalias/");
		curl_setopt ($this->_curl, CURLOPT_POSTFIELDS, $this->getAliasData($page, $address));
		$resultPage = curl_exec ($this->_curl);

		if (preg_match('/erfolgreich angelegt/i', $resultPage)) return 1;
	}

	/**
	 * Perform a cURL session
	 * @return mixed result page
	 */
	private function getMailboxPage()
	{
		if(!$this->login()) return 0;

		//TODO: make domainid & accountname variable
		$url = "https://www.united-domains.de/portfolio/mailaccount/index/domainid/4884871/accountname/axi-wtf-0001";
		curl_setopt($this->_curl, CURLOPT_URL, $url);
		$resultPage = curl_exec($this->_curl);

		//TODO: check result page before return
		//if (preg_match('/<span class="grey666">Postfach:<\/span>/i',$resultPage))
		return $resultPage;
	}

	/**
	 * Login to United Domains website
	 * Perform a cURL session
	 * @return int 1 for success : 0 for fail
	 */
	private function login()
	{
		if(!$this->_curl) $this->initCurl();

		$page = $this->getLoginPage();
		if(!$page) return 0;

		$loginData = $this->getLoginData($page);

		curl_setopt ($this->_curl, CURLOPT_POSTFIELDS, $loginData);
		$resultPage = curl_exec ($this->_curl);

		//TODO: error handling
		//TODO: check for better solution
		if (preg_match('/<span>Portfolio<\/span>/i',$resultPage)) return 1;
	}

	/**
	 * Initialize a cURL session
	 * ..set various parameters
	 * @set cURL handle $_ch
	 */
	private function initCurl()
	{
		$this->_curl = curl_init();
		//TODO: delete useless lines
		curl_setopt ($this->_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($this->_curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt ($this->_curl, CURLOPT_TIMEOUT, 4000);
		curl_setopt ($this->_curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($this->_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($this->_curl, CURLOPT_COOKIEJAR, self::$_COOKIE);
		curl_setopt ($this->_curl, CURLOPT_COOKIEFILE, self::$_COOKIE);
		curl_setopt ($this->_curl, CURLOPT_POST, 1);
	}

	/**
	 * Perform a cURL session
	 * @return mixed result page
	 */
	private function getLoginPage()
	{
		$url = "https://www.united-domains.de/login";
		curl_setopt ($this->_curl, CURLOPT_URL, $url);
		//TODO: check result page before return
		return curl_exec($this->_curl);
	}

	/**
	 * @param $page
	 * @return string login data
	 */
	private function getLoginData($page)
	{
		$csrf = $this->getLoginCSRF($page);
		return "csrf=".$csrf."&selector=login"."&email=".$this->_user."&pwd=".$this->_pass."&submit=Login";
	}

	/**
	 * Parse login page for Cross-Site-Request-Forgery key
	 * @param $page
	 * @return string csrf-key
	 */
	private function getLoginCSRF($page)
	{
		$pattern = '/<input type="hidden" name="csrf" value="(.*)"/';
		preg_match_all($pattern, $page, $matches);
		return $matches[1][2];
	}

	/**
	 * @param $page
	 * @param $address
	 * @return string alias data
	 */
	private function getAliasData($page, $address)
	{
		$csrf = $this->getAliasCSRF($page);
		//TODO: make domainid & accountname variable
		return "csrf=".$csrf."&domainid=4884871"."&accountname=axi-wtf-0001"."&formid=aliasStorage"."&form_name=saveNewAlias"."&alias=".$address;
	}

	/**
	 * Parse mailbox page for Cross-Site-Request-Forgery key
	 * @param $page
	 * @return string csrf-key
	 */
	private function getAliasCSRF($page)
	{
		$pattern = '/<form action="\/portfolio\/mailaccount\/createalias\/" id="aliasStorage" method="post"><input type="hidden" name="csrf" value="(.*)"/';
		preg_match_all($pattern, $page, $matches);
		return $matches[1][0];
	}
}