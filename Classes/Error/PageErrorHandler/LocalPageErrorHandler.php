<?php
namespace Pixelant\PxaLpeh\Error\PageErrorHandler;

/*
 * This file is part of a Pixelant extension.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Controller\ErrorPageController;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageContentErrorHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\MiddlewareDispatcher;
use TYPO3\CMS\Core\Http\MiddlewareStackResolver;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Http\RequestHandler;

/**
 * Does not execute a CURL request for internal pages. Just set it up in an extension
 * and refer to it in your Site Configuration (PHP Error Handler)
 */
class LocalPageErrorHandler extends PageContentErrorHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param string $message
     * @param array $reasons
     * @return ResponseInterface
     * @throws \RuntimeException
     */
    public function handlePageError(ServerRequestInterface $request, string $message, array $reasons = []): ResponseInterface
    {
        $targetDetails = $this->resolveDetails($this->errorHandlerConfiguration['errorContentSource']);
        $siteIsValid = $this->resolveSite($request, (int)$targetDetails['pageuid']);
        $pageIdIsValid = $this->doesSiteHavePage($request, (int)$targetDetails['pageuid']);

        if ($targetDetails['type'] === 'page' && $siteIsValid && $pageIdIsValid) {
            $response = $this->buildSubRequest($request, (int)$targetDetails['pageuid']);
            return $response->withStatus($this->statusCode);
        }

        $httpStatus = '\\TYPO3\\CMS\\Core\\Utility\\HttpUtility::HTTP_STATUS_' . $this->statusCode;
        $content = GeneralUtility::makeInstance(ErrorPageController::class)->errorAction(
            $this->statusCode,
            constant($httpStatus)
        );
        return new HtmlResponse($content, $this->statusCode);
    }

    /**
     * @param ServerRequestInterface $request
     * @param int $pageId
     * @return ResponseInterface
     * @throws SiteNotFoundException
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function buildSubRequest(ServerRequestInterface $request, int $pageId): ResponseInterface
    {
        $request = $request->withQueryParams(['id' => $pageId]);
        $dispatcher = $this->buildDispatcher();
        return $dispatcher->handle($request);
    }

    /**
     * @param string $typoLinkUrl
     * @return array
     */
    protected function resolveDetails(string $typoLinkUrl): array
    {
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        return $linkService->resolve($typoLinkUrl);
    }

    /**
     * @return MiddlewareDispatcher
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function buildDispatcher()
    {
        $requestHandler = GeneralUtility::makeInstance(RequestHandler::class);
        $resolver = new MiddlewareStackResolver(
            GeneralUtility::makeInstance(PackageManager::class),
            GeneralUtility::makeInstance(DependencyOrderingService::class),
            GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_core')
        );

        $middlewares = $resolver->resolve('frontend');
        return new MiddlewareDispatcher($requestHandler, $middlewares);
    }

    /**
     * @param ServerRequestInterface $request
     * @param int $pageId
     * @return bool
     */
    protected function resolveSite(ServerRequestInterface &$request, int $pageId): bool
    {
        $site = $request->getAttribute('site', null);
        if (!$site instanceof Site) {
            try {
                $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
                $request = $request->withAttribute('site', $site);
            } catch (\Throwable $th) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param ServerRequestInterface $request
     * @param int $pageId
     * @return bool
     */
    protected function doesSiteHavePage(ServerRequestInterface &$request, int $pageId): bool
    {
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }
}
