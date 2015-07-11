<?php

namespace SimpleGlacierBackups;

use \Aws\Glacier\GlacierClient;
use \PharData;
use \Phar;

class SimpleGlacierBackups
{
	private $client;
	private $vault;

	/**
	 * @param $vault : name of vault
	 * @param $params : array
	 *   ['profile'] = name of glacier profile
	 *   ['region']  = AWS region
	 */
	public function __construct($vault,array $params){
		$this->client = GlacierClient::factory($params);
		$this->setVault($vault);
	}

	/**
	 * @param $globs : array of globs that define which files to include in archive
	 *   -- see examples/backupFiles.php --
	 */
	public function backupFiles(array $globs){

		if(empty(ini_get('date.timezone'))){
			error_log('Warning: SimpleGlacierBackups - you may want to set date.timezone in php.ini');
		}

		$date = date('Ymd_His');

		$tarfile = '/tmp/'.$date.'.tar';
		$bz2file = $tarfile.'.bz2';

		$phar = new PharData($tarfile);

		$phar->startBuffering();
		foreach($globs as $glob){
		    $files = glob($glob);
		    foreach($files as $file){
		        $basename = basename($file);
		        $phar[$date.'/'.$basename] = file_get_contents($file);
		    }
		}

		$phar->compress(Phar::BZ2);
		$phar->stopBuffering();

		unlink($tarfile);

		$result = $this->client->uploadArchive(array(
			'sourceFile'=>$bz2file,
			'accountId'=>'-',
			'vaultName'=>$this->vault,
		));
		var_dump($result);

	}

	/**
	 * @param $params : array of params passed through to underlying AWS client
	 *   -- see examples/describeVault.php --
	 */
	public function describeVault(array $params){
		return $this->client->describeVault($params);
	}

	/**
	 * @param $params : array of params passed through to underlying AWS client
	 *  for simplicity we're only including 'start','end', and 'sns' for 'SNSTopic'
	 *   -- see examples/retrieveInventory.php --
	 */
	public function retrieveInventoryList(array $params){
		$p = array(
			'vaultName'=>$this->vault,
			'accountId'=>'-',
			'Description' => 'Retrieve Archive List for Range '.$params['start'].' to '.$params['end'].' [Submitted: '.date('Y-m-d H:i:s').']',
			'Type' => 'inventory-retrieval',
			'InventoryRetrievalParameters' => array(
				'StartDate'=>$params['start'],
				'EndDate'=>$params['end']
			)
		);
		if(!empty($params['sns'])){
			$p['SNSTopic'] = $params['sns'];
		}
		return $this->client->initiateJob($p);
	}

	/**
	 * @param $params : array of params passed through to underlying AWS client
	 *  for simplicity we're only including 'start','end',
	 *  archiveid for 'ArchiveId', and 'sns' for 'SNSTopic'
	 *   -- see examples/retrieveArchive.php --
	 */
	public function retrieveArchive(array $params){
		$p = array(
			'vaultName'=>$this->vault,
			'accountId'=>'-',
			'ArchiveId'=>$params['archiveid'],
			'Description' => 'Retrieve Archive '.$params['archiveid'].' [Submitted: '.date('Y-m-d H:i:s').']',
			'Type' => 'archive-retrieval',
		);
		if(!empty($params['sns'])){
			$p['SNSTopic'] = $params['sns'];
		}
		return $this->client->initiateJob($p);
	}

	/**
	 * @param $vaultName : name of AWS Glacier vault
	 */
	private function setVault($vaultName){
		$this->vault = $vaultName;
		$vaults = $this->client->listVaults(array(
			'accountId'=>'-'
		));
		if(!empty($vaults['VaultList'])){
			foreach($vaults['VaultList'] as $v){
				if($v['VaultName'] === $this->vault){
					return;
				}
			}
		}

		$this->client->createVault(array(
			'accountId'=>'-',
			'vaultName'=>$this->vault
		));
	}
}

