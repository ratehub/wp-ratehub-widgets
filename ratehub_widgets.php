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
    static function chart($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('series' => '', 'from' => null, 'to' => null, 'hide_legend' => false, 'lang' => 'en'),
            $attr);

        $url = self::WIDGET_URL . '/chart.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);

        self::$widgets[$ref] = $snippet;

        return $content;
    }

    static function smallRates($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('province' => null, 'lang' => 'en'),
            $attr);
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/rates-small.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);

        self::$widgets[$ref] = $snippet;

        return $content;
    }
    static function rateComparison($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('size' => null, 'purchase' => true, 'lang' => 'en'),
            $attr);
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/all-rates.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);

        self::$widgets[$ref] = $snippet;

        return $content;
    }
    static function paymentCalc($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('size' => null, 'purchase' => null, 'lang' => 'en'),
            $attr);

        $url = self::WIDGET_URL . '/payment-calc.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
    }
    static function cmhcCalc($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('size' => null, 'purchase' => null, 'lang' => 'en'),
            $attr);
        $params['cmhc']	= 'only';
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/payment-calc.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
    }
    static function lttCalc($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('size' => null, 'purchase' => null, 'lang' => 'en'),
            $attr);
        $params['ltt']	= 'only';
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/payment-calc.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
    }
    static function compactPayment($attr, $content, $tag) {
        $ref = self::makeKey($tag, $attr);
        $params = shortcode_atts(
            array('lang' => 'en'),
            $attr);
        $params['snippet']	= 'snippet';

        $url = self::WIDGET_URL . '/compact-payment.php?' . http_build_query($params);
        $snippet = self::fetchSnippet($url);
        self::$widgets[$ref] = $snippet;

        return $content;
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

        add_shortcode('ratehub_chart', array('RateHubWidgetPlugin', 'chart'));
        add_shortcode('ratehub_small', array('RateHubWidgetPlugin', 'smallRates'));
        add_shortcode('ratehub_comparison', array('RateHubWidgetPlugin', 'rateComparison'));
        add_shortcode('ratehub_payment_calc', array('RateHubWidgetPlugin', 'paymentCalc'));
        add_shortcode('ratehub_cmhc_calc', array('RateHubWidgetPlugin', 'cmhcCalc'));
        add_shortcode('ratehub_ltt_calc', array('RateHubWidgetPlugin', 'lttCalc'));
        add_shortcode('ratehub_affordability_calc', array('RateHubWidgetPlugin', 'affordabilityCalc'));
        add_shortcode('ratehub_compact_payment_calc', array('RateHubWidgetPlugin', 'compactPayment'));
        add_shortcode('ratehub_credit_card_table', array('RateHubWidgetPlugin', 'ccTable'));

        do_shortcode($post->post_content);

        update_post_meta($postId, '_ratehub', self::$widgets);
    }

    static function filterRendered($content) {
        add_shortcode('ratehub_chart', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_small', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_comparison', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_payment_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_cmhc_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_ltt_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_affordability_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_compact_payment_calc', array('RateHubWidgetPlugin', 'render'));
        add_shortcode('ratehub_credit_card_table', array('RateHubWidgetPlugin', 'render'));

        return $content;
    }
}
add_action('save_post', array('RateHubWidgetPlugin', 'filterPosted'));
add_filter('the_content', array('RateHubWidgetPlugin', 'filterRendered'));