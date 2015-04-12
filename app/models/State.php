<?php

class State extends BaseModel
{
    protected $table      = "state";
    protected $primaryKey = "stateID";


    public function getCountryId()
    {
        return $this->attributes['countryID'];
    }

    public function scopeWhereCode($query, $code) 
    {
        return $query->where($this->table . '.stateCode', '=', $code);
    }

}