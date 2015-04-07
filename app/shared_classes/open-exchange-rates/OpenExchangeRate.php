<?php

class OpenExchangeRate
{
	private $appId;
	private $apiUrl;
	private $latestJson;
	private $currenciesJson;
	private $baseCurrency;

	public function __construct($appId, $baseCurrency = 'USD')
	{
		$this->appId          = $appId;
		$this->apiUrl         = 'http://openexchangerates.org/api/';
		$this->baseCurrency   = $baseCurrency;
		$this->latestJson     = 'latest.json';
		$this->currenciesJson = 'currencies.json';
	}

	public function getConversionRates($baseCurrency = '')
	{
		$baseCurrency = $baseCurrency ?: $this->baseCurrency;

		$latestConversionUrl = $this->apiUrl . $this->latestJson . '?app_id=' . $this->appId;

		$curlHandler = curl_init();
		$result      = array();

		$curlOptions = array(
			CURLOPT_URL            => $latestConversionUrl . '&base=' . $baseCurrency,
			CURLOPT_RETURNTRANSFER => true,
		);

		curl_setopt_array($curlHandler, $curlOptions);

		$response = curl_exec($curlHandler);

		if ($response === false) {
			$result['status'] = curl_error($curlHandler);

			return $result; 
		} 

		curl_close($curlHandler);

		$jsonResponse = json_decode($response, true);

		if (isset($jsonResponse['error'])) {
			$result['status'] = $jsonResponse['description'];

			return $result;
		}

		$result['status'] = 'OK';
		$result['data']   = $jsonResponse;

		return $result;
	}

	public function getCurrencies()
	{
		$currenciesUrl = $this->apiUrl . $this->currenciesJson . '?app_id=' . $this->appId;

		$curlHandler = curl_init();
		$result      = array();

		$curlOptions = array(
			CURLOPT_URL            => $currenciesUrl,
			CURLOPT_RETURNTRANSFER => true,
		);

		curl_setopt_array($curlHandler, $curlOptions);

		$response = curl_exec($curlHandler);

		if ($response === false) {
			$result['status'] = curl_error($curlHandler);

			return $result; 
		} 

		curl_close($curlHandler);

		$jsonResponse = json_decode($response, true);

		if (isset($jsonResponse['error'])) {
			$result['status'] = $jsonResponse['description'];

			return $result;
		}

		$result['status'] = 'OK';
		$result['data']   = $jsonResponse;

		return $result;
	}
}