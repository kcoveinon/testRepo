<?php

class Supplier extends BaseModel
{
	protected $table = 'supplier';

	public function getId()
	{
	    return $this->attributes['supplierID'];
	}

	/* scopes */
	public function scopeWhereValid($query)
	{
		return $query->whereNotDeleted();
	}

	public function scopeWhereNotDeleted($query)
	{
		return $query->where($this->table . '.isDeleted', '=', 0);
	}

	public function scopeWhereCode($query, $code)
	{
		return $query->where($this->table . '.supplierCode', '=', $code);
	}

	/* static */
	public static function isValid($code)
	{
		$exist = self::whereValid()->whereCode($code)->exists();

		return $exist;
	}

    public static function getSupplierIDByCode($supplierCode) 
    {
        $supplierID = Supplier::select('supplierID')->where('supplierCode', '=', $supplierCode)->where('isDeleted', '=', 0)->first();
        
        return $supplierID;
    }
}