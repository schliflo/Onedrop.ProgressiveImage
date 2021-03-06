<?php
namespace Onedrop\ProgressiveImage\Domain\Model\ThumbnailGenerator;

use Onedrop\ProgressiveImage\Domain\Model\Adjustment\ProgressiveImageAdjustment;
use TYPO3\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use TYPO3\Media\Domain\Model\Thumbnail;
use TYPO3\Media\Domain\Model\ThumbnailGenerator\ImageThumbnailGenerator;
use TYPO3\Media\Exception\NoThumbnailAvailableException;

class ProgressiveImageThumbnailGenerator extends ImageThumbnailGenerator
{

    /**
     * @param Thumbnail $thumbnail
     * @return void
     * @throws NoThumbnailAvailableException
     */
    public function refresh(Thumbnail $thumbnail)
    {
        try {
            $adjustments = [
                new ResizeImageAdjustment(
                    [
                        'width' => $thumbnail->getConfigurationValue('width'),
                        'maximumWidth' => $thumbnail->getConfigurationValue('maximumWidth'),
                        'height' => $thumbnail->getConfigurationValue('height'),
                        'maximumHeight' => $thumbnail->getConfigurationValue('maximumHeight'),
                        'ratioMode' => $thumbnail->getConfigurationValue('ratioMode'),
                        'allowUpScaling' => $thumbnail->getConfigurationValue('allowUpScaling')
                    ]
                ),
                new ProgressiveImageAdjustment([])
            ];

            $processedImageInfo = $this->imageService->processImage($thumbnail->getOriginalAsset()->getResource(),
                $adjustments);

            $thumbnail->setResource($processedImageInfo['resource']);
            $thumbnail->setWidth($processedImageInfo['width']);
            $thumbnail->setHeight($processedImageInfo['height']);
        } catch (\Exception $exception) {
            $message = sprintf('Unable to generate thumbnail for the given image (filename: %s, SHA1: %s)',
                $thumbnail->getOriginalAsset()->getResource()->getFilename(),
                $thumbnail->getOriginalAsset()->getResource()->getSha1());
            throw new NoThumbnailAvailableException($message, 1433109654, $exception);
        }
    }
}
