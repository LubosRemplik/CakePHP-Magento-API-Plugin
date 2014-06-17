<?php
App::uses('MagentoAppModel', 'Magento.Model');
App::uses('Hash', 'Utility');
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
	 * Associated hasOne models
	 *
	 * @var array
	 * @access public
	 */
	public $hasMany = array(
		'CatalogProductImage' => array(
			'className' => 'Magento.CatalogProductAttributeMedia'
		)
	);

	/**
	 * Cached methods
	 *
	 * @var array
	 * @access public
	 */
	protected $_cachedMethods = array(
		'info', 
		'list', 
		'listWithInfo'
	);

	public function list2() {
		$data = $this->list();
		$results = Hash::combine(
			$data['CatalogProduct'], 
			'{n}.product_id', '{n}.name'
		);
		ksort($results);
		return $results;
	}

	/**
	 * Gets list of products with all information and images
	 * Firstly gets simple list and then do x requests to get product info & image
	 *
	 * @param array $args
	 * @access public
	 * @return array with results
	 */
	public function listWithInfo($args = array()) {
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

		// get info for each product
		$calls = array();
		foreach ($data as $item) {
			$calls[] = array('catalog_product.info', $item['product_id']);
		}
		$data = $this->multiCall($calls);
		foreach ($data as $item) {
			$item['id'] = $item['product_id'];
			$results[] = $item;
		}

		// get linked products for each product 
		$calls = array();
		foreach ($data as $item) {
			$calls[] = array('catalog_product_link.list', array('type' => 'grouped', 'product' => $item['product_id']));
		}
		$grouped = $this->multiCall($calls);
		foreach ($grouped as $k => $item) {
			$results[$k]['grouped'] = $item;
		}

		// get media (images) for each product
		$calls = array();
		foreach ($results as $item) {
			$calls[] = array('catalog_product_attribute_media.list', $item['product_id']);
		}
		$mediaList = $this->multiCall($calls);
		foreach ($mediaList as $k => $item) {
			$results[$k]['images'] = $item;
		}

		if ($cacheKey) {
			Cache::write($cacheKey, $results);
		}
		return $results;
	}
}
