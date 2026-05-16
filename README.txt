Manuelcode portfolio — PHP + Tailwind CDN

LOCAL (XAMPP subfolder):
  http://localhost/manuelcode/
  http://localhost/manuelcode/login

LIVE (domain root — no /manuelcode in URLs):
  https://manuelcode.info/
  https://manuelcode.info/login
  https://manuelcode.info/about

Old /manuelcode/... links redirect to the correct path automatically.

LIVE — ONLY HOME WORKS, OTHER PAGES 404 (/projects, /about, …):
  The app is probably in public_html/manuelcode/ but links point to /projects.
  Fix A (best): move ALL files into public_html/ (domain root), keep the project .htaccess.
  Fix B (subfolder): copy BOTH files into public_html/:
    deploy/public_html.htaccess  → public_html/.htaccess
    deploy/public_html.index.php → public_html/index.php
  Fix C (nginx): use deploy/nginx.conf.example in your host panel.
  Remove any rule that rewrites /manuelcode/ to /home3/.../public_html/ (broken paths).

LIVE TROUBLESHOOTING (wrong URL like /manuelcode/login.php):
  1. Use https://manuelcode.info/login
  2. Pull latest code; PHP redirects /manuelcode/* and *.php to clean URLs.
  3. See Fix B above if pages 404.

Pages:
  index.php      — Home
  projects.php   — Work
  services.php   — Services
  quotes.php     — Quotes
  designs.php    — Designs
  about.php      — About
  contact.php    — Contact
