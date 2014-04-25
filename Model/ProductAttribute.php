<?php
App::uses('MagentoAppModel', 'Magento.Model');

class ProductAttribute extends MagentoAppModel {

	public $belongsTo = array(
		'Magento.ProductAttributeSet'
	);

	protected $_cachedMethods = array(
		'info', 
		'list', 
		'listWithInfo'
	);

	public function listWithInfo($args = array()) {
		$cacheKey = $this->_generateCacheKey(1);
		if ($cacheKey) {
			$results = Cache::read($cacheKey);
			if ($results !== false) {
				return $results;
			}
		}

		$sets = $this->ProductAttributeSet->list();
		if (empty($sets)) {
			return false;
		}

		$results = array();
		$attrIds = array();
		foreach ($sets as $set) {
			// getting all attributes in set
			$data = $this->list($set['set_id']);

			// get info for each attribute
			$calls = array();
			foreach ($data as $item) {
				$calls[] = array('product_attribute.info', $item['attribute_id']);
			}
			$data = $this->multiCall($calls);
			foreach ($data as $item) {
				if (!in_array($item['attribute_id'], $attrIds)) {
					$item['id'] = $item['attribute_id'];
					$results[] = $item;
					$attrIds[] = $item['attribute_id'];
				}
			}
		}

		if ($cacheKey) {
			Cache::write($cacheKey, $results);
		}
		return $results;
	}
}
