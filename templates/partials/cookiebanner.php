<div class="cookie-banner">
Diese Webseite verwendet Cookies <button class="btn btn-secondary" onclick="cookieBannerOkay()">Danke!</button><br>
<a href="<?= Template::getUrl("/?p=4") ?>">Zur Datenschutzerklärung</a>
</div>
<script>
$(document).ready(function() {
  var cookieBanner = getCookie("cookieBannerOkay");
  if (cookieBanner == 1)
  {
    cookieBannerOkay();
  }
});

function cookieBannerOkay()
{
  setCookie("cookieBannerOkay", "1", 3600*24*365);
  $('.cookie-banner').addClass("d-none");
}
</script>
