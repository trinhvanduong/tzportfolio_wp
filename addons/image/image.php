<?php
/*
 Plugin Name: Image
 Plugin URI: https://www.tzportfolio.com/
 Description: This add-on helps you create Image Article for your Portfolio gallery.
 Author: Sonny
 Version: 1.0
 Author URI: https://www.tzportfolio.com/
 Type: mediatype
 Element: image
 Text Domain: tz-portfolio
 */

// No direct access
if ( ! defined( 'ABSPATH' ) && !defined('_JEXEC') ) {
	die();
}
if (defined('_JEXEC')) {
	use Joomla\Filesystem\File;
	jimport('joomla.filesytem.file');
} elseif (defined( 'ABSPATH' )) {

}


class PlgTZ_Portfolio_PlusMediaTypeImage extends TZ_Portfolio_PlusPlugin
{
    protected $autoloadLanguage = true;

    // Display html for views in front-end.
    public function onContentDisplayMediaType($context, &$article, $params, $page = 0, $layout = null){
        if($article){
            if($media = $article -> media){
                $image  = null;
                if(isset($media -> image)){
                    $image  = clone($media -> image);


                    if(isset($image -> url) && $image -> url) {
                        if ($size = $params->get('mt_image_size', 'o')) {
                            if (isset($image->url) && !empty($image->url)) {
                                $image_url_ext = \JFile::getExt($image->url);
                                $image_url = str_replace('.' . $image_url_ext, '_' . $size . '.'
                                    . $image_url_ext, $image->url);
                                $image->url = JURI::root() . $image_url;
                            }

                            if (isset($image->url_detail) && !empty($image->url_detail)) {
                                $image_url_ext = \JFile::getExt($image->url_detail);
                                $image_url = str_replace('.' . $image_url_ext, '_' . $size . '.'
                                    . $image_url_ext, $image->url_detail);
                                $image->url_detail = JURI::root() . $image_url;
                            }
                        }
                    }
                }
                $this -> setVariable('image', $image);
            }
            $this -> setVariable('item', $article);

            return parent::onContentDisplayMediaType($context, $article, $params, $page, $layout);
        }
    }
}