<?php
App::uses('MagentoAppModel', 'Magento.Model');

class ProductAttributeSet extends MagentoAppModel {

	protected $_cachedMethods = array(
		'info', 
		'list', 
	);
}
