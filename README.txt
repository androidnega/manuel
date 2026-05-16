Manuelcode portfolio — PHP + Tailwind CDN

LOCAL (XAMPP subfolder):
  http://localhost/manuelcode/
  http://localhost/manuelcode/login

LIVE (domain root — no /manuelcode in URLs):
  https://manuelcode.info/
  https://manuelcode.info/login
  https://manuelcode.info/about

Old /manuelcode/... links redirect to the correct path automatically.

LIVE TROUBLESHOOTING (wrong URL like /manuelcode/login.php):
  1. Use https://manuelcode.info/login — this already works on the server.
  2. Pull the latest code so PHP sends /manuelcode/* → /login (301).
  3. In cPanel public_html/.htaccess, remove rules that rewrite /manuelcode/
     to a folder path such as /home3/.../public_html/ (that exposes internal paths).
     See deploy/public_html-root.htaccess if the app must stay in a subfolder.
  4. Ideal: put all site files in public_html root (not in a manuelcode/ folder).

Pages:
  index.php      — Home
  projects.php   — Work
  services.php   — Services
  quotes.php     — Quotes
  designs.php    — Designs
  about.php      — About
  contact.php    — Contact
