<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleGlacierBackups\SimpleGlacierBackups;
use Aws\Glacier\GlacierClient;

$client = new SimpleGlacierBackups('testvault',array(
	'profile'=>'glacier',
	'region'=>'us-west-2'
));

$a = $client->retrieveInventoryList(array(
	'sns'=>'arn:aws:sns:us-west-2:123456789:glacier_sns',
	'start'=>'2015-07-01T00:00:00Z',
	'end'=>'2015-07-09T23:59:59Z'
));

var_dump($a);
