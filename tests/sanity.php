<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Templating/Virty.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Qaribou\Templating\Virty;
use function Qaribou\Templating\createElement as ce;

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
        yield ce('li', ['class' => 'doing'], 'Do number ' . $i);
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

echo Virty::render(ce('main', ['class' => 'hello'], 'Hello, ', ce('span', [], 'World!')));
echo Virty::render(
    ce('main', ['data-name' => 'main section'],
        ce('h1', null, 'Hello to all! Here\'s how I template! It\'s concise, easy to manage datasets & works well with special chars.'),
        ce('p', ['class' => 'foobar'], 'Expensive items: ',
            ce('ul', null,
                ce('li', ['class' => 'test'], '<script>alert("I am hax0ring you!");</script>'),
                ...array_map(
                    function ($item) {
                        return ce('li', ['class' => 'expensive_item'], $item['name']);
                    },
                    array_filter(
                        $shop_cart,
                        function ($item) {
                            return $item['price'] > 200;
                        }
                    )
                ),
                ...doTimes(3)
            )
        ),
        $immar->map(function ($v) { return ce('p', null, $v); }),
        ce('article', null,
            ce('img', ['src' => 'foo.png']),
            ce('h1', null, 'Cities'),
            ce('ul', ['class' => 'cities'],
                ...array_map(
                    function ($city) {
                        return (
                            ce('li', null,
                                ce('h1', null, $city['name']),
                                ce('p', null, $city['shortDescription'])
                            )
                        );
                    },
                    Promise\unwrap($cities)
                )
            )
        )
    )
);
