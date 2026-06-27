<?php
require_once __DIR__ . '/includes/data.php';

$defaults = cms_quotes_page_defaults();
$sent = false;
$error = '';

$projectTypes = [
  'website' => 'Website',
  'mobile' => 'Mobile app',
  'system' => 'Custom system / dashboard',
  'ui' => 'UI / UX design',
  'design' => 'Graphic design',
  'media' => 'Photo / video',
  'other' => 'Other',
];

$budgetRanges = [
  'under-5k' => 'Under GHS 5,000',
  '5k-15k' => 'GHS 5,000 – 15,000',
  '15k-50k' => 'GHS 15,000 – 50,000',
  '50k-plus' => 'GHS 50,000+',
  'unsure' => 'Not sure yet',
];

$timelines = [
  'asap' => 'ASAP',
  '1-2-weeks' => '1–2 weeks',
  '1-month' => 'About 1 month',
  '2-3-months' => '2–3 months',
  'flexible' => 'Flexible',
];

$post = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($post['name'] ?? '');
  $email = trim($post['email'] ?? '');
  $phone = trim($post['phone'] ?? '');
  $organization = trim($post['organization'] ?? '');
  $projectName = trim($post['project_name'] ?? '');
  $projectType = $post['project_type'] ?? '';
  $budgetRange = $post['budget_range'] ?? '';
  $timeline = $post['timeline'] ?? '';
  $description = trim($post['description'] ?? '');
  $referral = trim($post['referral'] ?? '');

  if ($name === '' || $email === '' || $projectName === '' || $description === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !isset($projectTypes[$projectType])
    || !isset($budgetRanges[$budgetRange])
    || !isset($timelines[$timeline])) {
    $error = 'Please complete all required fields with a valid email.';
  } else {
    cms_save_quote_request([
      'name' => $name,
      'email' => $email,
      'phone' => $phone,
      'organization' => $organization,
      'project_name' => $projectName,
      'project_type' => $projectTypes[$projectType],
      'budget_range' => $budgetRanges[$budgetRange],
      'timeline' => $timelines[$timeline],
      'description' => $description,
      'referral' => $referral,
    ]);
    $sent = true;
    $post = [];
  }
}

$cms = cms_page('quotes', $defaults);
$pageTitle = 'Request a quote | Manuelcode.info';
$heroLabel = $cms['label'];
$heroTitle = $cms['title'];
$heroDesc = $cms['desc'];
$pageBody = $cms['body'];
include 'includes/header.php';
include 'includes/page-hero.php';

$inputClass = 'w-full rounded-xl border border-line bg-cloud px-3 py-3 text-sm normal-case outline-none focus:border-blue focus:ring-2 focus:ring-blue/10';
$labelClass = 'text-xs font-bold text-body';
?>
<main>
  <section class="py-10 sm:py-12 bg-cloud">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php if ($sent): ?>
        <p class="mb-6 rounded-2xl bg-mint/10 border border-mint/20 text-mint text-sm font-semibold px-5 py-4 reveal">
          Thanks — your quote request was received. I’ll review the details and get back to you soon.
        </p>
      <?php elseif ($error): ?>
        <p class="mb-6 rounded-2xl bg-red-50 text-red-700 text-sm font-semibold px-5 py-4 reveal"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <div class="grid lg:grid-cols-3 gap-6 lg:gap-8 items-start">
        <aside class="reveal reveal-left lg:col-span-1 lg:sticky lg:top-28 lg:self-start rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm z-10">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Project quotes</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body"><?= htmlspecialchars($pageBody['intro_left'] ?? '') ?></p>
          <ul class="mt-5 space-y-3 text-xs sm:text-sm text-body leading-relaxed">
            <?php foreach (['note_1', 'note_2', 'note_3'] as $key): ?>
              <?php if (!empty($pageBody[$key])): ?>
                <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span><?= htmlspecialchars($pageBody[$key]) ?></span></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
          <div class="mt-6 pt-5 border-t border-line text-xs font-bold text-body space-y-2">
            <p class="inline-flex items-center gap-2"><?= icon('mail', 'w-4 h-4 text-blue shrink-0') ?> <a href="mailto:<?= htmlspecialchars($site['email']) ?>" class="hover:text-ink break-all"><?= htmlspecialchars($site['email']) ?></a></p>
            <p class="inline-flex items-center gap-2"><?= icon('message', 'w-4 h-4 text-mint shrink-0') ?> <a href="<?= htmlspecialchars($site['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-ink"><?= htmlspecialchars($site['phone']) ?></a></p>
          </div>
        </aside>

        <div class="reveal reveal-right reveal-delay-1 lg:col-span-2 min-w-0 rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Quote request form</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body"><?= htmlspecialchars($pageBody['intro_right'] ?? '') ?></p>

          <?php if (!$sent): ?>
          <form class="mt-6 space-y-4" method="post" action="<?= page_url('quotes.php') ?>">
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Full name *</label>
                <input class="<?= $inputClass ?> mt-1" name="name" required value="<?= htmlspecialchars($post['name'] ?? '') ?>" autocomplete="name" />
              </div>
              <div>
                <label class="<?= $labelClass ?>">Email *</label>
                <input class="<?= $inputClass ?> mt-1" name="email" type="email" required value="<?= htmlspecialchars($post['email'] ?? '') ?>" autocomplete="email" />
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Phone</label>
                <input class="<?= $inputClass ?> mt-1" name="phone" type="tel" value="<?= htmlspecialchars($post['phone'] ?? '') ?>" autocomplete="tel" />
              </div>
              <div>
                <label class="<?= $labelClass ?>">Organization</label>
                <input class="<?= $inputClass ?> mt-1" name="organization" value="<?= htmlspecialchars($post['organization'] ?? '') ?>" />
              </div>
            </div>
            <div>
              <label class="<?= $labelClass ?>">Project name *</label>
              <input class="<?= $inputClass ?> mt-1" name="project_name" required placeholder="e.g. School attendance portal" value="<?= htmlspecialchars($post['project_name'] ?? '') ?>" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Project type *</label>
                <select class="<?= $inputClass ?> mt-1" name="project_type" required>
                  <option value="">Select type</option>
                  <?php foreach ($projectTypes as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>" <?= ($post['project_type'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="<?= $labelClass ?>">Budget range *</label>
                <select class="<?= $inputClass ?> mt-1" name="budget_range" required>
                  <option value="">Select budget</option>
                  <?php foreach ($budgetRanges as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>" <?= ($post['budget_range'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Timeline *</label>
                <select class="<?= $inputClass ?> mt-1" name="timeline" required>
                  <option value="">Select timeline</option>
                  <?php foreach ($timelines as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>" <?= ($post['timeline'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="<?= $labelClass ?>">How did you hear about us?</label>
                <input class="<?= $inputClass ?> mt-1" name="referral" placeholder="Optional" value="<?= htmlspecialchars($post['referral'] ?? '') ?>" />
              </div>
            </div>
            <div>
              <label class="<?= $labelClass ?>">Project description *</label>
              <textarea class="<?= $inputClass ?> mt-1 min-h-[160px] resize-y" name="description" required placeholder="Goals, features, users, tech preferences, deadlines, links to references…"><?= htmlspecialchars($post['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-blue text-white px-8 py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all">
              Submit quote request <?= icon('arrow-right', 'w-4 h-4') ?>
            </button>
          </form>
          <?php else: ?>
            <p class="mt-6 text-sm text-body">Need to add more detail? <a href="<?= page_url('contact.php') ?>" class="font-bold text-blue hover:underline">Contact me</a> or submit another request later.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
