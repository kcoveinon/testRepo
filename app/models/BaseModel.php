<?php

class BaseModel extends Eloquent {
	public static function getTableName()
	{
		return with(new static)->getTable();
	}
}