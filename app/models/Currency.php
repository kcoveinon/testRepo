<?php

class Currency extends Eloquent
{
	protected $table      = 'currency';
	protected $primaryKey = 'currencyID';

	public function getId() 
	{
		return $this->attributes['currencyID'];
	}

	public function setId($value) 
	{
		$this->attributes['currencyID'] = $value;
	}

	public function getCode() 
	{
		return $this->attributes['currencyCode'];
	}

	public function setCode($value) 
	{
		$this->attributes['currencyCode'] = $value;
	}

	public function getRate() 
	{
	    return $this->attributes['currencyRate'];
	}

	public function setRate($value) 
	{
	    $this->attributes['currencyRate'] = $value;
	}

	public function getCreatedAt() 
	{
		return $this->attributes['created_at'];
	}

	public function setCreatedAt($value) 
	{
		$this->attributes['created_at'] = $value;
	}

	public function getUpdatedAt() 
	{
		return $this->attributes['updated_at'];
	}

	public function setUpdatedAt($value) 
	{
		$this->attributes['updated_at'] = $value;
	}

	public function getDeletedAt() 
	{
		return $this->attributes['deleted_at'];
	}

	public function setDeletedAt($value) 
	{
		$this->attributes['deleted_at'] = $value;
	}

	public function getIsDeleted() 
	{
		return $this->attributes['isDeleted'];
	}

	public function setIsDeleted($value) 
	{
		$this->attributes['isDeleted'] = $value;
	}

	public function getCreatedBy() 
	{
		return $this->attributes['createdBy'];
	}

	public function setCreatedBy($value) 
	{
		$this->attributes['createdBy'] = $value;
	}

	public function getUpdatedBy() 
	{
		return $this->attributes['updatedBy'];
	}

	public function setUpdatedBy($value) 
	{
		$this->attributes['updatedBy'] = $value;
	}

	public function getDeletedBy() 
	{
		return $this->attributes['deletedBy'];
	}

	public function setDeletedBy($value) 
	{
		$this->attributes['deletedBy'] = $value;
	}

	/* scopes */
	public function scopeWhereCode($query, $code)
	{
		return $query->where($this->table . '.currencyCode', '=', $code);
	}

	public function scopeWhereCodeIn($query, $codes) 
	{
		return $query->whereIn($this->table . '.currencyCode', $codes);
	}

	/* static */
	public static function convert($value, $from, $to, $currencyRates = array())
	{
		$convertedValue = 0;

		if (!empty($currencyRates)) {
			$fromRate = $currencyRates[$from];
			$toRate   = $currencyRates[$to];
		} else {
			$fromCurrency = self::whereCode($from)->first();
			$toCurrency   = self::whereCode($to)->first();

			$fromRate = $fromCurrency->getRate();
			$toRate   = $toCurrency->getRate();
		}

		$convertedValue = (($value / $fromRate) * $toRate);

		return $convertedValue;
	}
}