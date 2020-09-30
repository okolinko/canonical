<?php

namespace Hunters\Canonical\Plugin\Catalog\Helper\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page as ResultPage;

class ViewPlugin
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }


   public function afterPrepareAndRender(\Magento\Catalog\Helper\Product\View $s, $result,  ResultPage $resultPage, $productId, $controller, $params = null)
    {
        try {

            $product = $this->productRepository->getById($productId);
            if ($product){
                $pageConfig = $resultPage->getConfig();
                $assets = $pageConfig->getAssetCollection()->getGroups();
                foreach ($assets as $asset) {
                    if($asset->getProperty('content_type') == 'canonical') {
                        $url = $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
                        $pageConfig->getAssetCollection()->remove($url);
                        $newUrl = $this->newCanonical($product->getUrlModel()->getUrl($product), $productId);
                        $pageConfig->addRemotePageAsset(
                            $newUrl,
                            'canonical',
                            ['attributes' => ['rel' => 'canonical']]
                        );
                    }
                } 
            }
        } catch (NoSuchEntityException $e) {
        file_put_contents(BP . '/var/log/cannonical.log', print_r($e->getMessage(), true) . "\n", FILE_APPEND | LOCK_EX);
        }
    return $result;
    }

    private function newCanonical($url, $productId) {
        $inputUrl = $url;
        $shop = array(325, 326, 327, 328, 329);
        $offers = array(337, 347, 357, 362, 363);
        $outputArray = array();
        preg_match('/\/[a-z]+\/[a-z]+\/[a-z]+\/[a-z]+\/[0-9]+\/[a-z]\/[a-z-0-9A-Z]+\/[a-z]+\/[0-9+]\//', $inputUrl, $outputArray);
        if ($outputArray) {
            $urlArray = explode("/", $outputArray[0]);
            $index = count($urlArray) - 4;
            $corectUrl = '/offers/'. $urlArray[$index];
            if (array_search($productId, $shop)){
                $corectUrl = '/shop/'. $urlArray[$index];
            }
            if (array_search($productId, $offers)){
                $corectUrl = '/offers/'. $urlArray[$index];
            }
            $resultUrl = str_replace($outputArray, $corectUrl, $inputUrl);
            return $resultUrl;
        }
    return $url;

    }

}

