<?php

class Depot extends BaseModel
{
	protected $table      = "depot";
	protected $primaryKey = "depotId";

	public function getDepotCode()
	{
		return $this->attributes['depotCode'];
	}

    public function scopeGetGroupedDepotCode($query, $code)
    {
		return $query->where($this->table . '.city', '=', $code)->groupBy("depotCode","city");        
    }
}


