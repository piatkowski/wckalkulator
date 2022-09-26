---
order: -610
label: "Code Snippets"
icon: file-code
---

# Useful Code Snippets

WC Kalculator is an advanced plugin to deal with additional product fields and calculate custom prices. Some functionalities are very specific and require an individual approach. In this section, we will post some useful code snippets for use in your own projects. You can get even more out of this plugin!

## 1. Product Quick View (lightbox)

Some themes use Quick View to show a lightbox with product details and "Add to Cart" button.
WCK Plugin can't work in a lightbox, so in this code snippet we will show how to replace ATC button with "Customize Product", which will redirect to the product page.

``` # Replace ATC Button with Customize Product Button

    /*
     * Code goes to the functions.php in your theme
     * In most cases QV's content is returned by some AJAX action
     * You can use browser dev tools to get action name and change 'REPLACE_WITH_QUICKVIEW_ACTION'
     */
    
    if(!function_exists('wck_before_atc')) {
        function wck_before_atc() {
            if(
                wp_doing_ajax() && 
                isset($_POST['action']) && 
                $_POST['action'] === 'REPLACE_WITH_QUICKVIEW_ACTION'
            ) {
                echo '<!--'; // start HTML comment to cut ATC button and QTY input field
            }
        }
    }
    
    if(!function_exists('wck_after_atc')) {
        function wck_after_atc() {
            if(
                wp_doing_ajax() && 
                isset($_POST['action']) && 
                $_POST['action'] === 'REPLACE_WITH_QUICKVIEW_ACTION'
            ) {
                global $product;
                $product_url = get_permalink( $product->ID );
                echo '-->'; // end HTML content
                ?>
                <a href="<?php echo esc_url($product_url); ?>" class="single_add_to_cart_button button alt">
                    Customize Product
                </a>
                <?php
            }
        }
    }
    
    add_action('woocommerce_before_add_to_cart_button', 'wck_before_atc');
    add_action('woocommerce_after_add_to_cart_button', 'wck_after_atc');
```

## 2. Move Price block to the different position - for example above the fields

```
if( !function_exists('wck_add_custom_price_block') ) {
    function wck_add_custom_price_block() {
        /* 
         * Here you can customize price block.
         * Tag with id="wckalkulator-price" is required!
         */
?>
        <p class="wckalkulator-price">
            <span id="wckalkulator-price"></span>
        </p>
<?php
    }
}

if( !function_exists('wck_remove_default_price_block') ) {
    function wck_custom_price_block() {
        remove_action(
            'woocommerce_before_add_to_cart_button',  
            array('WCKalkulator\Woocommerce\Product', 'price_block')
        );
        remove_action(
            'woocommerce_after_add_to_cart_button',   
            array('WCKalkulator\Woocommerce\Product', 'price_block')
        );
    }
}

// priority must be > 10 and the hook must be wp_enqueue_scripts
add_action( 'wp_enqueue_scripts', 'wck_remove_default_price_block', 20);

// woocommerce_before_add_to_cart_form - is an example
add_action('woocommerce_before_add_to_cart_form', 'wck_add_custom_price_block');
```