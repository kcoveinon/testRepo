<?php

class Location extends BaseModel {
	protected $table      = "location";
	protected $primaryKey = "locationID";

	public function getId()
	{
		return $this->attributes['locationID'];
	}
	
	public function setId($value)
	{
		$this->attributes['locationID'] = $value;
	}
	
	public function getCountryId()
	{
		return $this->attributes['countryID'];
	}
	
	public function setCountryId($value)
	{
		$this->attributes['countryID'] = $value;
	}
	
	public function getStateId()
	{
		return $this->attributes['stateID'];
	}
	
	public function setStateId($value)
	{
		$this->attributes['stateID'] = $value;
	}
	
	public function getName()
	{
		return $this->attributes['locationName'];
	}
	
	public function setName($value)
	{
		$this->attributes['locationName'] = $value;
	}
	
	public function getCity()
	{
		return $this->attributes['city'];
	}
	
	public function setCity($value)
	{
		$this->attributes['city'] = $value;
	}
	
	public function getPopularity()
	{
		return $this->attributes['locationPopularity'];
	}
	
	public function setPopularity($value)
	{
		$this->attributes['locationPopularity'] = $value;
	}
	
	public function getIsShow()
	{
		return $this->attributes['isShow'];
	}
	
	public function setIsShow($value)
	{
		$this->attributes['isShow'] = $value;
	}
	
	public function getIsIndex()
	{
		return $this->attributes['isIndex'];
	}
	
	public function setIsIndex($value)
	{
		$this->attributes['isIndex'] = $value;
	}
	
	public function getIsLocationPage()
	{
		return $this->attributes['isLocationPage'];
	}
	
	public function setIsLocationPage($value)
	{
		$this->attributes['isLocationPage'] = $value;
	}
	
	public function getLocationLatitude()
	{
		return $this->attributes['locationLatitude'];
	}
	
	public function setLocationLatitude($value)
	{
		$this->attributes['locationLatitude'] = $value;
	}
	
	public function getLocationLongitude()
	{
		return $this->attributes['locationLongitude'];
	}
	
	public function setLocationLongitude($value)
	{
		$this->attributes['locationLongitude'] = $value;
	}
	
	public function getTypeID()
	{
		return $this->attributes['locationTypeID'];
	}
	
	public function setTypeID($value)
	{
		$this->attributes['locationTypeID'] = $value;
	}
	
	public function getGeoNameId()
	{
		return $this->attributes['locationGeoNameID'];
	}
	
	public function setGeoNameId($value)
	{
		$this->attributes['locationGeoNameID'] = $value;
	}
	
	public function getComment()
	{
		return $this->attributes['comment'];
	}
	
	public function setComment($value)
	{
		$this->attributes['comment'] = $value;
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
	
	public function scopeWhereId($query, $id)
	{
		return $query->where($this->table . '.' . $this->primaryKey, '=', $id);
	}
	

}
