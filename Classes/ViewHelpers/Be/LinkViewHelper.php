<?php

namespace JVelletti\JvEvents\ViewHelpers\Be;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class LinkViewHelper extends AbstractTagBasedViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        // ✅ Business arguments
        $this->registerArgument('uid', 'int', 'Uid of record', true);
        $this->registerArgument('pageId', 'int', 'Target page', true);

        $this->registerArgument('onlyActual', 'int', '', false, 0);
        $this->registerArgument('eventId', 'int', '', false, 0);
        $this->registerArgument('recursive', 'int', '', false, 0);

        $this->registerArgument('table', 'string', '', false, 'tx_jvevents_domain_model_event');
        $this->registerArgument('returnM', 'string', '', false, 'jvevents_eventmngt');
        $this->registerArgument('returnPid', 'int', '', false, 0);
        $this->registerArgument('returnAction', 'string', '', false, 'list');
    }

    public function render(): string
    {
        $this->tag->setTagName('a');
        $uid = (int)$this->arguments['uid'];
        $table = (string)$this->arguments['table'];
        $returnM = (string)$this->arguments['returnM'];

        $returnAction = (string)$this->arguments['returnAction'];
        $returnPid = (int)$this->arguments['returnPid'];

        $eventId = (int)$this->arguments['eventId'];
        $recursive = (int)$this->arguments['recursive'];
        $onlyActual = (int)$this->arguments['onlyActual'];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        // --- returnUrl ---
        $returnUrl = '';

        if ($returnPid > 0) {
            $moduleName = str_replace(['module/', '/'], ['', '_'], trim($returnM, '/'));

            try {
                $returnUri = $uriBuilder->buildUriFromRoute($moduleName, [
                    'id' => $returnPid,
                    'action' => $returnAction,
                    'recursive' => $recursive,
                    'event' => $eventId,
                    'onlyActual' => $onlyActual
                ]);

                $returnUrl = (string)$returnUri;

            } catch (RouteNotFoundException) {
                $returnUrl = 'routeError__' . $moduleName;
            }
        }

        // --- edit link ---
        try {
            $uri = $uriBuilder->buildUriFromRoute('record_edit', [
                'edit[' . $table . '][' . $uid . ']' => 'edit',
                'returnUrl' => $returnUrl
            ]);
        } catch (RouteNotFoundException) {
            $uri = 'routeError__record_edit';
        }

        // --- HTML attributes (NEW WAY) ---
        // ✅ everything not defined via registerArgument lands here
        foreach ($this->additionalArguments as $name => $value) {
            $this->tag->addAttribute($name, $value);
        }

        $this->tag->addAttribute('href', (string)$uri);
        $this->tag->setContent($this->renderChildren());

        return $this->tag->render();
    }
}