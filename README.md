# Local Page Error Handler for TYPO3

This extension speeds up error page handling and frees up PHP workers by loading local page content without issuing an external HTTP request.

The speed increase depends on the round-trip time for external HTTP requests for your server (including resolving the DNS), but it could easily be 3x what you're having today.

## Installation

1. Download from the TYPO3 Extension Repository or require the extension using Composer: `composer req pixelant/pxa-lpeh`
2. Enable the extension in the Admin tools > Extensions module or run `vendor/bin/typo3 extension:activate pxa_lpeh`

## Configuration

### Default Configuration

No configuration is required by default.

The extension will use the configuration for any "Show Content from Page" error handler.

* Make internal requests where Show Content From Page is to an internal TYPO3 page, e.g. "t3://page?uid=404".
* External requests will behave normally and issue an external request. E.g. where Show Content From Page points to "https://www.example.com".

### Disabling Page Content Error Handler Override

By default, this extension overrides the `PageContentErrorHandler` class and calls this class only if the Error Handler configuration explicitly requires an external request.

You can disable this override in the Admin tools > Settings > Extension Configuration by checking the box "Don't replace the standard 'Show Content from Page' error handler, use 'PHP Class' instead".

This extension can still be used by explicitly configuring a PHP Error Handler Class in Site management > Sites > [Your Site] > Error Handling:

1. Create a new error handling
2. Set "HTTP Error Status Code" to 404
3. Set "How to handle Errors" to "PHP Class"
4. Set "ErrorHandler Class Target (FQCN)" to ```Pixelant\PxaLpeh\Error\PageErrorHandler\LocalPageErrorHandler```
5. Set "Show Content from Page" to a *page* in current Site to generate a link e.g. t3://page?uid=78

### Avoiding Hung HTTP Requests

External requests for error pages can hang your site during high-load situations. We recommend setting `$GLOBALS['TYPO3_CONF_VARS']['HTTP']['timeout']` to a non-zero value to alleviate this problem.

## Ultimate Error Fallback

If fetching the page fails, a "generic" TYPO3 error page will be displayed with the http status code. This might be e.g. the link isn't to a page, the page doesn't exist in this site etc.

## Issues and Contribution

Please feel free to submit issues or contribute pull requests to this extension.
