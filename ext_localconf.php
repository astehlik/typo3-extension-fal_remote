<?php

$extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Tx\FalRemote\Utility\ExtensionConfiguration::class);

if ($extensionConfiguration->isServer()) {
	$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['fal_remote'] = 'EXT:fal_remote/Classes/Remote/FalServer.php';
}

if ($extensionConfiguration->isClient()) {

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers']['fal_remote'] = array(
			'class' => \Tx\FalRemote\RemoteDriver::class,
			'shortName' => 'Remote',
			'flexFormDS' => 'FILE:EXT:core/Configuration/Resource/Driver/LocalDriverFlexForm.xml',
			'label' => 'Remote'
	);

	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
	$signalSlotDispatcher->connect(
			\TYPO3\CMS\Core\Resource\ResourceStorage::class,
			\TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreGeneratePublicUrl,
			\Tx\FalRemote\Remote\FalClient::class,
			'adjustPublicUrlForProcessedFiles'
	);
}


