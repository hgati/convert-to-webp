<?php declare(strict_types=1);

namespace Hgati\ConvertToWebp\Plugin;

use Magento\Framework\Image;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class ConvertAfterImageSave
{
	protected $scopeConfig;
	protected $logger;

    public function __construct(ScopeConfigInterface $scopeConfig, LoggerInterface $logger)
    {
		$this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @param Image $subject
     * @param mixed $return
     * @param null $destination
     * @return void
     */
    public function afterSave(Image $subject, $return, $destination = null)
    {
		$isLog = (int)$this->scopeConfig->isSetFlag('hgati_converttowebp/general/enable_log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$isEnabled = (int)$this->scopeConfig->isSetFlag('hgati_converttowebp/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if (!$isEnabled && $isLog) {
			$this->logger->info('Hgati_ConvertToWebP:: Skipped due to disabled on system.xml');
			return;
		}
		

		$sourceImagePath = $destination;
		$imageInfo = pathinfo($sourceImagePath);
		if(!isset($imageInfo['extension'])) {
			if($isLog) $this->logger->warning("[Warning] Hgati_ConvertToWebPp:: $imageInfo");
			return;
		}
		$extension = strtolower($imageInfo['extension']);
		if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') {
			$webPImagePath = "$sourceImagePath.ngx.webp";

			$sourceImage = $extension === 'png' ? imagecreatefrompng($sourceImagePath) : imagecreatefromjpeg($sourceImagePath);
			imagewebp($sourceImage, $webPImagePath, 65);
			imagedestroy($sourceImage);
			if($isLog) $this->logger->info("Hgati_ConvertToWebPp:: $webPImagePath (Quality: 65)");
		}
    }
}
