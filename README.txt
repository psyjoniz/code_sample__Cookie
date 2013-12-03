Code Sample : Cookie

A basic PHP class for Cookies.  Handles complex items.

Example of use :

<?php

include_once('Cookie.class.php');

//use optional namespace
$oCookie = new Cookie('my_namespace');

//set (permanent)
$oCookie->set('cookie_name', 'cookie_value');

//get
echo('cookie_name : ' . $oCookie->get('cookie_name') . '<br />');

//remove a cookie
$oCookie->remove('cookie_name');

//remove all cookies
$oCookie->removeAll();
