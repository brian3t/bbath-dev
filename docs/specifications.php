<?

session_start();

// Define your username and password
$username = array("1", "brian", "anna", "aloka", "tom", "corey",);
$password = array("a", "brian@bloomingbath.com", "anna@bloomingbath.com", "aloka@crowdedcoop.com", "tom@eggheadventures.com", "corey@blacksmithint.com",);

if ($_POST['login'] == 'yes') {

	$_SESSION['user'] = $_POST['txtUsername'];
	$_SESSION['pass'] = $_POST['txtPassword'];
}
if ((in_array($_SESSION['user'], $username, false)) && (in_array($_SESSION['pass'], $password, false))) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Blooming Bath - Manufacturing Specification Documents</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Manufacturing Specifications, Blooing Baby Specifications" />
<meta name="keywords" content="Blooiming Baby, Blooming Bath, Manufacturing Specification, Design Documentation" />
<style>
.head {margin:5px auto 50px auto;}
p, body {font-family:Arial, Sans-serif; font-size:20px; Color:#444444; background:#e2e9f6;}
ul{list-style:none; margin-bottom:60px;font-size:25px;}
li a{ line-height:10px;font-size:20px;}
.body{background:#eeeeee;}
.list{width: 600px;margin-left: auto;margin-right: auto;padding: 25px 0;font-size:30px;}
.list ol{font-size:20px;}
</style>
</head>
<body>
  <div class="page">
<!-- Begin Header -->
    <div class="head">
      <div align="center"><a href="../index.html"><img src="../skin/frontend/default/bloomingbath/images/logo.png" alt="Blooming Bath - The Best Baby Bath Solution"></a></div>
    </div>
<!--  End Header  -->

    <div class="body">
	    <div class="list">Current Blooming Baby Specifications
			<ol>
				<li>Blooming Bath
					<ul>
						<li><a href="bb_prod_spec_20140127-rev_H.pdf">Blooming Bath Revision H, 1-27-14</a></li>
					</ul>
				</li>
				
				<li>Blooming Baby Carrier
					<ul>
						<li><a href="blooming-baby-carrier-4-10-15.pdf">Blooming Baby Carrier Revision 1, 4-13-15</a></li>
						<li></li>
					</ul>			
				</li>
				
				<li>Blooming Baby Hooded Towel
					<ul>
						<li><a href="hooded-towel-r2-4-13-15.pdf">Blooming Baby Hooded Towel,Revision 0 4-13-15</a></li>
						<li></li>
					</ul>
				</li>
				
				<li>Blooming Baby Pillow Bag
					<ul>
						<li><a href="blooming-baby-pillow-bag-4-13-15.pdf">Blooming Baby Pillow Bag Revision 0, 4-13-15</a></li>
						<li></li>
					</ul>
				</li>
				
				<li>Blooming Baby Changing Bag
					<ul>
						<li><a href="blooming-baby-changing-bag-4-13-15.pdf">Blooming Baby Changing Bag Revision 0, 4-13-15</a></li>
						<li></li>
					</ul>
				</li>	
			</ol>
	    </div>
     </div>
   </div>
  </body>
</html>

<? } else { ?>
<div style="width:300px; margin:10px auto; font-family:sans-serif;">
<h1>Blooming Baby Login</h1>

<form name="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <p><label for="txtUsername">First Name:</label>
    <br /><input style="width:250px; height:30px; font-size:18px;"class="password" type="text" title="Enter your Username" name="txtUsername" /></p>

    <p><label for="txtpassword">Email Address:</label>
    <br /><input style="width:250px; height:30px; font-size:18px;" type="password" title="Enter your password" name="txtPassword" /></p>
		  <input type="hidden" name="login" value="yes"/>
    <p><input style="height:40px; width:250px; font-size:30px; margin:0 auto; padding:5px 10px;" type="submit" name="Submit" value="Login" /></p>

</form>
</div>
<? } ?>