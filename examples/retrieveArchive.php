<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleGlacierBackups\SimpleGlacierBackups;
use Aws\Glacier\GlacierClient;

$client = new SimpleGlacierBackups('testvault',array(
	'profile'=>'glacier',
	'region'=>'us-west-2'
));

$a = $client->retrieveArchive(array(
	'sns'=>'arn:aws:sns:us-west-2:123456789:glacier_sns',
	'archiveid'=>'??'
));

var_dump($a);
