<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Templating/Virty.php';

use Qaribou\Templating\Virty;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

// Build a new document
$virty = new Virty();

// Simple arrays are simple, and can be easily mapped in later
$shop_cart = [
    ['price' => 500, 'name' => 'fancy pants'],
    ['price' => 40, 'name' => 'discount jeans'],
    ['price' => 600, 'name' => 'gold watch'],
];

// Generators are Traversable, so we can render in that way too
function doTimes($foo = 5)
{
    for ($i = 0; $i < $foo; $i++) {
        yield ['li', ['class' => 'doing'], 'Do number ' . $i];
    }
}

// async! Put out all your requests to load data from curl or the DB upfront, so they
// load concurrently instead of waiting one after another! Easy to render with virty.
// Map a list of URLs to promises, which will resolve to virty later. Much cleaner
// without mixed html + PHP (or a long string concat).
$client = new Client(['base_uri' => 'http://janeswalk.org/canada/']);
$cities = array_map(
    function ($city) use ($client) {
        return $client->getAsync("{$city}/json")->then(function ($response) {
            return json_decode($response->getBody(), true);
        });
    },
    ['calgary', 'regina', 'edmonton']
);

$virty->doc->appendChild($virty->createNode(
    ['main', ['data-name' => 'main section'], [
        ['h1', null, 'Hello to all! Here\'s how I template! It\'s concise, easy to manage datasets & works well with special chars.'],
        ['p', ['class' => 'foobar'], 'Expensive items: ', [
            ['ul', null, [
                ['li', ['class' => 'test'], '<script>alert("I am hax0ring you!");</script>'],
            ], array_map(
                function ($item) {
                    return ['li', ['class' => 'expensive_item'], $item['name']];
                },
                array_filter(
                    $shop_cart,
                    function ($item) {
                        return $item['price'] > 200;
                    }
                )
            ), doTimes(3)],
        ]],
        ['article', null, [
            ['img', ['src' => 'foo.png']],
            ['h1', null, 'Cities'],
            ['ul', ['class' => 'cities'], array_map(function ($city) {
                return ['li', null, [
                    ['h1', null, $city['name']],
                    ['p', null, $city['shortDescription']],
                ]];
            }, Promise\unwrap($cities))],
        ]],
    ]]
));
echo $virty->doc->saveHTML();
