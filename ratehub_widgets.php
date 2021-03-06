<?php
/*
 Plugin Name: RateHub Widgets
 Description: Provides WordPress shortcodes for RateHub widgets.
 Version: 1.0.3
 Author: RateHub.ca
 License: GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
class RateHubWidgetPlugin {
    const WIDGET_BASE_URL = 'https://www.ratehub.ca/widgets/';
    const META_KEY = '_ratehub_widgets';

    static $widgets;

    static $defaultAttributes = array(
        'lang' => null,

        // affiliate attributes
        'aff_id' => null,
        'offer_id' => null,
        'file_id' => null,
        'url_id' => null,
        'source' => null,
        'aff_sub' => null,
        'aff_sub2' => null,
        'aff_sub2' => null,
        'aff_sub3' => null,
        'aff_sub4' => null,
        'aff_sub5' => null,
        'aff_ref' => null
    );

    static function fetchSnippet($url, $params) {
        $params['snippet'] = 'snippet';
        $url = "$url?" . http_build_query($params);

        if(WP_DEBUG)
            return file_get_contents($url);
        else
            return @file_get_contents($url);
    }

    static function makeKey($tag, $attr) {
        if($attr)
            return $tag . ';' . implode(';', array_keys($attr)) . ';' . implode(';', array_values($attr));
        return $tag;
    }

    static function mtgTable($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            self::$defaultAttributes,
            $attr);

        $url = self::WIDGET_BASE_URL.'mtg-table.js';
        $snippet = self::fetchSnippet($url, $params);
        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function paymentCalc($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            self::$defaultAttributes,
            $attr);
        $params['cmhc'] = ($tag === 'ratehub_cmhc_calc' ? 'only' : null);
        $params['ltt'] = ($tag === 'ratehub_ltt_calc' ? 'only' : null);

        $url = self::WIDGET_BASE_URL.'calc-payment.js';
        $snippet = self::fetchSnippet($url, $params);
        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function cmhcCalc($attr, $content, $tag) {
        return self::paymentCalc($attr, $content, $tag);
    }

    static function lttCalc($attr, $content, $tag) {
        return self::paymentCalc($attr, $content, $tag);
    }

    static function ccTable($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            self::$defaultAttributes,
            $attr);

        $url = self::WIDGET_BASE_URL.'/cc-table.js';
        $snippet = self::fetchSnippet($url, $params);
        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function render($attr, $content, $tag) {
        global $post;
        $ref = self::makeKey($tag, $attr);

        if(empty(self::$widgets))
            self::$widgets = get_post_meta($post->ID, self::META_KEY, true);

        return self::$widgets[$ref];
    }

    static function filterPosted($postId) {
        self::$widgets = array();

        $post = get_post($postId);

        remove_shortcode('ratehub_payment_calc');
        remove_shortcode('ratehub_cmhc_calc');
        remove_shortcode('ratehub_ltt_calc');
        remove_shortcode('ratehub_mortgage_table');
        remove_shortcode('ratehub_credit_card_table');

        add_shortcode('ratehub_payment_calc', array('RateHubWidgetPlugin', 'paymentCalc'));
        add_shortcode('ratehub_cmhc_calc', array('RateHubWidgetPlugin', 'cmhcCalc'));
        add_shortcode('ratehub_ltt_calc', array('RateHubWidgetPlugin', 'lttCalc'));
        add_shortcode('ratehub_mortgage_table', array('RateHubWidgetPlugin', 'mtgTable'));
        add_shortcode('ratehub_credit_card_table', array('RateHubWidgetPlugin', 'ccTable'));

        do_shortcode($post->post_content);

        update_post_meta($postId, self::META_KEY, self::$widgets);
    }
}
add_action('save_post', array('RateHubWidgetPlugin', 'filterPosted'));

add_shortcode('ratehub_payment_calc', array('RateHubWidgetPlugin', 'render'));
add_shortcode('ratehub_cmhc_calc', array('RateHubWidgetPlugin', 'render'));
add_shortcode('ratehub_ltt_calc', array('RateHubWidgetPlugin', 'render'));
add_shortcode('ratehub_mortgage_table', array('RateHubWidgetPlugin', 'render'));
add_shortcode('ratehub_credit_card_table', array('RateHubWidgetPlugin', 'render'));
