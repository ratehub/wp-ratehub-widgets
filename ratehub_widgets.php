<?php
/*
 Plugin Name: RateHub Widgets
 Version: 0.1
 */
class RateHubWidgetPlugin {
    const WIDGET_URL = 'http://www.ratehub.ca/widgets';

    static $widgets;

    static function fetchSnippet($url, $params) {
        $params['snippet']	= 'snippet';
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
            array('lang' => 'en'),
            $attr);
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/mtg-table.js?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function paymentCalc($attr, $content, $tag, $type = 'payment') {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('lang' => 'en'),
            $attr);
        $params['snippet']	= 'snippet';

        if ($type == 'cmhc') {
            $params['cmhc'] = 'only';
        }
        elseif ($type == 'ltt') {
            $params['ltt']	= 'only';
        }

        $url = self::WIDGET_URL . '/calc-payment.js?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function cmhcCalc($attr, $content, $tag) {
        return self::paymentCalc($attr, $content, $tag, 'cmhc');
    }
    static function lttCalc($attr, $content, $tag) {
        return self::paymentCalc($attr, $content, $tag, 'ltt');
    }

    static function ccTable($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('lang' => 'en'),
            $attr);
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/cc-table.js?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function render($attr, $content, $tag) {
        global $post;
        $ref = self::makeKey($tag, $attr);

        if(empty(self::$widgets))
            self::$widgets = get_post_meta($post->ID, '_ratehub', true);
        return self::$widgets[$ref];
    }

    static function filterPosted($postId) {
        self::$widgets = array();

        $post = get_post($postId);

        add_shortcode('ratehub_payment_calc', array('RateHubWidgetPlugin', 'paymentCalc'));
        add_shortcode('ratehub_cmhc_calc', array('RateHubWidgetPlugin', 'cmhcCalc'));
        add_shortcode('ratehub_ltt_calc', array('RateHubWidgetPlugin', 'lttCalc'));
        add_shortcode('ratehub_mortgage_table', array('RateHubWidgetPlugin', 'mtgTable'));
        add_shortcode('ratehub_credit_card_table', array('RateHubWidgetPlugin', 'ccTable'));

        do_shortcode($post->post_content);

        update_post_meta($postId, '_ratehub', self::$widgets);
    }

    static function filterRendered($content) {
        add_shortcode('ratehub_payment_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_cmhc_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_ltt_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_mortgage_table', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_credit_card_table', array('RateHubWidgetPlugin', 'render'));

        return $content;
    }
}
add_action('save_post', array('RateHubWidgetPlugin', 'filterPosted'));
add_filter('the_content', array('RateHubWidgetPlugin', 'filterRendered'));