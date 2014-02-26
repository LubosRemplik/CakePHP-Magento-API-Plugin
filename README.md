CakePHP Magento API Plugin
=========================

Requirements
------------
[CakePHP v2.x](https://github.com/cakephp/cakephp)   

How to use it
-------------
1.	Install this plugin for your CakePHP app.   
	Assuming `APP` is the directory where your CakePHP app resides, it's usually `app/` from the base of CakePHP.

	```bash
	cd APP/Plugin
	git clone git://github.com/LubosRemplik/CakePHP-Magento-API-Plugin.git Magento
	```

1.	Set up datasource, example is in Magento/Config/database.php.default (you need to get api user and key in magento admin panel).

1.  Include needed model in your controller or anywhere you want to

	```php
	$uses = array('Magento.CategoryProduct');
	...
	$data = $this->CategoryProduct->listWithInfo();
	debug ($data);
	```

	```php
	$data = ClassRegistry::init('Magento.CategoryProduct')->listWithInfo();
	debug ($data);
	```
