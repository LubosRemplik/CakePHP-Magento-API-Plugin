<?php
App::uses('MagentoAppModel', 'Magento.Model');
/**
 * Catalog Product api wrapper
 *
 * @author		Luboš Remplík <lubos@on-idle.com>
 * @copyright	Copyright (c) on-IDLE Ltd. (http://www.on-idle.com)
 * @link		http://www.magentocommerce.com/api/soap/catalog/catalogProduct/catalogProduct.html
 * @license		http://www.opensource.org/licenses/mit-license.php MIT License
 */
class CatalogProduct extends MagentoAppModel {

	/**
	 * Cached methods
	 *
	 * @var array
	 * @access public
	 */
	protected $_cachedMethods = array('list', 'listWithInfo');

	/**
	 * Gets list of products with all information
	 * Firstly gets simple list and then do x requests to get product info
	 *
	 * @param array $args
	 * @param int $limit
	 * @access public
	 * @return array with results
	 */
	public function listWithInfo($args = array(), $limit = 20) {
		$cacheKey = $this->_generateCacheKey(1);
		if ($cacheKey) {
			$results = Cache::read($cacheKey);
			if ($results !== false) {
				return $results;
			}
		}

		$data = $this->list($args);
		if (empty($data)) {
			return false;
		}
		
		$results = array();
		$i = 0;
		foreach ($data as $item) {
			$results[] = $this->info($item['product_id']);
			if ($i == $limit) {
				break;
			}
			$i++;
		}
			
		if ($cacheKey) {
			Cache::write($cacheKey, $results);
		}
		return $results;
	}
}
