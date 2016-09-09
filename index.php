<?php
require_once 'Virty.php';
use Qaribou\Templating\Virty;

// Build a new document
$virty = new Virty();

// Simple arrays are simple, and can be easily mapped in later
$shop_cart = [
    ['price' => 500, 'name' => 'fancy pants'],
    ['price' => 40, 'name' => 'discount jeans'],
    ['price' => 600, 'name' => 'gold watch'],
];

// Generators are Traversable, so we can render in that way too
function doTimes($foo = 5) {
    for ($i = 0; $i < $foo; $i++) {
        yield ['li', ['class' => 'doing'], 'Do number ' . $i];
    }
}

// async! Put out all your requests to load data from curl or the DB upfront, so they
// load concurrently instead of waiting one after another! Easy to render with virty.
function getCities(array $names) {
    // Load a webservice
    $mh = curl_multi_init();

    // Load the remote webservice
    foreach ($names as $name) {
        $ch = curl_init('http://janeswalk.org/canada/' . $name . '/json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_multi_add_handle($mh, $ch);
    }

    $active = 0;
    // Run the requests non-blockingly
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc === CURLM_CALL_MULTI_PERFORM && $active > 0);

    return function () use ($mh) {
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
            if ($info = curl_multi_info_read($mh)) {
                $res = json_decode(curl_multi_getcontent($info['handle']), true);
                yield ['li', null, [
                    ['h1', null, $res['name']],
                    ['p', null, $res['shortDescription']],
                ]];
            }
        } while ($running > 0);
        curl_multi_close($mh);
    };
}

$cities = getCities(['calgary', 'regina', 'edmonton']);
echo $virty->doc->saveHTML( $virty->createNode(
    ['main', ['data-name' => 'main section'], [
        ['h1', null, 'Hello to all! Here\'s how I template! It\'s concise, easy to manage datasets & works well with special chars.'],
        ['p', ['class' => 'foobar'], 'Expensive items: ', [
            ['ul', null, [
                ['li', ['class' => 'test'], '<script>alert("I am hax0ring you!");</script>'],
            ], array_map(
                function ($item) { return ['li', ['class' => 'expensive_item'], $item['name']]; },
                array_filter(
                    $shop_cart,
                    function ($item) { return $item['price'] > 200; }
                )
            ), doTimes(3)],
        ]],
        ['article', null, [
            ['h1', null, 'Cities'],
            ['ul', ['class' => 'cities'],  $cities()],
        ]],
    ]]
) ), PHP_EOL;
