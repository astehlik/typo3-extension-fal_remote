<?php
namespace Tx\FalRemote\Remote;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "fal_remote".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * eID processor for retrieving
 */
class FalServer {

	/**
	 * The result that is returned to the client in JSON format.
	 *
	 * @var array
	 */
	protected $result = array(
		'success' => FALSE,
		'error' => 'Unknown error',
	);

	/**
	 * Returns the current result in JSON format.
	 *
	 * @return string
	 */
	public function getResult() {
		return json_encode($this->result);
	}

	/**
	 * Processed the requested call based on the current GET parameters.
	 */
	public function processCall() {

		$payload = GeneralUtility::_GET('payload');
		if (empty($payload)) {
			$this->result['error'] = 'No payload submitted';
			return;
		}

		$parameters = json_decode($payload, TRUE);
		if (!$parameters) {
			$this->result['error'] = 'Error decoding JSON payload.';
			return;
		}

		if (empty($parameters['hash'])) {
			$this->result['error'] = 'No hash was submitted.';
			return;
		}

		if (empty($parameters['storageUid'])) {
			$this->result['error'] = 'No storageUid was submitted.';
			return;
		}

		if ($parameters['hash'] !== GeneralUtility::hmac($parameters['storageUid'] . $parameters['function'] . serialize($parameters['parameters']), 'fal_remote')) {
			$this->result['error'] = 'An invalid hash was submitted.';
			return;
		}

		try {
			$result = call_user_func_array(array($this->getDriver($parameters['storageUid']), $parameters['function']), $parameters['parameters']);

			if ($parameters['function'] === 'getFileContents') {
				$result = base64_encode($result);
			}

			$this->result = array(
				'success' => TRUE,
				'returnValue' => $result
			);
		} catch (\Exception $e) {
			$this->result = array(
				'success' => FALSE,
				'returnValue' => $e->getMessage()
			);
		}

	}

	/**
	 * Retrieves the driver from the storage with the given UID.
	 *
	 * @param int $storageUid
	 * @return \TYPO3\CMS\Core\Resource\Driver\DriverInterface
	 */
	protected function getDriver($storageUid) {

		$storage = ResourceFactory::getInstance()->getStorageObject($storageUid);

		$method = new \ReflectionMethod($storage, 'getDriver');
		$method->setAccessible(TRUE);

		return $method->invoke($storage);
	}
}

$remoteServer = GeneralUtility::makeInstance(FalServer::class);
$remoteServer->processCall();
echo $remoteServer->getResult();
die();
