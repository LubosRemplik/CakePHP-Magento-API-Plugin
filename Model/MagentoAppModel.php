<?php
App::uses('AppModel', 'App.Model');
App::uses('Inflector', 'Utility');

/**
 * Magento AppModel with methods for all models, __call method makes api calls available
 * with just creating and extending this model
 *
 * @author		Luboš Remplík <lubos@on-idle.com>
 * @copyright	Copyright (c) on-IDLE Ltd. (http://www.on-idle.com)
 * @license		http://www.opensource.org/licenses/mit-license.php MIT License
 */
class MagentoAppModel extends AppModel {
	
	/**
	 * Using soap data source
	 *
	 * @var string
	 * @access public
	 */
	public $useDbConfig = 'soap';

	/**
	 * Do not use table for api models
	 *
	 * @var mixed
	 * @access public
	 */
	public $useTable = false;

	/**
	 * Chached methods for model, should be only get methods
	 *
	 * @var array
	 * @access public
	 */
	protected $_cachedMethods = array();

	/**
	 * Creates and issue call to magento api without creating specific method
	 *
	 * @param mixed $method
	 * @param mixed $args
	 * @access public
	 * @return array with response results
	 */
	public function __call($method, $args) {
		$cacheKey = $this->_generateCacheKey();
		if ($cacheKey) {
			$results = Cache::read($cacheKey);
			if ($results !== false) {
				return $results;
			}
		}
		$model = Inflector::underscore($this->name);
		$results[$this->alias] = $this->query('call', array_merge(
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

	/**
	 * Generate cache key unique to the model, method and params
	 *
	 * @param int $index
	 * @access public
	 * @return string cache key
	 */
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

	/**
	 * Return session from cache when avaiable and newer than 1 hour, 
	 * otherwise issue call to generate session
	 *
	 * @access public
	 * @return session string
	 */
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
