<?php 

require_once __DIR__.'/iniatialization.php';





/* Testing identity */


$description = "Test identity";
$debtorReference = "1234";
$dueDateTime = null; // set it automatically to two weeks in advance.

// To create AND perform a request:
$request = $bluem_object->CreateIdentityRequest(
	["AddressRequest","BirthDateRequest"],
	$description,
	$debtorReference,
	"http://localhost/code/etc/"
);
var_dump($request);
echo $request->xmlString();
$response = $bluem_object->PerformRequest($request);
var_dump($response);



