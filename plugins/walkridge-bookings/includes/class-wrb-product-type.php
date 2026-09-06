<?php
defined('ABSPATH') || exit;

/**
 * Registers the "hg-tour" WooCommerce product type.
 *
 * hg-tour extends Simple product — it is purchasable, has a price, and
 * supports all standard WooCommerce features (coupons, taxes, etc.).
 * The booking widget is shown for both hg-tour products AND any simple/
 * variable product that has the "Enable tour booking" meta checkbox set.
 */
final class WRB_Product_Type
{
    private static ?WRB_Product_Type $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    private function hooks(): void
    {
        add_filter('product_type_selector', [$this, 'add_product_type']);
        add_filter('woocommerce_product_class', [$this, 'product_class'], 10, 2);
        add_filter('woocommerce_product_data_tabs', [$this, 'booking_tab']);
        add_action('admin_footer', [$this, 'product_type_js']);
    }

    public function add_product_type(array $types): array
    {
        $types['hg-tour'] = __('Tour (Walkridge)', 'wr-bookings');

        return $types;
    }

    /** Map hg-tour to WC_Product_HG_Tour (which extends WC_Product_Simple). */
    public function product_class(string $class, string $product_type): string
    {
        if ($product_type === 'hg-tour') {
            return 'WC_Product_HG_Tour';
        }

        return $class;
    }

    /** Add a "Tour Booking" tab to the product data panel. */
    public function booking_tab(array $tabs): array
    {
        $tabs['wrb_booking'] = [
            'label' => __('Tour Booking', 'wr-bookings'),
            'target' => 'wrb_booking_data',
            'class' => ['show_if_simple', 'show_if_hg-tour'],
            'priority' => 62,
        ];

        return $tabs;
    }

    /**
     * Show/hide product sections based on product type via JS.
     * The booking tab should only show for simple / hg-tour products.
     */
    public function product_type_js(): void
    {
        $screen = get_current_screen();
        if (! $screen || $screen->id !== 'product') {
            return;
        }
        ?>
        <script>
        (function($){
            var wrap = $('body.post-type-product');
            // Show shipping / inventory for hg-tour just like simple products.
            wrap.on('woocommerce-product-type-change', function(e, type){
                if(type === 'hg-tour'){
                    $('.show_if_simple').show();
                    $('.hide_if_simple').hide();
                }
            });
        })(jQuery);
        </script>
        <?php
    }
}

/**
 * Product class — extends WC_Product_Simple so hg-tour behaves like a
 * simple purchasable product in every WooCommerce context.
 * Defined on woocommerce_loaded so WC_Product_Simple is available.
 */
add_action('woocommerce_loaded', function () {
    if (class_exists('WC_Product_HG_Tour') || ! class_exists('WC_Product_Simple')) {
        return;
    }
    class WC_Product_HG_Tour extends WC_Product_Simple
    {
        public function __construct($product = 0)
        {
            $this->product_type = 'hg-tour';
            parent::__construct($product);
        }

        public function get_type(): string
        {
            return 'hg-tour';
        }

        public function is_purchasable(): bool
        {
            return true;
        }

        public function is_sold_individually(): bool
        {
            return false;
        }
    }
});
