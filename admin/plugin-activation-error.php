<style type="text/css">
body {
  padding: 0;
  margin: 0;
  color: #444;
  font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
  font-size: 13px;
  line-height: 1.4em;
}
a {
  color: #0073aa;
  text-decoration: none;
}
a:active, a:hover {
  color: #00a0d2;
}
</style>
<div style="padding-top: 4px;"></div>
<?php _e("You must Deactivate the <strong>WP Paint Pro</strong> plugin before activating <strong>WP Paint</strong>."); ?>
<div style="padding-top: 14px;"></div>
<a href="javascript:void(0);" id="wpp-deactivate-pro">Deactivate WP Paint Pro Plugin</a>
<script>
<!--
(function () {
  document.getElementById('wpp-deactivate-pro').onclick = function () {
    var deactivation_url = window.parent.jQuery('#the-list > tr[data-plugin="wp-paint-pro/wp-paint-pro.php"] span.deactivate > a', window.parent.document).attr('href');
    window.parent.location.href = deactivation_url;
  };
})();
//-->
</script>
