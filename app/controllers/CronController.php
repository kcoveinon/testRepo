<?php

class CronController extends BaseController
{
	public function getUpdateCurrencyRates()
	{
		ini_set('max_execution_time', 180);

		$openExchangeRateAppId = Config::get('open_exchange_rate.appId');

		$openExchangeRate = new OpenExchangeRate($openExchangeRateAppId);

		$exchangeRates = $openExchangeRate->getConversionRates();

		if ($exchangeRates['status'] == 'OK') {
			$rates = $exchangeRates['data']['rates'];

			DB::transaction(function () use ($rates) {
				foreach ($rates as $code => $rate) {
					Currency::whereCode($code)->update(array('currencyRate' => $rate));
				}
			});
		}
	}

	public function updateDepotTable()
	{
		return View::make('prototype.async_update_depots');
	}

}