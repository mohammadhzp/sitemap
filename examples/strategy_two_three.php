<?php

# require_once 'Sitemap.php';
$path = '/usually/web/server/root/directory/or/public/html';

$current_item_size = 0; // we don't need this value here
/*
 * xml names would be: products-0.xml, products-1.xml and so on...
 */
$name = 'products';  // default value is "sitemap", to avoid conflict you should have different $name for different tables
$including_img = false;  // setting this to true if your sitemap contain image info

$full_url = 'https://example.com/';  // this must point to exactly where $path is pointing to
$exclude_xml = ['my_other_unrelated_sitemap.xml']; // if you wan't to put some of your files in sitemap index file
$xml_index_name = 'products.xml'; // we recommend to leave this to use default one

$items = [];  #  TODO: Put items here
# i.e: $items = [['url' => 'http://example.com', 'cf' => 'daily', 'ts' => null, 'priority' => '0.7']];



$sm = new Sitemap();
$sm->initialize($path);
/*
 * If you want to delete already existed sitemap files, uncomment next line, if items are more than limit then
 * you need to remove all files and start whole process from the top
 */
// $sm->remove_xml_files([/*To exclude files, put their names here*/]);
try {
    $sm->begin($current_item_size, $name, $including_img);
} catch (InvalidSitemap $e) {
    exit($e->getMessage());
}
foreach ($items as $record) {
    try { // "utf-8 part" of url must be urlencoded
        $sm->set($record['url'], $record['cf'], $record['ts'], $record['priority']);
    } catch (InvalidSitemap $e) {
        // do something here, or don't
    }
}
$sm->done(); // you must call it somehow or all of it will burn

$sm->mk_index($full_url, $exclude_xml, $xml_index_name);
