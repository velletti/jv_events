<?php
namespace JVE\JvEvents\ViewHelpers;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'img';

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /**
     * @param \TYPO3\CMS\Extbase\Service\ImageService $imageService
     */
    public function injectImageService(\TYPO3\CMS\Extbase\Service\ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', false);
        $this->registerTagAttribute('ismap', 'string', 'Specifies an image as a server-side image-map. Rarely used. Look at usemap instead', false);
        $this->registerTagAttribute('longdesc', 'string', 'Specifies the URL to a document that contains a long description of an image', false);
        $this->registerTagAttribute('usemap', 'string', 'Specifies an image as a client-side image-map', false);

        $this->registerArgument('src', 'string', 'a path to a file, a combined FAL identifier or an uid (int). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead');
        $this->registerArgument('treatIdAsReference', 'bool', 'given src argument is a sys_file_reference record');
        $this->registerArgument('image', 'object', 'a FAL object');
        $this->registerArgument('crop', 'string|bool', 'overrule cropping of image (setting to FALSE disables the cropping set in FileReference)');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');

        $this->registerArgument('width', 'string', 'width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('height', 'string', 'height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('minWidth', 'int', 'minimum width of the image');
        $this->registerArgument('minHeight', 'int', 'minimum height of the image');
        $this->registerArgument('maxWidth', 'int', 'maximum width of the image');
        $this->registerArgument('maxHeight', 'int', 'maximum height of the image');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     * a copy of typo3 Viewhelper but do not throw a stupid  exception !
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws Exception
     * @return string Rendered tag
     */
    public function render()
    {
        if (($this->arguments['src'] === null && $this->arguments['image'] === null) || ($this->arguments['src'] !== null && $this->arguments['image'] !== null)) {
            throw new Exception('You must either specify a string src or a File object.', 1382284106);
        }

        try {
            $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);
            $cropString = $this->arguments['crop'];
            if ($cropString === null && $image->hasProperty('crop') && $image->getProperty('crop')) {
                $cropString = $image->getProperty('crop');
            }
            $cropVariantCollection = CropVariantCollection::create((string)$cropString);
            $cropVariant = $this->arguments['cropVariant'] ?: 'default';
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);
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
            $imageUri = $this->imageService->getImageUri($processedImage, $this->arguments['absolute']);

            if (!$this->tag->hasAttribute('data-focus-area')) {
                $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
                if (!$focusArea->isEmpty()) {
                    $this->tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($image));
                }
            }
            $this->tag->addAttribute('src', $imageUri);
            $this->tag->addAttribute('width', $processedImage->getProperty('width'));
            $this->tag->addAttribute('height', $processedImage->getProperty('height'));

            $alt = $image->getProperty('alternative');
            $title = $image->getProperty('title');

            // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
            if (empty($this->arguments['alt'])) {
                $this->tag->addAttribute('alt', $alt);
            }
            if (empty($this->arguments['title']) && $title) {
                $this->tag->addAttribute('title', $title);
            }
        } catch (ResourceDoesNotExistException $e) {
            // thrown if file does not exist
            return "1509741911: " . $e->getMessage() ;
        } catch (\UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
            return "1509741912: " . $e->getMessage() ;
        } catch (\RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
            return "1509741913: " . $e->getMessage() ;
        } catch (\InvalidArgumentException $e) {
            // thrown if file storage does not exist
            return "1509741914: " . $e->getMessage() ;
        }

        return $this->tag->render();
    }
}
