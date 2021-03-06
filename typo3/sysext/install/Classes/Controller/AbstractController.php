<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Install\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Install\Service\LateBootService;

/**
 * Controller abstract for shared parts of the install tool
 * @internal This class is a specific controller implementation and is not considered part of the Public TYPO3 API.
 */
class AbstractController
{
    /**
     * Helper method to initialize a standalone view instance.
     *
     * @param ServerRequestInterface $request
     * @param string $templatePath
     * @return StandaloneView
     * @internal param string $template
     */
    protected function initializeStandaloneView(ServerRequestInterface $request, string $templatePath): StandaloneView
    {
        $viewRootPath = GeneralUtility::getFileAbsFileName('EXT:install/Resources/Private/');
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->getRequest()->setControllerExtensionName('Install');
        $view->setTemplatePathAndFilename($viewRootPath . 'Templates/' . $templatePath);
        $view->setLayoutRootPaths([$viewRootPath . 'Layouts/']);
        $view->setPartialRootPaths([$viewRootPath . 'Partials/']);
        $view->assignMultiple([
            'controller' => $request->getQueryParams()['install']['controller'] ?? 'maintenance',
            'context' => $request->getQueryParams()['install']['context'] ?? '',
            'composerMode' => Environment::isComposerMode(),
            'currentTypo3Version' => (string)(new Typo3Version())
        ]);
        return $view;
    }

    /**
     * Some actions like the database analyzer and the upgrade wizards need additional
     * bootstrap actions performed.
     *
     * Those actions can potentially fatal if some old extension is loaded that triggers
     * a fatal in ext_localconf or ext_tables code! Use only if really needed.
     *
     * @param bool $resetContainer
     * @return ContainerInterface
     */
    public function loadExtLocalconfDatabaseAndExtTables(bool $resetContainer = true): ContainerInterface
    {
        return GeneralUtility::makeInstance(LateBootService::class)->loadExtLocalconfDatabaseAndExtTables($resetContainer);
    }

    public function resetGlobalContainer(): void
    {
        GeneralUtility::makeInstance(LateBootService::class)->makeCurrent(null, []);
    }
}
