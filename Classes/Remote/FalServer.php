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


		$storageUid = (string)GeneralUtility::_GET('storageUid');
		if ($storageUid === '') {
			$this->result['error'] = 'No storageUid submitted.';
			return;
		}
		$storageUid = (int)$storageUid;

		$function = (string)GeneralUtility::_GET('function');
		if ($function === '') {
			$this->result['error'] = 'No function submitted.';
			return;
		}

		$parameters = (string)GeneralUtility::_GET('parameters');
		if ($parameters === '') {
			$this->result['error'] = 'No parameters submnitted.';
			return;
		}

		$hash = (string)GeneralUtility::_GET('hash');
		if ($hash === '') {
			$this->result['error'] = 'No hash was submitted.';
			return;
		}

		if ($hash !== GeneralUtility::hmac($storageUid . $function . $parameters, 'fal_remote')) {
			$this->result['error'] = 'An invalid hash was submitted.';
			return;
		}

		$parameters = unserialize(base64_decode($parameters));
		if ($parameters === FALSE || !is_array($parameters)) {
			$this->result['error'] = 'The parameters array could not be deserialized.';
			return;
		}

		try {
			$result = call_user_func_array(array($this->getDriver($storageUid), $function), $parameters);

			if ($function === 'getFileContents') {
				$result = base64_encode($result);
			}

			$this->result = array(
				'success' => TRUE,
				'returnValue' => $result
			);
		} catch (\Exception $e) {
			$this->result = array(
				'success' => FALSE,
				'error' => $e->getMessage()
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
