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

namespace Goivvyllc\CacheWarmer\Model\Config\Source;

class Frequency implements \Magento\Framework\Option\ArrayInterface
{
   /** 
    * Options getter
    *
    * @return array
    */
   public function toOptionArray()
   {
     return [['value' => '0 * * * *', 'label' => __('Every 1 hour')]
            ,['value' => '0 */3 * * *', 'label' => __('Every 3 hours')]
            ,['value' => '0 */6 * * *', 'label' => __('Every 6 hours')]
            ,['value' => '0 */12 * * *', 'label' => __('Every 12 hours')]
            ,['value' => '0 0 * * *', 'label' => __('Every 24 hours')]];
   }
    
   /**
    * Get options in key-value pair
    *
    * @return array
    */
   public function toArray()
   {
     return ['0 * * * *' => __('Every 1 hour')
            ,'0 */3 * * *' => __('Every 3 hours')
            ,'0 */6 * * *' => __('Every 6 hours')
            ,'0 */12 * * *' => __('Every 12 hours')
            ,'0 0 * * *' => __('Every 24 hours')];
   }
}
