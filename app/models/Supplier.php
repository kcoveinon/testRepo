<?php

class Supplier extends BaseModel
{
	protected $table = 'supplier';

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
}