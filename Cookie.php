<?php

/**
 * 2013-12-06 - Jesse L Quattlebaum (psyjoniz@gmail.com)
 * Example of use for Cookie.class.php
 */

require_once('Cookie.class.php');

$sURL    = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . (false !== strpos($_SERVER['REQUEST_URI'], '?') ? strtok($_SERVER['REQUEST_URI'], '?') : $_SERVER['REQUEST_URI']);
$sErrorMessage = false;
$mCookie = false;

try {
	$oCookie = new Cookie(isset($_REQUEST['namespace']) ? $_REQUEST['namespace'] : true);
	//add a cookie based on the name and value inputs submitted by end-user
	if(isset($_REQUEST['set'])) {
		$oCookie->set($_REQUEST['name'], $_REQUEST['value']);
	}
	//get a cookie
	if(isset($_REQUEST['get'])) {
		$mCookie = $oCookie->get($_REQUEST['name']);
	}
	//add oCookie to itself
	if(isset($_REQUEST['addobject'])) {
		$oCookie->set('oCookie', $oCookie);
	}
	//eat a cookie
	if(isset($_REQUEST['remove'])) {
		$oCookie->remove($_REQUEST['name']);
	}
	//eat all the cookies
	if(isset($_REQUEST['removeall'])) {
		$oCookie->removeAll();
	}
} catch(Exception $exception) {
	$sErrorMessage = $exception->getMessage();
}

echo('Cookie | <a href="' . $sURL . '">Refresh</a><hr />');

if(false !== $sErrorMessage) {
?>
<fieldset style="background-color: #ffeeee; border: 1px solid #000000;">
	<legend style="background-color: #ffaaaa; padding: 2px; border: 1px solid #000000;">&nbsp;Error&nbsp;</legend>
	<?php echo($sErrorMessage); ?>
</fieldset>
<br />
<?php
}
if(false !== $mCookie) {
?>
<fieldset style="border: 1px solid #000000;">
	<legend style="background-color: #00ffff; padding: 2px; border: 1px solid #000000;">&nbsp;(<?php echo($_REQUEST['namespace']); ?>) <?php echo($_REQUEST['name']); ?>&nbsp;</legend>
	<pre><?php echo(print_r($mCookie, true)); ?></pre>
</fieldset>
<br />
<?php
}
?>
<fieldset style="border: 1px solid #000000;">
	<legend style="background-color: #00ffff; padding: 2px; border: 1px solid #000000;">&nbsp;Data&nbsp;</legend>
	<?php echo('<pre>' . print_r($_COOKIE, true) . '</pre>'); ?>
</fieldset>
<br />
<fieldset style="border: 1px solid #000000;">
	<legend style="background-color: #00ffff; padding: 2px; border: 1px solid #000000;">&nbsp;Interaction&nbsp;</legend>
	<form action="<?php echo($sURL); ?>?set=yes" method="POST">
		Namespace
		<input name="namespace" value="<?php echo($oCookie->getNamespace()); ?>" /><br />
		Name
		<input name="name" /><br />
		Value
		<input name="value" />
		<button>Add</button>
	</form>
	<form action="<?php echo($sURL); ?>?get=yes" method="POST">
		Namespace
		<input name="namespace" value="<?php echo($oCookie->getNamespace()); ?>" /><br />
		Name
		<input name="name" />
		<button>Get</button>
	</form>
	<form action="<?php echo($sURL); ?>?remove=yes" method="POST">
		Namespace
		<input name="namespace" value="<?php echo($oCookie->getNamespace()); ?>" /><br />
		Name
		<input name="name" />
		<button>Remove</button>
	</form>
	<form action="<?php echo($sURL); ?>?addobject=yes" method="POST">
		Namespace
		<input name="namespace" value="<?php echo($oCookie->getNamespace()); ?>" />
		<button>Add Object</button>
	</form>
	<form action="<?php echo($sURL); ?>?removeall=yes" method="POST">
		Namespace
		<input name="namespace" value="<?php echo($oCookie->getNamespace()); ?>" />
		<button>Remove All</button>
	</form>
</fieldset>
