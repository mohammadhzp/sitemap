<?php

# require_once 'Sitemap.php';

// CREATE TABLE IF NOT EXISTS tbl_name(identity(int), title(string), ts(int), in_sitemap(int(1));

function base_url($uri='') {
    return "https://example.com/{$uri}";
}

function fetch_data_for_sitemap_from_database($limit) {
    /*
     * do some SELECT here to fetch data from database, you should get those records which are not marked as indexed
     * i.e: select * from tbl_name where in_sitemap = 0
     */
    return [];
}

function get_number_of_indexed_items() {
    /*
     * here you need to select and get already indexed value
     * i.e: select count(*) from tbl_name where in_sitemap = 1
     */
    $count = 0;
    return $count;
}

function mark_as_indexed($ids) {
    /*
     * You need to update your database and set flags of this ids to 1
     * i.e: update tbl_name set in_sitemap = 1 where identity in($ids)
     */
}
$limit = 100;
$priority = '0.7';
$cf = 'monthly';
$indexed_ids = [];

$path = '/usually/web/server/root/directory/or/public/html';

$current_item_size = get_number_of_indexed_items(); // tell us so far, how much item we've indexed
/*
 * xml names would be: products-0.xml, products-1.xml and so on...
 */
$name = 'products';  // default value is "sitemap", to avoid conflict you should have different $name for different tables
$including_img = false;  // setting this to true if your sitemap contain image info

$full_url = 'https://example.com/';  // this must point to exactly where $path is pointing to
$exclude_xml = ['my_other_unrelated_sitemap.xml']; // if you wan't to put some of your files in sitemap index file
$xml_index_name = 'products.xml'; // we recommend to leave this to use default one

$items = fetch_data_for_sitemap_from_database($limit);



$sm = new Sitemap();
$sm->initialize($path);
try {
    $sm->begin($current_item_size, $name, $including_img);
} catch (InvalidSitemap $e) {
    exit($e->getMessage());
}
foreach ($items as $record) {
    try {
        $title = urlencode($record['title']); // "utf-8 part" of url must be urlencoded
        $sm->set(base_url("products/{$record['identity']}/{$title}"), $cf, $record['ts'], $priority);
        $indexed_ids[] = $record['identity'];
    } catch (InvalidSitemap $e) {
        // do something here, or don't
    }
}
$sm->done(); // you must call it somehow or all of it will burn

if (!empty($indexed_ids)) {
    mark_as_indexed($indexed_ids);
}

$sm->mk_index($full_url, $exclude_xml, $xml_index_name);

// make this work, put it on cron job to run for  everyday(or whatever you like)
// now forget all about it