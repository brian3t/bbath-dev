	<?php
	/* memcache is running */
	$test1 = memcache_connect('127.0.0.1',11211);
//	$test1 = array();
	echo gettype($test1);
	// object
	echo get_class($test1);
	// Memcache

	/* memcached is stopped */
	//$test2 = memcache_connect('127.0.0.1',11211);

	/*
	Notice: memcache_connect(): Server 127.0.0.1 (tcp 11211) failed with: A connection attempt failed because the connected party did not properly respond after a period of time, or established connection failed because connected host has failed to respond.
	 (10060) in C:\Program Files\Support Tools\- on line 1

	Warning: memcache_connect(): Can't connect to 127.0.0.1:11211, A connection attempt failed because the connected party did not properly respond after a period of time, or established connection failed because connected host has failed to respond.
	 (10060) in C:\Program Files\Support Tools\- on line 1
	*/

	echo gettype($test2);
	// boolean
	echo $test2===false;
	// 1
	?>
