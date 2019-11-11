<?php
// Show both errorPhpClassFQCN and errorContentSource for PHP Class error handler
$currentShowItem = $GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'];
if (strpos($currentShowItem, 'errorContentSource') === false) {
    // Add errorContentSource for PHP Class error handler if not already present
    $GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'] = str_replace(
        'errorPhpClassFQCN',
        'errorPhpClassFQCN, errorContentSource',
        $GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem']
    );
}
