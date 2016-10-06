<?php
session_start()SiteSample.bak("simple-php-captcha/simple-php-captcha.php");
session_cache_limiter('nocache');
header('Expires: ' . gmdate('r', 0));

header('Content-type: application/json');

if (strtolower($_POST["captcha"]) == strtolower($_SESSION['captcha']['code'])) {
	$arrResult = array ('response'=>'success');
} else {
	$arrResult = array ('response'=>'error');
}

echo json_encode($arrResult);
?>