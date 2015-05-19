<?php
class Moogento_Pickpack_Model_Getlabels
{

    /**
     * Options getter
     *
     * @return array
     */
	function getLabels() {
    	
		$ftp_server = 'covtxhou02-71.covalentworks.com';
		$ftp_user_name = 'Node1185';
		$ftp_user_pass = 'Fu3230';
		$remote_location = 'Inbox/Label';
	
	    $connection = ftp_connect($ftp_server);
		$login = ftp_login($connection, $ftp_user_name, $ftp_user_pass);
		ftp_pasv($connection, true);
		ftp_chdir($connection, $remote_location);
		
		$contents = ftp_nlist($connection, ".");
		
		foreach ($contents as $file) {
			ftp_get($connection, Mage::getBaseDir('base') . '/misc/import/labels/' . $file, $file, FTP_BINARY);
			ftp_rename($connection, $file, 'Archive/' . $file);
		}
		
		ftp_close($connection);
    }

}