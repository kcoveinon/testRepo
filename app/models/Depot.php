<?php

class Depot extends BaseModel
{
    protected $table      = "depot";
    protected $primaryKey = "depotId";

    public function getId()
    {
        return $this->attributes['depotID'];
    }

    public function setId($value)
    {
        $this->attributes['depotID'] = $value;
    }

    public function getSupplierId()
    {
        return $this->attributes['supplierID'];
    }

    public function setSupplierId($value)
    {
        $this->attributes['supplierID'] = $value;
    }

    public function getCountryId()
    {
        return $this->attributes['countryID'];
    }

    public function setCountryId($value)
    {
        $this->attributes['countryID'] = $value;
    }

    public function getCode()
    {
        return $this->attributes['depotCode'];
    }

    public function setCode($value)
    {
        $this->attributes['depotCode'] = $value;
    }

    public function getIsAirport()
    {
        return $this->attributes['isAirport'];
    }

    public function setIsAirport($value)
    {
        $this->attributes['isAirport'] = $value;
    }

    public function getName()
    {
        return $this->attributes['depotName'];
    }

    public function setName($value)
    {
        $this->attributes['depotName'] = $value;
    }

    public function getAddress()
    {
        return $this->attributes['address'];
    }

    public function setAddress($value)
    {
        $this->attributes['address'] = $value;
    }

    public function getCity()
    {
        return $this->attributes['city'];
    }

    public function setCity($value)
    {
        $this->attributes['city'] = $value;
    }

    public function getPostCode()
    {
        return $this->attributes['postCode'];
    }

    public function setPostCode($value)
    {
        $this->attributes['postCode'] = $value;
    }

    public function getPhoneNumber()
    {
        return $this->attributes['phoneNumber'];
    }

    public function setPhoneNumber($value)
    {
        $this->attributes['phoneNumber'] = $value;
    }

    public function getExtraInfo()
    {
        return $this->attributes['extraInfo'];
    }

    public function setExtraInfo($value)
    {
        $this->attributes['extraInfo'] = $value;
    }

    public function getPopularity()
    {
        return $this->attributes['popularity'];
    }

    public function setPopularity($value)
    {
        $this->attributes['popularity'] = $value;
    }

    public function getLatitude()
    {
        return $this->attributes['latitude'];
    }

    public function setLatitude($value)
    {
        $this->attributes['latitude'] = $value;
    }

    public function getLongitude()
    {
        return $this->attributes['longitude'];
    }

    public function setLongitude($value)
    {
        $this->attributes['longitude'] = $value;
    }

    public function getAccuracy()
    {
        return $this->attributes['accuracy'];
    }

    public function setAccuracy($value)
    {
        $this->attributes['accuracy'] = $value;
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
}


