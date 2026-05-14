<?php
/**
 * Plugin Name: Seller Circle Customizations
 * Description: WooCommerce checkout, registration, payments & styling for Seller Circle
 * Version: 1.0
 * Author: Seller Circle
 */
if (!defined('ABSPATH')) exit;

// ═══════════════════════════════════════════════
// 1. REMOVE UNNECESSARY CHECKOUT FIELDS
// ═══════════════════════════════════════════════
add_filter('woocommerce_billing_fields', 'sc_billing_fields', 20, 2);
function sc_billing_fields($fields, $country = '') {
    $remove = ['billing_company','billing_address_1','billing_address_2',
               'billing_city','billing_postcode','billing_country','billing_state'];
    foreach ($remove as $key) unset($fields[$key]);
    // Relabel
    if (isset($fields['billing_first_name'])) $fields['billing_first_name']['label'] = 'الاسم الأول';
    if (isset($fields['billing_last_name']))  $fields['billing_last_name']['label']  = 'الاسم الأخير';
    if (isset($fields['billing_phone']))      $fields['billing_phone']['label']       = 'رقم الهاتف';
    if (isset($fields['billing_email']))      $fields['billing_email']['label']       = 'البريد الإلكتروني';
    return $fields;
}

add_filter('woocommerce_enable_order_notes_field', '__return_false');
add_filter('woocommerce_cart_needs_shipping', '__return_false');
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');
add_filter('woocommerce_checkout_fields', function($fields) {
    unset($fields['shipping']);
    unset($fields['order']['order_comments']);
    return $fields;
});

// ═══════════════════════════════════════════════
// 2. REGISTRATION → REDIRECT TO CHECKOUT
// ═══════════════════════════════════════════════
add_filter('woocommerce_registration_redirect', function($redirect) {
    // Add product to cart automatically before redirecting to checkout
    $product_id = 44;
    if (!WC()->cart->find_product_in_cart(WC()->cart->generate_cart_id($product_id))) {
        WC()->cart->add_to_cart($product_id);
    }
    return wc_get_checkout_url();
});

// Disable new account email (no password sent)
add_filter('woocommerce_email_enabled_customer_new_account', '__return_false');

// Also disable welcome email
add_filter('send_email_change_email', '__return_false');

// ═══════════════════════════════════════════════
// 3. PAYMENT GATEWAYS
// ═══════════════════════════════════════════════
add_filter('woocommerce_payment_gateways', function($gateways) {
    $gateways[] = 'SC_Instapay_Gateway';
    $gateways[] = 'SC_Vodafone_Gateway';
    return $gateways;
});

// Disable COD, BACS, Cheque by default
add_filter('woocommerce_available_payment_gateways', function($gateways) {
    unset($gateways['bacs']);
    unset($gateways['cheque']);
    unset($gateways['cod']);
    return $gateways;
});

// ── Instapay Gateway ──
class SC_Instapay_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id                 = 'sc_instapay';
        $this->method_title       = 'Instapay';
        $this->method_description = 'الدفع عبر Instapay';
        $this->has_fields         = true;
        $this->supports           = ['products'];
        $this->init_form_fields();
        $this->init_settings();
        $this->title       = $this->get_option('title', 'Instapay 📲');
        $this->description = $this->get_option('description', '');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }
    public function init_form_fields() {
        $this->form_fields = [
            'enabled'          => ['title' => 'تفعيل', 'type' => 'checkbox', 'default' => 'yes'],
            'title'            => ['title' => 'العنوان', 'type' => 'text', 'default' => 'Instapay 📲'],
            'instapay_number'  => ['title' => 'رقم / Username Instapay', 'type' => 'text', 'default' => ''],
            'whatsapp_number'  => ['title' => 'رقم واتساب لاستقبال الإيصالات', 'type' => 'text', 'default' => '201030435954'],
        ];
    }
    public function payment_fields() {
        $num = esc_html($this->get_option('instapay_number', ''));
        $wa  = esc_html($this->get_option('whatsapp_number', '201030435954'));
        echo '<div class="sc-pay-info">';
        if ($num) echo '<p>📲 رقم Instapay: <strong>' . $num . '</strong></p>';
        echo '<p>بعد إتمام التحويل، أرسل لقطة الشاشة على <a href="https://wa.me/' . $wa . '?text=تم+الدفع+عبر+Instapay" target="_blank" style="color:#FF9900;font-weight:700">واتساب</a> لتفعيل الكورس.</p>';
        echo '</div>';
    }
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $order->update_status('pending', 'في انتظار تأكيد الدفع عبر Instapay.');
        WC()->cart->empty_cart();
        return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
    }
}

// ── Vodafone Cash Gateway ──
class SC_Vodafone_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id                 = 'sc_vodafone';
        $this->method_title       = 'Vodafone Cash';
        $this->method_description = 'الدفع عبر Vodafone Cash';
        $this->has_fields         = true;
        $this->supports           = ['products'];
        $this->init_form_fields();
        $this->init_settings();
        $this->title       = $this->get_option('title', 'Vodafone Cash 📱');
        $this->description = $this->get_option('description', '');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }
    public function init_form_fields() {
        $this->form_fields = [
            'enabled'          => ['title' => 'تفعيل', 'type' => 'checkbox', 'default' => 'yes'],
            'title'            => ['title' => 'العنوان', 'type' => 'text', 'default' => 'Vodafone Cash 📱'],
            'vodafone_number'  => ['title' => 'رقم Vodafone Cash', 'type' => 'text', 'default' => ''],
            'whatsapp_number'  => ['title' => 'رقم واتساب لاستقبال الإيصالات', 'type' => 'text', 'default' => '201030435954'],
        ];
    }
    public function payment_fields() {
        $num = esc_html($this->get_option('vodafone_number', ''));
        $wa  = esc_html($this->get_option('whatsapp_number', '201030435954'));
        echo '<div class="sc-pay-info">';
        if ($num) echo '<p>📱 رقم Vodafone Cash: <strong>' . $num . '</strong></p>';
        echo '<p>بعد إتمام التحويل، أرسل لقطة الشاشة على <a href="https://wa.me/' . $wa . '?text=تم+الدفع+عبر+Vodafone+Cash" target="_blank" style="color:#FF9900;font-weight:700">واتساب</a> لتفعيل الكورس.</p>';
        echo '</div>';
    }
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $order->update_status('pending', 'في انتظار تأكيد الدفع عبر Vodafone Cash.');
        WC()->cart->empty_cart();
        return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
    }
}

// ═══════════════════════════════════════════════
// 4. ORDER CONFIRMATION PAGE — WhatsApp CTA
// ═══════════════════════════════════════════════
add_action('woocommerce_thankyou', function($order_id) {
    $order = wc_get_order($order_id);
    $wa = '201030435954';
    $method = $order->get_payment_method_title();
    $msg = urlencode('مرحبًا، أرسل إيصال الدفع عبر ' . $method . ' لطلب رقم #' . $order_id);
    echo '<div style="margin:30px 0;padding:24px;background:#1C2730;border:2px solid #FF9900;border-radius:14px;text-align:center;font-family:Cairo,sans-serif;direction:rtl;">';
    echo '<p style="font-size:18px;font-weight:700;color:#fff;margin-bottom:8px;">✅ تم تسجيل طلبك بنجاح!</p>';
    echo '<p style="color:#bbb;margin-bottom:20px;">الخطوة الأخيرة: أرسل إيصال الدفع على واتساب لتفعيل الكورس.</p>';
    echo '<a href="https://wa.me/' . $wa . '?text=' . $msg . '" target="_blank" style="display:inline-block;background:#25D366;color:#fff;font-family:Cairo,sans-serif;font-weight:700;font-size:16px;padding:14px 32px;border-radius:8px;text-decoration:none;">📱 أرسل الإيصال على واتساب</a>';
    echo '</div>';
}, 5);

// ═══════════════════════════════════════════════
// 5. BRAND CSS ON ALL WOOCOMMERCE PAGES
// ═══════════════════════════════════════════════
add_action('wp_head', 'sc_brand_css');
function sc_brand_css() {
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) return;
    ?>
<style id="sc-woo-styles">
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');
:root{--sc-orange:#FF9900;--sc-dark:#0F1111;--sc-dark2:#131921;--sc-dark3:#1C2730;--sc-border:rgba(255,153,0,.18);--sc-gray:#8B949E}
*{box-sizing:border-box}

/* ── Page background ── */
body.woocommerce,body.woocommerce-page,body.woocommerce-checkout,body.woocommerce-cart,body.woocommerce-account{
  background:var(--sc-dark)!important;color:#fff!important;font-family:'Cairo',sans-serif!important;direction:rtl!important
}

/* ── Hide theme header/footer on woo pages ── */
.site-header,header.site-header,#masthead,.ast-above-header-bar,.ast-mobile-header-wrap{display:none!important}
.entry-header,.page-header{display:none!important}
.site-content,#primary{padding-top:80px!important;max-width:900px;margin:0 auto!important}

/* ── Woo Wrapper ── */
.woocommerce-notices-wrapper,.woocommerce{width:100%}
.woocommerce h1,.woocommerce h2,.woocommerce h3,.woocommerce-page h1{color:#fff!important;font-family:'Cairo',sans-serif!important;font-weight:700}

/* ── CHECKOUT ── */
.woocommerce-checkout #payment,
.woocommerce form.checkout{color:#fff;font-family:'Cairo',sans-serif}

.woocommerce-billing-fields h3,
.woocommerce-shipping-fields h3,
#order_review_heading{color:var(--sc-orange)!important;font-size:16px!important;font-weight:700;margin-bottom:16px;border-bottom:1px solid var(--sc-border);padding-bottom:10px}

/* Form labels */
.woocommerce form .form-row label{color:#ccc!important;font-family:'Cairo',sans-serif!important;font-size:13px;margin-bottom:6px;display:block}
.woocommerce form .form-row label .required{color:var(--sc-orange)!important}

/* Form inputs */
.woocommerce form .form-row input.input-text,
.woocommerce form .form-row select,
.woocommerce form .form-row textarea{
  background:#1C2730!important;color:#fff!important;border:1px solid var(--sc-border)!important;
  border-radius:8px!important;padding:12px 14px!important;font-family:'Cairo',sans-serif!important;
  font-size:14px!important;width:100%!important;direction:rtl!important;transition:border-color .2s
}
.woocommerce form .form-row input.input-text:focus,
.woocommerce form .form-row select:focus{border-color:var(--sc-orange)!important;outline:none!important;box-shadow:0 0 0 3px rgba(255,153,0,.12)!important}

/* ── Payment section ── */
#payment{background:transparent!important;border-radius:12px!important}
#payment .payment_methods{list-style:none!important;padding:0!important;margin:0 0 16px!important}
#payment .payment_methods li{
  background:#1C2730!important;border:1px solid var(--sc-border)!important;
  border-radius:10px!important;margin-bottom:10px!important;padding:14px 16px!important;cursor:pointer;
  transition:border-color .2s!important
}
#payment .payment_methods li.wc_payment_method input[type="radio"]{accent-color:var(--sc-orange)}
#payment .payment_methods li label{color:#fff!important;font-family:'Cairo',sans-serif!important;font-size:15px!important;font-weight:600!important;cursor:pointer}
#payment .payment_methods li.woocommerce-PaymentMethod--active,
#payment .payment_methods li:has(input:checked){border-color:var(--sc-orange)!important;background:#232F3E!important}
.payment_box{background:transparent!important;color:#bbb!important;font-family:'Cairo',sans-serif!important;padding:10px 0!important}
.sc-pay-info{padding:8px 0;color:#bbb;line-height:1.7;font-size:14px}
.sc-pay-info strong{color:#fff}

/* ── Place order button ── */
#place_order,
.woocommerce #payment #place_order,
.woocommerce-cart .wc-proceed-to-checkout a.checkout-button{
  background:var(--sc-orange)!important;color:#0F1111!important;
  font-family:'Cairo',sans-serif!important;font-weight:700!important;font-size:16px!important;
  border:none!important;border-radius:8px!important;padding:16px 32px!important;
  cursor:pointer!important;width:100%!important;text-align:center!important;
  letter-spacing:.3px;transition:background .2s!important;margin-top:8px!important
}
#place_order:hover,.woocommerce-cart .wc-proceed-to-checkout a.checkout-button:hover{background:#cc7a00!important}

/* ── Order review table ── */
.woocommerce-checkout-review-order-table,
.shop_table{background:#1C2730!important;border-radius:10px!important;overflow:hidden!important;border:1px solid var(--sc-border)!important}
.woocommerce-checkout-review-order-table th,
.woocommerce-checkout-review-order-table td,
.shop_table th,.shop_table td{border-color:var(--sc-border)!important;color:#fff!important;font-family:'Cairo',sans-serif!important;padding:12px 16px!important}
.woocommerce-checkout-review-order-table tfoot .order-total .amount,
.shop_table tfoot .order-total .amount{color:var(--sc-orange)!important;font-weight:900!important;font-size:20px!important}

/* ── Breadcrumbs ── */
.woocommerce-breadcrumb{display:none!important}

/* ── Messages / notices ── */
.woocommerce-message,.woocommerce-error,.woocommerce-info{
  background:#1C2730!important;color:#fff!important;border-top-color:var(--sc-orange)!important;
  font-family:'Cairo',sans-serif!important;border-radius:8px!important
}
.woocommerce-message::before{color:var(--sc-orange)!important}

/* ── MY ACCOUNT page ── */
.woocommerce-MyAccount-navigation{background:#1C2730!important;border-radius:10px!important;padding:16px!important;border:1px solid var(--sc-border)!important}
.woocommerce-MyAccount-navigation ul{list-style:none!important;padding:0!important;margin:0!important}
.woocommerce-MyAccount-navigation ul li a{
  display:block;padding:10px 14px;color:#ccc!important;font-family:'Cairo',sans-serif!important;
  font-size:14px;text-decoration:none;border-radius:6px;transition:all .2s
}
.woocommerce-MyAccount-navigation ul li.is-active a,
.woocommerce-MyAccount-navigation ul li a:hover{background:rgba(255,153,0,.12)!important;color:var(--sc-orange)!important}
.woocommerce-MyAccount-content{color:#fff!important;font-family:'Cairo',sans-serif!important}

/* ── LOGIN/REGISTER FORM (My Account page) ── */
.woocommerce-account:not(.logged-in) .site-content{max-width:500px!important}
.woocommerce-account .woocommerce{background:transparent}

.woocommerce-form-login,
.woocommerce-form-register{
  background:#131921!important;border:1px solid var(--sc-border)!important;
  border-radius:14px!important;padding:32px!important;color:#fff!important
}
.woocommerce-form-login h2,
.woocommerce-form-register h2{color:#fff!important;font-family:'Cairo',sans-serif!important;font-size:20px!important;margin-bottom:20px!important;font-weight:700}

.woocommerce-form-login .form-row input,
.woocommerce-form-register .form-row input{
  background:#1C2730!important;color:#fff!important;border:1px solid var(--sc-border)!important;
  border-radius:8px!important;padding:12px 14px!important;font-family:'Cairo',sans-serif!important;font-size:14px!important
}
.woocommerce-form-login .form-row input:focus,
.woocommerce-form-register .form-row input:focus{border-color:var(--sc-orange)!important;outline:none!important}

.woocommerce-form-login label,
.woocommerce-form-register label{color:#ccc!important;font-family:'Cairo',sans-serif!important;font-size:13px}

/* Login/Register buttons */
.woocommerce-form-login__submit,
.woocommerce-form-register__submit,
button[name="login"],button[name="register"]{
  background:var(--sc-orange)!important;color:#0F1111!important;
  font-family:'Cairo',sans-serif!important;font-weight:700!important;font-size:15px!important;
  border:none!important;border-radius:8px!important;padding:13px 28px!important;
  cursor:pointer!important;width:100%!important;transition:background .2s!important
}
.woocommerce-form-login__submit:hover,
.woocommerce-form-register__submit:hover{background:#cc7a00!important}

/* Lost password link */
.woocommerce-LostPassword a,.lost_password a{color:var(--sc-orange)!important;font-family:'Cairo',sans-serif!important}

/* Remember me */
.woocommerce-form-login__rememberme span{color:#aaa!important;font-family:'Cairo',sans-serif!important;font-size:12px}

/* ── CART page ── */
.cart-empty{color:#fff!important;font-family:'Cairo',sans-serif!important}
.woocommerce table.cart td,.woocommerce table.cart th{color:#fff!important;font-family:'Cairo',sans-serif!important;background:#1C2730!important;border-color:var(--sc-border)!important}
.woocommerce table.cart .product-name a{color:#fff!important}
.cart_totals h2{color:var(--sc-orange)!important;font-family:'Cairo',sans-serif!important}
.cart_totals table td,.cart_totals table th{color:#fff!important;background:#1C2730!important;border-color:var(--sc-border)!important;font-family:'Cairo',sans-serif!important}

/* ── Order received (thank you) ── */
.woocommerce-thankyou-order-received,.woocommerce-order-received{color:#fff!important;font-family:'Cairo',sans-serif!important}
.woocommerce-order-overview{background:#1C2730!important;border:1px solid var(--sc-border)!important;border-radius:10px!important;list-style:none!important;padding:20px!important;display:flex;flex-wrap:wrap;gap:20px}
.woocommerce-order-overview li{color:#ccc!important;font-family:'Cairo',sans-serif!important;font-size:14px}
.woocommerce-order-overview li strong{color:#fff!important}

/* ── Checkout registration prompt ── */
.woocommerce-account-fields .create-account label,
#createaccount~label{color:#ccc!important;font-family:'Cairo',sans-serif!important}

/* ── Mobile ── */
@media(max-width:767px){
  .site-content,#primary{padding-top:70px!important}
  #payment .payment_methods li{padding:12px!important}
  .woocommerce-form-login,.woocommerce-form-register{padding:20px!important}
  .woocommerce-checkout-review-order-table th,.woocommerce-checkout-review-order-table td{padding:10px!important;font-size:13px!important}
}
</style>
    <?php
}

// ═══════════════════════════════════════════════
// 6. CUSTOM NAVBAR ON WOO PAGES (minimal header)
// ═══════════════════════════════════════════════
add_action('wp_body_open', 'sc_woo_nav');
function sc_woo_nav() {
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) return;
    ?>
<nav style="position:fixed;top:0;width:100%;z-index:1000;background:rgba(15,17,17,0.97);backdrop-filter:blur(14px);border-bottom:1px solid rgba(255,153,0,.18);padding:12px 24px;display:flex;align-items:center;justify-content:space-between;direction:rtl;font-family:Cairo,sans-serif;">
  <a href="/" style="font-size:20px;font-weight:900;color:#fff;text-decoration:none;letter-spacing:-.5px">Seller <span style="color:#FF9900">Circle</span></a>
  <div style="display:flex;gap:10px;align-items:center">
    <?php if (is_user_logged_in()): ?>
      <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>" style="color:rgba(255,255,255,.7);font-size:13px;text-decoration:none;padding:8px 12px;border:1px solid rgba(255,255,255,.2);border-radius:6px;">حسابي</a>
    <?php else: ?>
      <a href="<?php echo wc_get_page_permalink('myaccount'); ?>" style="color:rgba(255,255,255,.7);font-size:13px;text-decoration:none;padding:8px 12px;border:1px solid rgba(255,255,255,.2);border-radius:6px;">تسجيل الدخول</a>
    <?php endif; ?>
    <a href="/checkout/?add-to-cart=44" style="background:#FF9900;color:#0F1111;font-weight:700;font-size:14px;padding:10px 18px;border-radius:6px;text-decoration:none;white-space:nowrap">اشترك الآن — 999ج</a>
  </div>
</nav>
    <?php
}

// ═══════════════════════════════════════════════
// 7. MAKE PRODUCT 44 VIRTUAL (no shipping needed)
// ═══════════════════════════════════════════════
add_action('init', function() {
    $product = wc_get_product(44);
    if ($product && !$product->is_virtual()) {
        $product->set_virtual(true);
        $product->save();
    }
});

// ═══════════════════════════════════════════════
// 8. CUSTOM CHECKOUT TITLE
// ═══════════════════════════════════════════════
add_filter('the_title', function($title, $id = null) {
    if (is_checkout() && !is_order_received_page()) {
        if (function_exists('is_checkout') && $id === wc_get_page_id('checkout')) {
            return 'أكمل اشتراكك في Seller Circle';
        }
    }
    return $title;
}, 10, 2);

// ═══════════════════════════════════════════════
// 9. SHOW REGISTRATION FORM ON MY ACCOUNT (not just login)
// ═══════════════════════════════════════════════
add_filter('woocommerce_login_form_args', function($args) {
    return $args;
});
// Ensure registration is enabled
add_action('init', function() {
    if (!get_option('woocommerce_enable_myaccount_registration')) {
        update_option('woocommerce_enable_myaccount_registration', 'yes');
    }
});
