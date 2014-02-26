<?php
App::uses('MagentoAppModel', 'Magento.Model');
class CatalogProduct extends MagentoAppModel {

	protected $_cachedMethods = array('list', 'listWithInfo');

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
