<?php
$secret_salt = 'T35t1ng54lt';
$timestamp = time();
$user = 'tesparadiso';
$email = 'test@paradiso.dev';
$newuser = 1;
$cohortname = '99999';
$fn = 'Test';
$ln = 'Paradiso';
$city = 'CA';
$country = 'US';

$token = crypt($timestamp.$user.$email,$secret_salt);

$url = 'http://lms.astm.org/auth/token/index.php';

$sso_url = $url.'?user='.$user.'&token='.$token.'&timestamp='.$timestamp.'&email='.$email.'&newuser='.$newuser.'&cohortname='.$cohortname.'&fn='.$fn.'&ln='.$ln.'&city='.$city.'&country='.$country;

header("Location: ".$sso_url);

/*
echo "<pre>";
print_r($sso_url);
echo "</pre>";
*/