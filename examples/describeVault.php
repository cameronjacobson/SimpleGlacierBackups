<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleGlacierBackups\SimpleGlacierBackups;
use Aws\Glacier\GlacierClient;

$client = new SimpleGlacierBackups('testvault',array(
	'profile'=>'glacier',
	'region'=>'us-west-2'
));

$a = $client->describeVault(array(
	'accountId'=>'-',
	'vaultName'=>'testvault'
));
var_dump($a);
