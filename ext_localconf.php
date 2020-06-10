<?php
defined('TYPO3_MODE') or die();

$boot = function() {
    if (
        !TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )
        ->get('pxa_lpeh', 'disableXClass')
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Core\Error\PageErrorHandler\PageContentErrorHandler::class] = [
            'className' => Pixelant\PxaLpeh\Error\PageErrorHandler\LocalPageErrorHandler::class
        ];
    }

};

$boot();
unset($boot);
