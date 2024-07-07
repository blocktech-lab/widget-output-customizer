<?php
/**
 * Class Widget_Output_Customizer
 *
 * A library that allows developers to filter the output of any WordPress widget.
 */
class Widget_Output_Customizer {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('dynamic_sidebar_params', [$this, 'filter_dynamic_sidebar_params'], 9);
    }

    public function filter_dynamic_sidebar_params($sidebar_params) {
        if (is_admin()) {
            return $sidebar_params;
        }

        global $wp_registered_widgets;
        $widget_id = $sidebar_params[0]['widget_id'];

        $wp_registered_widgets[$widget_id]['original_callback'] = $wp_registered_widgets[$widget_id]['callback'];
        $wp_registered_widgets[$widget_id]['callback'] = [$this, 'display_widget'];

        return $sidebar_params;
    }

    public function display_widget() {
        global $wp_registered_widgets;
        $params = func_get_args();
        $widget_id = $params[0]['widget_id'];

        $original_callback = $wp_registered_widgets[$widget_id]['original_callback'];
        $wp_registered_widgets[$widget_id]['callback'] = $original_callback;

        $widget_id_base = $original_callback[0]->id_base;
        $sidebar_id = $params[0]['id'];

        if (is_callable($original_callback)) {
            ob_start();
            call_user_func_array($original_callback, $params);
            $output = ob_get_clean();

            echo apply_filters('widget_output_customizer', $output, $widget_id_base, $widget_id, $sidebar_id);
        }
    }
}