<?php
/*
=================================================
  PENDING ADDITIONS — تُضاف لـ functions.php
  أضفها في نهاية الملف عبر Theme Editor
  seller-circle.com/wp-admin/theme-editor.php
=================================================
*/

/* =============================================
   LOGIN PAGE (My Account, ID=18) — DARK PREMIUM
   ============================================= */
function sc_login_page_fixes(){
  if(!is_page(18)) return;
  ?>
  <style>
  body.page-id-18{background:#0d1117!important;min-height:100vh}
  body.page-id-18 main.site-main{padding-top:0!important}
  body.page-id-18 .woocommerce{max-width:480px;margin:60px auto}
  body.page-id-18 .woocommerce-form-login,.woocommerce-form-register{
    background:#161b22;border:1px solid rgba(255,107,0,.2);
    border-radius:16px;padding:40px;direction:rtl}
  body.page-id-18 h2{color:#ff6b00!important;font-family:Cairo,sans-serif!important;
    font-size:22px!important;font-weight:700!important;text-align:center}
  body.page-id-18 .woocommerce-form-login input[type="text"],
  body.page-id-18 .woocommerce-form-login input[type="password"],
  body.page-id-18 .woocommerce-form-register input[type="text"],
  body.page-id-18 .woocommerce-form-register input[type="email"],
  body.page-id-18 .woocommerce-form-register input[type="password"]{
    background:#1c2333!important;border:1px solid rgba(255,107,0,.3)!important;
    color:#fff!important;border-radius:8px!important;padding:12px 16px!important;
    font-family:Cairo,sans-serif!important;width:100%!important;box-sizing:border-box!important}
  body.page-id-18 .woocommerce-form-login input::placeholder,
  body.page-id-18 .woocommerce-form-register input::placeholder{color:rgba(255,255,255,.35)!important}
  body.page-id-18 .woocommerce-form-login label,
  body.page-id-18 .woocommerce-form-register label{
    color:#e6edf3!important;font-family:Cairo,sans-serif!important;font-size:14px!important}
  body.page-id-18 .woocommerce-form-login__submit,
  body.page-id-18 .woocommerce-Button,
  body.page-id-18 button[name="login"],
  body.page-id-18 button[name="register"]{
    background:linear-gradient(135deg,#ff6b00,#ff8c00)!important;
    color:#fff!important;border:none!important;border-radius:10px!important;
    padding:14px 24px!important;font-family:Cairo,sans-serif!important;
    font-size:16px!important;font-weight:700!important;
    cursor:pointer!important;width:100%!important}
  body.page-id-18 .lost_password a{color:#ff6b00!important;font-family:Cairo,sans-serif!important}
  .sc-login-logo{text-align:center;margin-bottom:28px}
  .sc-login-logo span{font-family:Cairo,sans-serif;font-size:30px;font-weight:900;
    background:linear-gradient(135deg,#ff6b00,#ff8c00);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent}
  .sc-login-sub{text-align:center;color:#8b949e;font-family:Cairo,sans-serif;
    font-size:14px;margin-bottom:24px}
  .sc-login-reg-link{text-align:center;margin-top:20px;color:#8b949e;
    font-family:Cairo,sans-serif;font-size:13px}
  .sc-login-reg-link a{color:#ff6b00;text-decoration:none;font-weight:600}
  body.page-id-18 .u-column1,.u-column2{float:none!important;width:100%!important}
  </style>
  <script>
  (function(){
    var loginForm = document.querySelector('.woocommerce-form-login');
    if(loginForm && !document.getElementById('sc-login-logo')){
      var logo = document.createElement('div');
      logo.id='sc-login-logo'; logo.className='sc-login-logo';
      logo.innerHTML='<span>Seller Circle</span>';
      loginForm.insertBefore(logo, loginForm.firstChild);
      var sub = document.createElement('p');
      sub.className='sc-login-sub';
      sub.textContent='سجل دخولك للوصول لكورسك';
      logo.after(sub);
      var regLink = document.createElement('div');
      regLink.className='sc-login-reg-link';
      regLink.innerHTML='مش عندك حساب؟ <a href="/تسجيل-الطالب/">سجل الآن</a>';
      loginForm.appendChild(regLink);
    }
  })();
  </script>
  <?php
}
add_action('wp_footer','sc_login_page_fixes');
