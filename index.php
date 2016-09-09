<?php
require_once 'Virty.php';
use Qaribou\Templating\Virty;

$virty = new Virty();

$shop_cart = [
	['price' => 500, 'name' => 'fancy pants'],
	['price' => 40, 'name' => 'discount jeans'],
	['price' => 600, 'name' => 'gold watch'],
];

echo $virty->doc->saveHTML( $virty->createNode(
	['main', ['data-name' => 'main section'], [
		['h1', null, 'Hello to all! Here\'s how I template! It\'s concise, easy to manage datasets & works well with special chars.'],
		['p', ['class' => 'foobar'], 'Expensive items: ', [
			['ul', null,
				[
					['li', ['class' => 'test'], 'Hello'],
					['li', null, 'World!'],
				],
				array_map(
					function ($item) { return ['li', ['class' => 'expensive_item'], $item['name']]; },
					array_filter(
						$shop_cart,
						function ($item) { return $item['price'] > 200; }
					)
				)
			],
		]],
		['p', null, [['strong', null, 'Yelling!']]],
	]]
) ), PHP_EOL;
