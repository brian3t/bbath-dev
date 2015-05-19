<?php
	class Tommorris_Commercebug_Helper_Collector extends Tommorris_Commercebug_Helper_Abstract
	{
		static protected $items;
		static public function saveItem($key, $value)
		{
			self::$items[$key] = $value;
		}
	}