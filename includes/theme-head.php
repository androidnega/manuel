<script>
(function () {
  var key = 'manuelcode-theme-mode';
  var theme = 'light';
  try {
    var mode = localStorage.getItem(key);
    if (mode === 'light' || mode === 'dark') {
      theme = mode;
    } else {
      var h = new Date().getHours();
      theme = h >= 18 || h < 6 ? 'dark' : 'light';
    }
  } catch (e) {
    var h = new Date().getHours();
    theme = h >= 18 || h < 6 ? 'dark' : 'light';
  }
  document.documentElement.setAttribute('data-theme', theme);
})();
</script>
<link rel="stylesheet" href="<?= asset('assets/css/theme.css') ?>" />
