<?php

# require_once 'Sitemap.php';

$sm = new Sitemap();
$sm->initialize('/usually/web/server/root/directory/or/public/html');
$include_img = true; // set this to true
$images = [ // only url key is required
    ["url"=> "https://example.com/first.jpg", "title"=> "First", "caption"=> "First Picture", 'geo'=> "Tehran,Iran"],
    ["url"=> "https://example.com/pizza.jpg", "caption"=> "Pizza Picture"],
    ["url"=> "https://example.com/juice.jpg"]
];

try {
    $sm->begin(0, null, $include_img);
} catch (InvalidSitemap $e) {
    exit($e->getMessage());
}

try {
    $sm->set('https://example.com/gallery', 'daily', null, '0.9', [

    ]);
} catch (InvalidSitemap $e) {
    $sm->done();  // do not forget this, its true we encounter error, but xml file is already open and we need to close it
    exit($e->getMessage());
}
$sm->done();

$sm->mk_index("http://example.com/");