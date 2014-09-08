<?php
	$installer = $this;
	$o = Mage::getModel('admin/session');
	if(is_object($o) && method_exists($o, 'refreshAcl') && is_callable(array($o,'refreshAcl')))
	{
	    $o->refreshAcl();
	}