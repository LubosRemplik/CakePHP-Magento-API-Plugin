<?php
App::uses('AppModel', 'App.Model');
App::uses('Inflector', 'Utility');
class MagentoAppModel extends AppModel {
	
	public $useDbConfig = 'soap';

	public $useTable = false;

	protected $_cachedMethods = array();

	public function __call($method, $args) {
		$cacheKey = $this->_generateCacheKey();
		if ($cacheKey) {
			$results = Cache::read($cacheKey);
			if ($results !== false) {
				return $results;
			}
		}
		$model = Inflector::underscore($this->name);
		$results = $this->query('call', array_merge(
			array(
				$this->_getSession(),
				sprintf('%s.%s', $model, $method), 
			),
			$args
		));
		if ($cacheKey) {
			Cache::write($cacheKey, $results);
		}
		return $results;
	}

	protected function _generateCacheKey($index = 2) {
		$backtrace = debug_backtrace();
		$cacheKey = array();
		$cacheKey[] = $this->alias;
		if (!in_array($backtrace[$index]['function'], $this->_cachedMethods)) {
			return false;
		}
		if (!empty($backtrace[$index]['function'])) {
			$cacheKey[] = $backtrace[$index]['function'];
		}
		if ($backtrace[$index]['args']) {
			$cacheKey[] = md5(serialize($backtrace[$index]['args']));	
		}
		return implode('_', $cacheKey);
	}

	protected function _getSession() {
		Cache::set(array('duration' => '+1 hour'));
		if (($session = Cache::read('Magento.session')) === false) {
			$ds = $this->getDataSource();
			$session = $this->query('login', array(
				$ds->config['user'], 
				$ds->config['key']
			));
			Cache::set(array('duration' => '+1 hour'));
			Cache::write('Magento.session', $session);
		}
		return $session;
	}
}
