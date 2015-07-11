<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleGlacierBackups\SimpleGlacierBackups;
use Aws\Glacier\GlacierClient;

$client = new SimpleGlacierBackups('testvault',array(
	'profile'=>'glacier',
	'region'=>'us-west-2'
));

$a = $client->getJobOutput(array(
	'jobid'=>'??'
));

if($x = json_decode($a['body']->__toString(),JSON_PRETTY_PRINT)){
	print_r($x);
}
else{
	echo $a['body']->__toString();
}
