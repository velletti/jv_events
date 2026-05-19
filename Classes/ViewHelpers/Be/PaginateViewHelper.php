<?php

namespace JVelletti\JvEvents\ViewHelpers\Be;

use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PaginateViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('objects', 'mixed', 'array or queryresult', true);
        $this->registerArgument('as', 'string', 'new variable name', true);
        $this->registerArgument('itemsPerPage', 'int', 'items per page', false, 10);
        $this->registerArgument('name', 'string', 'unique identification - will take "as" as fallback', false, '');
    }

    public function render(): string
    {
        $arguments = $this->arguments;
        $this->escapeOutput = false ;
        $this->escapeChildren = false ;

        if ($arguments['objects'] === null) {
            return $this->renderChildren();
        }

        $variableProvider = $this->renderingContext->getVariableProvider();

        $variableProvider->add($arguments['as'], [
            'pagination' => $this->getPagination(),
            'paginator' => $this->getPaginator(),
            'name' => $this->getName()
        ]);

        $output = $this->renderChildren();

        $variableProvider->remove($arguments['as']);

        return $output;
    }

    protected function getPagination(): PaginationInterface
    {
        $paginator = $this->getPaginator();
        return GeneralUtility::makeInstance(SimplePagination::class, $paginator);
    }

    protected function getPaginator(): PaginatorInterface
    {
        $objects = $this->arguments['objects'];

        if (is_array($objects)) {
            $paginatorClass = ArrayPaginator::class;
        } elseif ($objects instanceof QueryResultInterface) {
            $paginatorClass = QueryResultPaginator::class;
        } else {
            throw new \InvalidArgumentException(
                'Objects must be array or QueryResultInterface',
                1680000000
            );
        }

        return GeneralUtility::makeInstance(
            $paginatorClass,
            $objects,
            $this->getPageNumber(),
            $this->arguments['itemsPerPage']
        );
    }

    protected function getPageNumber(): int
    {
        $variableProvider = $this->renderingContext->getVariableProvider();

        return $variableProvider->exists('currentPage')
            ? (int)$variableProvider->get('currentPage')
            : 1;
    }

    protected function getName(): string
    {
        return $this->arguments['name'] ?: $this->arguments['as'];
    }
}