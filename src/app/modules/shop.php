<?php

require_once __DIR__ . '/../models/product.m.php';
require_once __DIR__ . '/../models/product_category.m.php';
require_once __DIR__ . '/../models/order.m.php';

/**
* e-shop
*/
class shopModule extends zModule {

	public $depends_on = ['db', 'images'];

}
