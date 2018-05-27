**What is this ?**
----------
Very Fast, lightweight and scalable sitemap library for google written in php, it uses no extension and it has no requirement.
it almost has no overhead even on large sitemap files when updating them, what you need to do
is to provide valid data and this library will do the rest for you(including updating sitemap index file). you can easily
use it in your frameworks like Codeigniter.

This library support images. this means you can add one or more images to an item, if you want to add support for vidoes, 
its pretty easy, you can check how I implemented support for images and take it from there.

Please note that this library won't use urlencode, you need to encode utf-8 part of your urls yourself, after reading README file
checkout example folder for mor examples

php 5.4+ is required to run this library

----------
**Strategies ?**
----------
1. Best strategy would be where you have a database, you can set a flag(boolean column) in the table, i.e: `in_sitemap`.
you can write a little script to feed this library with right data and put it to cronjob and forget all about sitemap, 
this library is your **best** choice, for more details see example folder

2. Next strategy would be where you know never reach item limitation for a sitemap file(which is 45000 in this library
and you can change it up to 50000 if want to), this library is your **best**  choice

3. Last strategy is where you **don't** have any database and you will **reach** item limitation for a sitemap file,
there are libraries out there which **only** cover this strategy(which is not ideal at all).You can still use this 
library, but you need to delete xml files before you start,
If you have a few items, this strategy is okay, but I don't recommend it at all

----------
**Quick start**
----------
```php
require_once 'Sitemap.php';
$sm = new Sitemap();
$sm->initialize('/path/to/destination/dir/usually/root/of/web/server')->
     begin()->
     set("http://example.com/gallery", "daily")->
     done()->
     mk_index("http://example.com/");
```

This quick start will create a file named `sitemap-0.xml` and add one item to it, then it will create a file named
`sitemap.xml`, which is a `sitemap index file`, you can submit the latter to google. make sure your path is absolute

Please note you can set options to override defaults, also there are more advanced use cases, please see example folder 
for more details

----------
**Reference**
----------

**`initialize($path)`**: You need to call this method before everything. 

`$path` is the path to the directory where you want
 to place your xml files there, this is usually the root of your web server
 
 
 
**`begin($current_item_size=0, $name=null, $include_img=false)`**: to prepare xml file and add item(s) to it, call this
 method, 
 
`$current_item_size` is the size of already indexed items, you normally fetch its value from database
(i.e: `select COUNT(*) FROM table WHERE in_sitemap = 1`)

`$name` is the name of our xml site, its default is `sitemap`, note that this name won't make conflict with "sitemap index file", so don't worry

`$include_img`: will set a flag in xml files so that you can include your images in sitemap file too.

**`set($loc, $cf, $ts=null, $p='0.8', $images=null)`**: to add item to your sitemap, you need to call this method.

`$loc` is the url you want to index,

`$cf` is change frequency, its value can be: always,hourly,daily,weekly,monthly,yearly,never

`$p` is priority and its value are between 0.0 and 1.0

`$images` is an array of arrays, an example would be `[["url"=> "full_url", "title"=> "", "caption"=> "", "geo"=> "Tehran,Iran"]]`
Only url key is require and other parts are optional

> Please note that you need to call `begin()` with `$include_img=true` in order to have a valid sitemap

**`done()`**: you need to call this method when you've done adding items, this will save a valid xml


**`mk_index($full_url, $exclude=[], $xml_name='sitemap.xml')`**: using this method, you can create a sitemap index file.

`$full_url` is your full url, this url must point to the right directory. example: https://example.com

`$exclude` is an array of file names(not full path, just basename). to ignores specified xml files

`$xml_name` is the name of the sitemap index file, we recommend to leave it as is


**`remove_xml_files($exclude=[])`**: remove all files at our path

`$exclude` is an array of file names(not full path, just basename) to ignore when removing