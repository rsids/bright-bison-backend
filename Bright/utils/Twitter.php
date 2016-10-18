<?php
class Twitter {

	public function tweet($message) {
		$conn = Connection::getInstance();
		$sql = 'SELECT * FROM `twitter` ORDER BY id DESC LIMIT 0,1';
		$credentials = $conn -> getRow($sql);
		if(!$credentials)
			throw new Exception('No Twitter user registered');

		$twitterConnection = new TwitterOAuth(	CONSUMER_KEY,
												CONSUMER_SECRET,
												$credentials -> token,
												$credentials -> secret);

		$twitterConnection -> useragent = 'Fur-o-matic 1.0';
		$res = $twitterConnection->post('statuses/update', array('status' => $message));
		return $res;
	}

	/**
	 * Shortens an url
	 * @param string $url
	 * @return string Shortened url
	 */
	public function shorten($url) {
		$url = 'http://api.bit.ly/v3/shorten?login=' . BITLY_USER . '&apiKey=' . BITLY_APIKEY .'&longUrl=' . urlencode($url) . '&format=json';

		$this->http_info = array();

		$ci = $this -> setupCurl();
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);

		curl_close ($ci);
		if($response) {
			$res = json_decode($response);
			if($res -> status_code == 200) {
				// OK!
				return $res -> data -> url;
			}
		}
		return $response;
	}

	/**
	 * Sets up Twitter Connection and requests a token and url
	 * @return string A register url the user should visit.
	 */
	public function register() {
		$twitterConnection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
		$tok = $twitterConnection->getRequestToken(BASEURL . 'bright/actions/tw_callback.php');
		$request_link = $twitterConnection->getAuthorizeURL($tok);
	    $_SESSION['oauth_request_token'] = $token = $tok['oauth_token'];
	    $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];
		return $request_link;
	}

	/**
	 * Called by the callback from register.
	 */
	public function saveAuthorize() {

		if ((!isset($_SESSION['oauth_access_token'])) || ($_SESSION['oauth_access_token'])=='') {
			$twitterConnection = new TwitterOAuth(	CONSUMER_KEY,
													CONSUMER_SECRET,
													$_SESSION['oauth_request_token'],
													$_SESSION['oauth_request_token_secret']);
			$tok = $twitterConnection->getAccessToken($_GET['oauth_verifier']);
			$conn = Connection::getInstance();
			$sql = 'INSERT INTO `twitter` (`screenname`, `token`, `secret`) VALUES ' .
					"('" . Connection::getInstance() -> escape_string($tok['screen_name']) . "', '" . Connection::getInstance() -> escape_string($tok['oauth_token']) . "', '" . Connection::getInstance() -> escape_string($tok['oauth_token_secret']) . "')";
			$conn -> insertRow($sql);

			if($tok['screen_name'] == '') {
				echo 'Authorization failed, try again';
			} else {
				echo 'Authorization saved.';
			}
		}
	}

	protected function setupCurl() {
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, 'Fur-o-matic 1.0');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		return $ci;
	}

	protected function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			//$this->http_header[$key] = $value;
		}
		return strlen($header);
	}

}