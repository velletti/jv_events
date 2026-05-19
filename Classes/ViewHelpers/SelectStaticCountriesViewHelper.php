<?php

namespace JVelletti\JvEvents\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use JVelletti\JvEvents\Domain\Repository\StaticCountryRepository;


class SelectStaticCountriesViewHelper extends AbstractFormFieldViewHelper
{


    protected mixed $selectedValue;

    protected StaticCountryRepository $countryRepository;


    public function initializeArguments(): void
    {
        parent::initializeArguments();

        // ✅ HTML attributes (intern handled by FormViewHelper)
        $this->registerArgument('size', 'string', 'Size of select field', false);
        $this->registerArgument('disabled', 'bool', 'Disabled flag', false, false);

        // ✅ data arguments
        $this->registerArgument('options', 'array', 'Options array', false, []);
        $this->registerArgument('optionsAfterContent', 'bool', '', false, false);
        $this->registerArgument('multiple', 'bool', '', false, false);
        $this->registerArgument('required', 'bool', '', false, false);

        $this->registerArgument('sortByOptionLabel', 'bool', '', false, true);
        $this->registerArgument('allowedCountries', 'array', '', false, []);
        $this->registerArgument('prependOptionLabel', 'string', '', false);
        $this->registerArgument('prependOptionValue', 'string', '', false);

        $this->registerArgument('optionValueField', 'string', '', false, 'cnIso2');
        $this->registerArgument('optionLabelField', 'string', '', false, 'cnShortLocal');

        $this->registerArgument('selectAllByDefault', 'bool', '', false, false);
        $this->registerArgument('errorClass', 'string', '', false, 'f3-form-error');
    }

    public function render(): string
    {
        // ✅ load options dynamically (replaces old initialize())
        $options = $this->arguments['options'] ?? [];
        $this->tag->setTagName('select');

        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->countryRepository = GeneralUtility::makeInstance(StaticCountryRepository::class);
            if ($this->countryRepository) {
                if (!empty($this->arguments['allowedCountries'])) {
                    $options = $this->countryRepository->findByCnIso2($this->arguments['allowedCountries']);
                } else {
                    $options = $this->countryRepository->findAll();
                }
            }

        }

        // ✅ convert options
        $options = $this->getOptions($options);

        // --- basic setup ---
        if ($this->arguments['required']) {
            $this->tag->addAttribute('required', 'required');
        }

        $name = $this->getName();

        if ($this->arguments['multiple']) {
            $this->tag->addAttribute('multiple', 'multiple');
            $name .= '[]';
        }

        $this->tag->addAttribute('name', $name);

        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();

        $content = '';

        // token handling
        $this->registerFieldNameForFormTokenGeneration($name);

        if ($this->arguments['multiple']) {
            $content .= $this->renderHiddenFieldForEmptyValue();

            $optionsCount = count($options);
            for ($i = 1; $i < $optionsCount; $i++) {
                $this->registerFieldNameForFormTokenGeneration($name);
            }

            $viewHelperVariableContainer->addOrUpdate(
                self::class,
                'registerFieldNameForFormTokenGeneration',
                $name
            );
        }

        $viewHelperVariableContainer->addOrUpdate(
            self::class,
            'selectedValue',
            $this->getSelectedValue()
        );

        $prependContent = $this->renderPrependOptionTag();
        $tagContent = $this->renderOptionTags($options);
        $childContent = $this->renderChildren();

        $viewHelperVariableContainer->remove(self::class, 'selectedValue');
        $viewHelperVariableContainer->remove(self::class, 'registerFieldNameForFormTokenGeneration');

        if ($this->arguments['optionsAfterContent']) {
            $tagContent = $childContent . $tagContent;
        } else {
            $tagContent .= $childContent;
        }

        $tagContent = $prependContent . $tagContent;

        $this->tag->setContent($tagContent);

        $content .= $this->tag->render();

        return $content;
    }

    protected function renderPrependOptionTag(): string
    {
        $output = '';

        if (!empty($this->arguments['prependOptionLabel'])) {
            $value = $this->arguments['prependOptionValue'] ?? '';
            $label = $this->arguments['prependOptionLabel'];

            $output .= $this->renderOptionTag((string)$value, (string)$label, false) . LF;
        }

        return $output;
    }

    protected function renderOptionTags(array $options): string
    {
        $output = '';

        foreach ($options as $value => $label) {
            $isSelected = $this->isSelected($value);
            $output .= $this->renderOptionTag((string)$value, (string)$label, $isSelected) . LF;
        }

        return $output;
    }

    protected function getOptions(iterable $optionsArgument): array
    {
        if (!is_iterable($optionsArgument)) {
            return [];
        }

        $options = [];

        foreach ($optionsArgument as $key => $value) {

            if (!is_object($value) && !is_array($value)) {
                $options[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $key = ObjectAccess::getPropertyPath($value, $this->arguments['optionValueField']);
                $value = ObjectAccess::getPropertyPath($value, $this->arguments['optionLabelField']);
                $options[$key] = $value;
                continue;
            }

            // value extraction
            if (!empty($this->arguments['optionValueField'])) {
                $key = ObjectAccess::getPropertyPath($value, $this->arguments['optionValueField']);
            } elseif ($this->persistenceManager->getIdentifierByObject($value) !== null) {
                $key = $this->persistenceManager->getIdentifierByObject($value);
            } elseif (method_exists($value, '__toString')) {
                $key = (string)$value;
            } else {
                throw new Exception('No identifying value found.', 1247826696);
            }

            // label extraction
            if (!empty($this->arguments['optionLabelField'])) {
                $value = ObjectAccess::getPropertyPath($value, $this->arguments['optionLabelField']);
            } elseif (method_exists($value, '__toString')) {
                $value = (string)$value;
            }

            $options[$key] = $value;
        }

        if ($this->arguments['sortByOptionLabel']) {
            asort($options, SORT_LOCALE_STRING);
        }

        return $options;
    }

    protected function isSelected($value): bool
    {
        $selectedValue = $this->getSelectedValue();

        if ($value === $selectedValue || (string)$value === $selectedValue) {
            return true;
        }

        if ($this->arguments['multiple']) {

            if ($selectedValue === null && $this->arguments['selectAllByDefault']) {
                return true;
            }

            if (is_array($selectedValue) && in_array($value, $selectedValue, true)) {
                return true;
            }
        }

        return false;
    }

    protected function getSelectedValue()
    {
        $this->setRespectSubmittedDataValue(true);

        $value = $this->getValueAttribute();

        if (!is_iterable($value)) {
            return $this->getOptionValueScalar($value);
        }

        $selectedValues = [];

        foreach ($value as $selectedValueElement) {
            $selectedValues[] = $this->getOptionValueScalar($selectedValueElement);
        }

        return $selectedValues;
    }

    protected function getOptionValueScalar($valueElement)
    {
        if (is_object($valueElement)) {

            if (!empty($this->arguments['optionValueField'])) {
                return ObjectAccess::getPropertyPath($valueElement, $this->arguments['optionValueField']);
            }

            if ($this->persistenceManager->getIdentifierByObject($valueElement) !== null) {
                return $this->persistenceManager->getIdentifierByObject($valueElement);
            }

            return (string)$valueElement;
        }

        return $valueElement;
    }

    protected function renderOptionTag(string $value, string $label, bool $isSelected): string
    {
        $output = '<option value="' . htmlspecialchars($value) . '"';

        if ($isSelected) {
            $output .= ' selected="selected"';
        }

        $output .= '>' . htmlspecialchars($label) . '</option>';

        return $output;
    }
}