# pxa_lpeh

Local Page Error Handler

To use as error handler, go to site configuration "Error Handler" tab.

1. Create a new error handling
2. Set "HTTP Error Status Code" to 404
3. Set "How to handle Errors" to "PHP Class"
4. Set "ErrorHandler Class Target (FQCN)" to ```Pixelant\PxaLpeh\Error\PageErrorHandler\LocalPageErrorHandler```
5. Set "Show Content from Page" to a *page* in current Site to generate a link e.g. t3://page?uid=78

If TYPO3 fails to fetch the page, a "generic" TYPO3 error page will be displayed with the http status code.
This might be e.g. the link isn't to a page, the page doesn't exist in this site etc.
