<?php
require_once __DIR__ . '/includes/data.php';

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');
  if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please fill in your name, a valid email, and a message.';
  } else {
    cms_save_contact_message($name, $email, $subject, $message);
    $sent = true;
  }
}

$cms = cms_page('contact', [
  'label' => 'Contact',
  'title' => 'Have a project in mind?',
  'desc' => 'Let’s build something clean and useful.',
  'body' => [
    'intro_left' => 'Reach out for websites, systems, apps, posters, design, photo or video.',
    'intro_right' => 'Share a few details and I’ll get back to you.',
  ],
]);
$pageTitle = 'Contact | Manuelcode.info';
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
$pageBody = $cms['body'];
include 'includes/header.php';
include 'includes/page-hero.php';
?>
<main>
  <section class="py-10 sm:py-12 bg-cloud">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php if ($sent): ?>
        <p class="mb-6 rounded-2xl bg-mint/10 border border-mint/20 text-mint text-sm font-semibold px-5 py-4 reveal">Thanks — your message was sent. I’ll get back to you soon.</p>
      <?php elseif ($error): ?>
        <p class="mb-6 rounded-2xl bg-red-50 text-red-700 text-sm font-semibold px-5 py-4 reveal"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <div class="grid lg:grid-cols-2 gap-4 lg:gap-6 items-stretch">
        <div class="reveal reveal-left flex flex-col rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Get in touch</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body"><?= htmlspecialchars($pageBody['intro_left'] ?? '') ?></p>
          <div class="mt-5 grid gap-3 text-xs sm:text-sm font-bold text-body">
            <p class="inline-flex items-center gap-2"><?= icon('mail', 'w-5 h-5 text-blue shrink-0') ?> <a href="mailto:<?= htmlspecialchars($site['email']) ?>" class="hover:text-ink break-all"><?= htmlspecialchars($site['email']) ?></a></p>
            <p class="inline-flex items-center gap-2"><?= icon('message', 'w-5 h-5 text-mint shrink-0') ?> <a href="<?= htmlspecialchars($site['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-ink"><?= htmlspecialchars($site['phone']) ?></a></p>
            <p class="inline-flex items-center gap-2"><?= icon('globe', 'w-5 h-5 text-deep shrink-0') ?> <?= htmlspecialchars($site['website']) ?></p>
          </div>
          <a href="<?= htmlspecialchars($site['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="mt-auto pt-6 inline-flex w-fit items-center gap-2 rounded-full bg-mint/10 text-mint border border-mint/20 px-5 py-2.5 text-xs font-extrabold hover:bg-mint/20 transition-colors">
            Chat on WhatsApp <?= icon('message', 'w-4 h-4') ?>
          </a>
        </div>

        <div class="reveal reveal-right reveal-delay-1 flex flex-col rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Send a message</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body"><?= htmlspecialchars($pageBody['intro_right'] ?? '') ?></p>
          <form class="mt-5 flex flex-col flex-grow w-full gap-3" method="post" action="<?= page_url('contact.php') ?>">
            <div class="grid sm:grid-cols-2 gap-3 w-full">
              <input class="w-full rounded-xl border border-line bg-cloud px-3 py-3 text-sm normal-case outline-none focus:border-blue focus:ring-2 focus:ring-blue/10" name="name" placeholder="Your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
              <input class="w-full rounded-xl border border-line bg-cloud px-3 py-3 text-sm normal-case outline-none focus:border-blue focus:ring-2 focus:ring-blue/10" name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
            </div>
            <input class="w-full rounded-xl border border-line bg-cloud px-3 py-3 text-sm normal-case outline-none focus:border-blue focus:ring-2 focus:ring-blue/10" name="subject" placeholder="Subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" />
            <textarea class="w-full flex-grow min-h-[140px] rounded-xl border border-line bg-cloud px-3 py-3 text-sm normal-case outline-none focus:border-blue focus:ring-2 focus:ring-blue/10 resize-y" name="message" placeholder="Tell me about the project" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            <button type="submit" class="w-full rounded-xl bg-blue text-white py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
