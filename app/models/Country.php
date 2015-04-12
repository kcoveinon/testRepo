<?php

class Country extends BaseModel
{
	protected $table      = 'country';
	protected $primaryKey = 'countryID';

	public function getId()
	{
	    return $this->attributes['countryID'];
	}

	public function setId($value)
	{
	    $this->attributes['countryID'] = $value;
	}

	public function getCurrencyId()
	{
	    return $this->attributes['currencyID'];
	}

	public function setCurrencyId($value)
	{
	    $this->attributes['currencyID'] = $value;
	}

	public function getName()
	{
	    return $this->attributes['countryName'];
	}

	public function setName($value)
	{
	    $this->attributes['countryName'] = $value;
	}

	public function getCode()
	{
	    return $this->attributes['countryCode'];
	}

	public function setCode($value)
	{
	    $this->attributes['countryCode'] = $value;
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

	public function getIsShowCurrency()
	{
	    return $this->attributes['isShowCurrency'];
	}

	public function setIsShowCurrency($value)
	{
	    $this->attributes['isShowCurrency'] = $value;
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
		return $query->where($this->table . '.countryCode', '=', $code);
	}
}