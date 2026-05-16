<script>
(function () {
  var key = 'manuelcode-theme-mode';
  var theme = 'light';
  try {
    var mode = localStorage.getItem(key);
    if (mode === 'dark') {
      theme = 'dark';
    }
  } catch (e) {}
  document.documentElement.setAttribute('data-theme', theme);
})();
</script>
<link rel="stylesheet" href="<?= asset('assets/css/theme.css') ?>" />
