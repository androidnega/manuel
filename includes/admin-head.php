<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>" />
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
        colors: {
          ink: '#101828',
          body: '#475467',
          line: '#EAECF0',
          cloud: '#F9FAFB',
          blue: '#FF7A00',
          deep: '#0B1E3A',
          mint: '#12B76A',
        },
      },
    },
  };
</script>
<style>
  .admin-nav-link { display: flex; align-items: center; gap: 0.625rem; }
  .admin-nav-link i { width: 1.125rem; text-align: center; opacity: 0.85; }
  .admin-nav-link.is-active { font-weight: 800; }
  .admin-nav-link.is-active i { opacity: 1; }
  .admin-nav-link.is-active .rounded-full { background-color: #ff7a00 !important; color: #fff !important; }
</style>
