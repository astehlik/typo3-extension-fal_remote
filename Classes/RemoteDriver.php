<?php
namespace Tx\FalRemote;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "fal_remote".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx\FalRemote\Remote\FalClient;
use Tx\FalRemote\Utility\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This driver will use a placeholder service for displaying non existing images.
 */
class RemoteDriver extends LocalDriver {

	/**
	 * @var ExtensionConfiguration
	 */
	protected $extensionConfiguration;

	/**
	 * @var \Tx\FalRemote\Remote\FalClient
	 */
	protected $remoteClient;

	/**
	 * @param array $configuration
	 */
	public function __construct(array $configuration = array()) {
		$this->remoteClient = GeneralUtility::makeInstance(FalClient::class);
		$this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
		parent::__construct($configuration);

		// The remote driver does not provide write access.
		$this->capabilities =
			ResourceStorage::CAPABILITY_BROWSABLE
			| ResourceStorage::CAPABILITY_PUBLIC;
	}

	/**
	 * Adds a file from the local server hard disk to a given path in TYPO3s
	 * virtual file system. This assumes that the local file exists, so no
	 * further check is done here! After a successful the original file must
	 * not exist anymore.
	 *
	 * @param string $localFilePath (within PATH_site)
	 * @param string $targetFolderIdentifier
	 * @param string $newFileName optional, if not given original name is used
	 * @param boolean $removeOriginal if set the original file will be removed
	 *                                after successful operation
	 * @return string the identifier of the new file
	 */
	public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = TRUE) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Copies a file *within* the current storage.
	 * Note that this is only about an inner storage copy action,
	 * where a file is just copied to another folder in the same storage.
	 *
	 * @param string $fileIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $fileName
	 * @return string the Identifier of the new file
	 */
	public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Folder equivalent to copyFileWithinStorage().
	 *
	 * @param string $sourceFolderIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $newFolderName
	 *
	 * @return boolean
	 */
	public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Creates a new (empty) file and returns the identifier.
	 *
	 * @param string $fileName
	 * @param string $parentFolderIdentifier
	 * @return string
	 */
	public function createFile($fileName, $parentFolderIdentifier) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Creates a folder, within a parent folder.
	 * If no parent folder is given, a root level folder will be created
	 *
	 * @param string $newFolderName
	 * @param string $parentFolderIdentifier
	 * @param boolean $recursive
	 * @return string the Identifier of the new folder
	 */
	public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = FALSE) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Removes a file from the filesystem. This does not check if the file is
	 * still used or if it is a bad idea to delete it for some other reason
	 * this has to be taken care of in the upper layers (e.g. the Storage)!
	 *
	 * @param string $fileIdentifier
	 * @return boolean TRUE if deleting the file succeeded
	 */
	public function deleteFile($fileIdentifier) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Removes a folder in filesystem.
	 *
	 * @param string $folderIdentifier
	 * @param boolean $deleteRecursively
	 * @return boolean
	 */
	public function deleteFolder($folderIdentifier, $deleteRecursively = FALSE) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Directly output the contents of the file to the output
	 * buffer. Should not take care of header files or flushing
	 * buffer before. Will be taken care of by the Storage.
	 *
	 * @param string $identifier
	 * @return void
	 */
	public function dumpFileContents($identifier) {
		readfile($this->getFileForLocalProcessing($identifier));
	}

	/**
	 * Checks if a file exists.
	 *
	 * To improve performance we always return TRUE here an accept the possiblity of a later error.
	 *
	 * @param string $fileIdentifier
	 *
	 * @return boolean
	 */
	public function fileExists($fileIdentifier) {
		// Since we do not want to make a remote request for this check every time we
		// only return false if the identifier ends with a slash because then it is a directory.
		if (substr($fileIdentifier, strlen($fileIdentifier) - 1, 1) === '/') {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Checks if a file inside a folder exists
	 *
	 * To improve performance we always return TRUE here an accept the possiblity of a later error.
	 *
	 * @param string $fileName
	 * @param string $folderIdentifier
	 * @return boolean
	 */
	public function fileExistsInFolder($fileName, $folderIdentifier) {
		return TRUE;
	}

	/**
	 * Checks if a folder exists.
	 *
	 * To improve performance we always return TRUE here an accept the possiblity of a later error.
	 *
	 * @param string $folderIdentifier
	 *
	 * @return boolean
	 */
	public function folderExists($folderIdentifier) {
		return TRUE;
	}

	/**
	 * Checks if a folder inside a folder exists.
	 *
	 * To improve performance we always return TRUE here an accept the possiblity of a later error.
	 *
	 * @param string $folderName
	 * @param string $folderIdentifier
	 * @return boolean
	 */
	public function folderExistsInFolder($folderName, $folderIdentifier) {
		return TRUE;
	}

	/**
	 * Returns the contents of a file. Beware that this requires to load the
	 * complete file into memory and also may require fetching the file from an
	 * external location. So this might be an expensive operation (both in terms
	 * of processing resources and money) for large files.
	 *
	 * @param string $fileIdentifier
	 * @return string The file contents
	 */
	public function getFileContents($fileIdentifier) {
		return base64_decode($this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args()));
	}

	/**
	 * Returns a path to a local copy of a file for processing it. When changing the
	 * file, you have to take care of replacing the current version yourself!
	 *
	 * @param string $fileIdentifier
	 * @param bool $writable Set this to FALSE if you only need the file for read
	 *                       operations. This might speed up things, e.g. by using
	 *                       a cached local version. Never modify the file if you
	 *                       have set this flag!
	 * @return string The path to the file on the local disk
	 */
	public function getFileForLocalProcessing($fileIdentifier, $writable = TRUE) {
		$temporaryPath = $this->getTemporaryPathForFile($fileIdentifier);
		$result = file_put_contents($temporaryPath, $this->getFileContents($fileIdentifier));
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier, array('mtime'));
		touch($temporaryPath, $fileInfo['mtime']);
		if ($result === FALSE) {
			throw new \RuntimeException('Creating temporary file ' . $fileIdentifier . ' for local processing failed.');
		}
		return $temporaryPath;
	}

	/**
	 * Returns information about a file.
	 *
	 * @param string $fileIdentifier
	 * @param array $propertiesToExtract Array of properties which are be extracted
	 *                                   If empty all will be extracted
	 * @return array
	 */
	public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = array()) {
		return $this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args());
	}

	/**
	 * Returns a list of files inside the specified path
	 *
	 * @param string $folderIdentifier
	 * @param integer $start
	 * @param integer $numberOfItems
	 * @param boolean $recursive
	 * @param array $filenameFilterCallbacks callbacks for filtering the items
	 *
	 * @return array of FileIdentifiers
	 */
	public function getFilesInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = FALSE, array $filenameFilterCallbacks = array()) {
		return $this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args());
	}

	/**
	 * Returns information about a file.
	 *
	 * @param string $folderIdentifier
	 * @return array
	 */
	public function getFolderInfoByIdentifier($folderIdentifier) {
		return $this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args());
	}

	/**
	 * Returns a list of folders inside the specified path
	 *
	 * @param string $folderIdentifier
	 * @param integer $start
	 * @param integer $numberOfItems
	 * @param boolean $recursive
	 * @param array $folderNameFilterCallbacks callbacks for filtering the items
	 *
	 * @return array of Folder Identifier
	 */
	public function getFoldersInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = FALSE, array $folderNameFilterCallbacks = array()) {
		return $this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args());
	}

	/**
	 * Returns the permissions of a file/folder as an array
	 * (keys r, w) of boolean flags
	 *
	 * @param string $identifier
	 * @return array
	 */
	public function getPermissions($identifier) {
		return array('r' => TRUE, 'w' => FALSE);
	}

	/**
	 * Returns the public URL to a file.
	 * For the local driver, this will always return a path relative to PATH_site.
	 *
	 * @param string $identifier
	 * @return string
	 * @throws \TYPO3\CMS\Core\Resource\Exception
	 */
	public function getPublicUrl($identifier) {
		$publicUrl = parent::getPublicUrl($identifier);
		if (!empty($publicUrl)) {
			$publicUrl = rtrim($this->extensionConfiguration->getRemoteTypo3Url(), '/') . '/' . ltrim($publicUrl, '/');
		}
		return $publicUrl;
	}

	/**
	 * Creates a hash for a file.
	 *
	 * @param string $fileIdentifier
	 * @param string $hashAlgorithm The hash algorithm to use
	 * @return string
	 */
	public function hash($fileIdentifier, $hashAlgorithm) {
		return $this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args());
	}

	/**
	 * Checks if a folder contains files and (if supported) other folders.
	 *
	 * @param string $folderIdentifier
	 * @return boolean TRUE if there are no files and folders within $folder
	 */
	public function isFolderEmpty($folderIdentifier) {
		return $this->remoteClient->call($this->storageUid, __FUNCTION__, func_get_args());
	}

	/**
	 * Moves a file *within* the current storage.
	 * Note that this is only about an inner-storage move action,
	 * where a file is just moved to another folder in the same storage.
	 *
	 * @param string $fileIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $newFileName
	 *
	 * @return string
	 */
	public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Folder equivalent to moveFileWithinStorage().
	 *
	 * @param string $sourceFolderIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $newFolderName
	 *
	 * @return array All files which are affected, map of old => new file identifiers
	 */
	public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Processes the configuration for this driver.
	 *
	 * @return void
	 */
	public function processConfiguration() {
		parent::processConfiguration();
	}

	/**
	 * Renames a file in this storage.
	 *
	 * @param string $fileIdentifier
	 * @param string $newName The target path (including the file name!)
	 * @return string The identifier of the file after renaming
	 */
	public function renameFile($fileIdentifier, $newName) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Renames a folder in this storage.
	 *
	 * @param string $folderIdentifier
	 * @param string $newName
	 * @return array A map of old to new file identifiers of all affected resources
	 */
	public function renameFolder($folderIdentifier, $newName) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Replaces a file with file in local file system.
	 *
	 * @param string $fileIdentifier
	 * @param string $localFilePath
	 * @return boolean TRUE if the operation succeeded
	 */
	public function replaceFile($fileIdentifier, $localFilePath) {
		throw new \RuntimeException('This driver does not support writing files.');
	}

	/**
	 * Sets the contents of a file to the specified value.
	 *
	 * @param string $fileIdentifier
	 * @param string $contents
	 * @return integer The number of bytes written to the file
	 */
	public function setFileContents($fileIdentifier, $contents) {
		throw new \RuntimeException('This driver does not support writing files.');
	}
}
