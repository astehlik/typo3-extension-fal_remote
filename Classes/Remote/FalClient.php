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

use Tx\FalRemote\RemoteDriver;
use Tx\FalRemote\Utility\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Client for retrieving data from a remote fal_remote installation.
 */
class FalClient {

	/**
	 * @var ExtensionConfiguration
	 */
	protected $extensionConfiguration;

	/**
	 * Initialize dependencies.
	 */
	public function __construct() {
		$this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
	}

	/**
	 * This method will be called be the signal slot for retrieving the public URL
	 * of a file.
	 *
	 * If the processed file was not modified (the original file is used) and if
	 * that file comes from the storage using the Remote driver the public
	 * URL will be modified to read the image from the remote server.
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $resourceStorage
	 * @param \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver
	 * @param \TYPO3\CMS\Core\Resource\ResourceInterface $resourceObject
	 * @param bool $relativeToCurrentScript
	 * @param $urlData
	 */
	public function adjustPublicUrlForProcessedFiles(
		/** @noinspection PhpUnusedParameterInspection */
		$resourceStorage, $driver, $resourceObject, $relativeToCurrentScript, $urlData
	) {

		if (!$resourceObject instanceof ProcessedFile) {
			return;
		}

		if (!$resourceObject->usesOriginalFile()) {
			return;
		}

		$originalDriver = $this->getDriver($resourceObject->getOriginalFile()->getStorage());
		if (!$originalDriver instanceof RemoteDriver) {
			return;
		}

		$urlData['publicUrl'] = $resourceObject->getOriginalFile()->getPublicUrl($relativeToCurrentScript);
	}

	/**
	 * Runs a remote function call.
	 *
	 * @param int $storageUid The UID of the drivers storage.
	 * @param string $function The name of the function that should be called.
	 * @param array $parameters The parameters passed to the remote function.
	 * @return mixed The return value of the remote function.
	 */
	public function call($storageUid, $function, $parameters = array()) {

		$payload = array(
			'storageUid' => $storageUid,
			'function' => $function,
			'parameters' => $parameters,
			'hash' => GeneralUtility::hmac($storageUid . $function . serialize($parameters), 'fal_remote')
		);

		$report = array();
		$url = $this->extensionConfiguration->getRemoteTypo3Url() . '?eID=fal_remote&payload=' . urlencode(json_encode($payload));
		$result = GeneralUtility::getUrl($url, 0, FALSE, $report);

		if (!$result) {
			throw new \RuntimeException('Error fetching file information for ' . $function . ': ' . $report['message']);
		}

		$result = json_decode($result, TRUE);

		if (empty($result['success'])) {
			$error = !empty($result['error']) ? ': ' . $result['error'] : '';
			throw new \RuntimeException('Error fetching file information for ' . $function . $error);
		}

		return $result['returnValue'];
	}

	/**
	 * Retrieves the driver from the given storage.
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storage
	 * @return \TYPO3\CMS\Core\Resource\Driver\DriverInterface
	 */
	protected function getDriver($storage) {

		$method = new \ReflectionMethod($storage, 'getDriver');
		$method->setAccessible(TRUE);

		return $method->invoke($storage);
	}
}