<?php

namespace JVelletti\JvEvents\ViewHelpers;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class ImageViewHelper extends AbstractTagBasedViewHelper
{
    protected ImageService $imageService;

    public function injectImageService(ImageService $imageService): void
    {
        $this->imageService = $imageService;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        // ✅ BUSINESS ARGUMENTS
        $this->registerArgument('src', 'string', 'Image source', false, '');
        $this->registerArgument('treatIdAsReference', 'bool', 'Treat as reference', false, false);
        $this->registerArgument('image', 'object', 'FAL object');

        $this->registerArgument('crop', 'mixed', 'Crop config');
        $this->registerArgument('cropVariant', 'string', 'Crop variant', false, 'default');

        $this->registerArgument('width', 'string', 'Width');
        $this->registerArgument('height', 'string', 'Height');
        $this->registerArgument('minWidth', 'int', 'Min width');
        $this->registerArgument('minHeight', 'int', 'Min height');
        $this->registerArgument('maxWidth', 'int', 'Max width');
        $this->registerArgument('maxHeight', 'int', 'Max height');
        $this->registerArgument('absolute', 'bool', 'Absolute URL', false, false);
    }

    public function render(): string
    {
        $this->tag->setTagName('img');
        if (
            ($this->arguments['src'] === null && $this->arguments['image'] === null) ||
            ($this->arguments['src'] !== null && $this->arguments['image'] !== null)
        ) {
            throw new Exception('You must either specify src or image.', 1382284106);
        }

        try {
            $image = $this->imageService->getImage(
                $this->arguments['src'],
                $this->arguments['image'],
                $this->arguments['treatIdAsReference']
            );

            // --- crop handling ---
            $cropString = $this->arguments['crop'];

            if ($cropString === null && $image->hasProperty('crop') && $image->getProperty('crop')) {
                $cropString = $image->getProperty('crop');
            }

            $cropVariants = CropVariantCollection::create((string)$cropString);
            $cropVariant = $this->arguments['cropVariant'] ?: 'default';
            $cropArea = $cropVariants->getCropArea($cropVariant);

            $processingInstructions = [
                'width' => $this->arguments['width'],
                'height' => $this->arguments['height'],
                'minWidth' => $this->arguments['minWidth'],
                'minHeight' => $this->arguments['minHeight'],
                'maxWidth' => $this->arguments['maxWidth'],
                'maxHeight' => $this->arguments['maxHeight'],
                'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];

            $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

            $imageUri = $this->imageService->getImageUri(
                $processedImage,
                (bool)$this->arguments['absolute']
            );

            // --- apply HTML attributes (IMPORTANT v13 change) ---
            foreach ($this->additionalArguments as $name => $value) {
                $this->tag->addAttribute($name, $value);
            }

            // --- focus area ---
            if (!$this->tag->hasAttribute('data-focus-area')) {
                $focus = $cropVariants->getFocusArea($cropVariant);
                if (!$focus->isEmpty()) {
                    $this->tag->addAttribute(
                        'data-focus-area',
                        $focus->makeAbsoluteBasedOnFile($image)
                    );
                }
            }

            // --- mandatory attributes ---
            $this->tag->addAttribute('src', (string)$imageUri);
            $this->tag->addAttribute('width', (string)$processedImage->getProperty('width'));
            $this->tag->addAttribute('height', (string)$processedImage->getProperty('height'));

            // --- alt/title fallback ---
            $alt = $image->getProperty('alternative');
            $title = $image->getProperty('title');

            if (!$this->tag->hasAttribute('alt')) {
                $this->tag->addAttribute('alt', (string)$alt);
            }

            if (!$this->tag->hasAttribute('title') && $title) {
                $this->tag->addAttribute('title', (string)$title);
            }


        } catch (ResourceDoesNotExistException $e) {
            return $this->renderFallback('1509741911', $e->getMessage());
        } catch (\UnexpectedValueException $e) {
            return $this->renderFallback('1509741912', $e->getMessage());
        } catch (\RuntimeException $e) {
            return $this->renderFallback('1509741913', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->renderFallback('1509741914', $e->getMessage());
        } catch (\Exception $e) {
            return $this->renderFallback('1509741915', $e->getMessage());
        }


        return $this->tag->render();
    }


    protected function renderFallback(string $id, string $message): string
    {
        $src = $this->arguments['src'] ?: '[invalid image]';

        return '<img src="' . htmlspecialchars((string)$src) . '" '
            . 'title="' . $id . ': ' . htmlspecialchars($message) . '" />';
    }

}