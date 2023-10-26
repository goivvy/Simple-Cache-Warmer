<?php
/**
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@goivvy.com so we can send you a copy immediately.
 *
 * @component  Goivvyllc_CacheWarmer
 * @copyright  Copyright (c) 2023 GOIVVY LLC (https://www.goivvy.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Goivvy.com <sales@goivvy.com>
 */

namespace Goivvyllc\CacheWarmer\Cron;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsCollectionFactory;
use Magento\Cms\Helper\Page;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility;

class Warmer
{
   public const IS_ENABLED = "gcachewarmer/general/enable";

   private $_storeManager;
   private $_scopeConfig;
   private $_cmsPageCollectionFactory;
   private $_categoryPageCollectionFactory;
   private $_productPageCollectionFactory;
   private $_productVisibility;
   private $_pageHelper;
   
   /** Initial construction
   *
   */
   public function __construct(StoreManagerInterface $storeManager 
                              ,ScopeConfigInterface $scopeConfig
                              ,CmsCollectionFactory $_cmsPageCollectionFactory
                              ,CategoryCollectionFactory $_categoryPageCollectionFactory
                              ,ProductCollectionFactory $_productPageCollectionFactory
                              ,Visibility $_productVisibility
                              ,Page $_pageHelper)
   {
      $this->_storeManager = $storeManager; 
      $this->_scopeConfig = $scopeConfig;
      $this->_cmsPageCollectionFactory = $_cmsPageCollectionFactory;
      $this->_categoryPageCollectionFactory = $_categoryPageCollectionFactory;
      $this->_productPageCollectionFactory = $_productPageCollectionFactory;
      $this->_productVisibility = $_productVisibility;
      $this->_pageHelper = $_pageHelper;
   }

   /** Run a cache warming process
    *  @return void
    */
   public function execute()
   {
      $stores = $this->_storeManager->getStores(); 
      foreach($stores as $id => $store)
      {
        if(!$store->isActive() || !$this->_scopeConfig->getValue(static::IS_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$id)) continue; 
        $this->_fetchStore($store); 
      }
   }
   
   /** Warm pages by store id
    * @return void
    */
   private function _fetchStore($store)
   { 
      $this->_requestUrl($this->_storeManager->getStore($store->getId())->getBaseUrl());

      $cmsPages = $this->_cmsPageCollectionFactory->create()
                  ->addStoreFilter($store->getId()) 
                  ->addFieldToFilter('is_active',1);
      foreach($cmsPages as $cms) $this->_requestUrl($this->_pageHelper->getPageUrl($cms->getPageId()));
       
      $categoryPages = $this->_categoryPageCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('is_active',1)
                        ->setStore($store)
                        ->joinUrlRewrite();
      foreach($categoryPages as $category) $this->_requestUrl($category->getUrl());
       
      $productPages = $this->_productPageCollectionFactory->create()
                        ->addAttributeToSelect('*') 
                        ->addStoreFilter($store->getId())
                        ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
                        ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                        ->joinUrlRewrite();
      
      foreach($productPages as $product) $this->_requestUrl($product->getProductUrl());
   }
     
   /** Request a page 
    * @return void
    */
   private function _requestUrl($url)
   {
     sleep(3);
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0");
     curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_VERBOSE, 0);
     curl_setopt($ch, CURLOPT_HEADER, 0);
     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
        ));         
     curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
     curl_exec($ch);
     curl_close($ch);
   }
}
