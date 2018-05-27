<?php

/**
 * @package    Sitemap
 * @author     Mohammadhzp
 * @copyright  2018 Mohammadhzp
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/mohammadhzp/sitemap
 * @link       https://mohammadhzp.ir
 */


class InvalidSitemap extends Exception{}


class Sitemap {
    /**
     * @var string
     */
    public $name = 'sitemap';

    /**
     * @var string
     */
    public $path;

    /**
     * @var int
     */
    public $current_item_size;

    /**
     * @var int
     */
    public $index = 0;

    /**
     * @var resource | null
     */
    private $fh = null;

    /**
     * @var $fs;
     */
    private $fs;


    const LIMIT = 45000; // I didn't use 50000 because I wanted to avoid file size issue and stuff without checking

    /**
     * @param string $path -> full path to the directory where sitemap(s) will be placed
     * @return $this
     */
    function initialize($path) {
        $this->path = rtrim($path, '/') . '/';
        return $this;
    }

    /**
     * @param int $current_item_size -> You need to set a flag in database, and count those rows and pass it here
     *        In case you have only one sitemap(in another word,you never reach Sitemap::limit), simply pass 0 as value
     * @param null|string $name -> desired filename of sitemap, default is "sitemap"
     * @param bool $include_img
     * @return $this
     * @throws InvalidSitemap
     */
    function begin($current_item_size=0, $name=null, $include_img=false) {
        $this->current_item_size = $current_item_size;
        $this->index = (int) ($this->current_item_size / self::LIMIT);
        $name === null or $this->name = $name;
        $this->fh === null or @fclose($this->fh);

        $fp = "{$this->path}{$this->name}-{$this->index}.xml";
        $this->fh = fopen($fp, "a+");
        $this->fs = filesize($fp);
        $len = 9;
        if ($this->fs - $len > 0) { // updating xml
            while (true) { // dirty hack, no need to fix since its light and fast
                if ($len > 20) {
                    throw new InvalidSitemap("Make sure there is nothing after </urlset> in xml");
                }
                fseek($this->fh, $this->fs - $len);
                if (trim(fread($this->fh, 10)) == '</urlset>') {
                    break;
                }
                $len += 1;
            }
            ftruncate($this->fh, $this->fs - $len);
            return $this;
        }
        fwrite($this->fh, '<?xml version="1.0" encoding="UTF-8"?>');
        $img = $include_img ? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' : '';
        fwrite($this->fh, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'. $img .'>' );

        return $this;
    }

    /**
     * @param string $loc -> URL
     * @param string $cf -> change frequency, values can be: always,hourly,daily,weekly,monthly,yearly,never
     * @param null|int|string $ts -> unix time or a valid string for strtotime
     * @param string|int|float $p -> priority from 0.0 to 1.0
     * @param null|array $images -> an array of images an example would be:
     *        [["url"=> "full_url", "title"=> "", "caption"=> "", geo=> "Tehran,Iran"]]
     *        Only "url" key is required
     * @return $this
     * @throws InvalidSitemap
     */
    function set($loc, $cf, $ts=null, $p='0.8', $images=null) {
        if ($this->current_item_size % self::LIMIT == 0 && $this->current_item_size > 0) {
            $this->done()->begin($this->current_item_size, $this->name);
        }
        $output = "<url>";
        $output .= "<loc>" . htmlspecialchars($loc) . "</loc>";
        $output .= "<changefreq>{$cf}</changefreq>";
        $ts === null or $output .= "<lastmod>" . $this->date_handler($ts) . "</lastmod>";
        $p === null or $output .= "<priority>{$p}</priority>";
        if ($images !== null) {
            foreach ($images as $photo) {
                $output .= '<image:image>';
                $output .= "<image:loc>" . htmlspecialchars($photo['url']) . "</image:loc>";
                !isset($photo['title']) or $output .= "<image:title>{$photo['title']}</image:title>";
                !isset($photo['caption']) or $output .= "<image:caption>{$photo['caption']}</image:caption>";
                !isset($photo['geo']) or $output .= "<image:geo_location>{$photo['geo']}</image:geo_location>";
                $output .= '</image:image>';
            }
        }
        $output .= "</url>";
        fwrite($this->fh, $output);
        ++$this->current_item_size;
        return $this;
    }

    /**
     * @return $this
     */
    function done() {
        fwrite($this->fh, "</urlset>");
        $this->clean();
        return $this;
    }

    /**
     * @param string $full_url -> you need full URL + full directories name(if any)
     * @param string $xml_name -> filename with extension, example: sitemap.xml
     * @param array|string $exclude -> exclude some xml files from index
     * @return $this
     */
    function mk_index($full_url, $exclude=[], $xml_name='sitemap.xml') {
        is_array($exclude) or $exclude = [$exclude];
        $exclude[] = $xml_name; // exclude index sitemap
        $full_url = rtrim($full_url, '/') . '/';
        $output = '<?xml version="1.0" encoding="UTF-8"?>';
        $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $file_name = glob("{$this->path}*.xml");
        foreach ($file_name as $xml_path) {
            $name = basename($xml_path);
            if (in_array($name, $exclude)) {
                continue;
            }
            $lm = filemtime($xml_path);
            $output .= "<sitemap><loc>{$full_url}{$name}</loc><lastmod>{$this->date_handler($lm)}</lastmod></sitemap>";
        }
        $output .= '</sitemapindex>';
        $fh = fopen("{$this->path}{$xml_name}", 'w+');
        fwrite($fh, $output);
        fclose($fh);
        return $this;
    }

    /**
     * @param array $exclude
     */
    function remove_xml_files($exclude=[]) {
        foreach (glob("{$this->path}*.xml") as $xml_path) {
            $name = basename($xml_path);
            if (in_array($name, $exclude)) {
                continue;
            }
            @unlink($xml_path);
        }
    }

    function __destruct() {
        $this->clean();
    }

    private function date_handler($ts) {
        return date('Y-m-d', $ts ? (ctype_digit($ts) ? $ts : strtotime($ts)): time());
    }

    private function clean() {
        $this->fh === null or @fclose($this->fh);
    }
}

