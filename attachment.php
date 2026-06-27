<?php
require_once __DIR__ . '/includes/data.php';

$classGroups = cms_attachment_class_groups();
$sent = false;
$error = '';
$post = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = trim($post['full_name'] ?? '');
  $indexNumber = strtoupper(trim($post['index_number'] ?? ''));
  $contact = trim($post['contact'] ?? '');
  $companyName = trim($post['company_name'] ?? '');
  $location = trim($post['location'] ?? '');
  $officialPosition = trim($post['official_position'] ?? '');
  $classGroup = $post['class_group'] ?? '';

  if ($fullName === '' || $indexNumber === '' || $contact === '' || $companyName === ''
    || $location === '' || $officialPosition === ''
    || !isset($classGroups[$classGroup])) {
    $error = 'Please complete all required fields and select your class group.';
  } elseif (cms_attachment_index_exists($indexNumber, $classGroup)) {
    $error = 'This index number is already registered for ' . $classGroups[$classGroup] . '. Contact your class rep if you need to update your details.';
  } else {
    cms_save_industrial_attachment([
      'full_name' => $fullName,
      'index_number' => $indexNumber,
      'contact' => $contact,
      'company_name' => $companyName,
      'location' => $location,
      'official_position' => $officialPosition,
      'class_group' => $classGroup,
    ]);
    $sent = true;
    $post = [];
  }
}

$pageTitle = 'Industrial Attachment Registration | Manuelcode.info';
$heroLabel = 'Industrial Attachment';
$heroTitle = 'End of semester attachment register';
$heroDesc = 'Submit your company and placement details for second semester industrial attachment.';
include 'includes/header.php';
include 'includes/page-hero.php';

$inputClass = 'w-full rounded-xl border border-line bg-cloud px-3 py-3 text-sm outline-none focus:border-blue focus:ring-2 focus:ring-blue/10';
$labelClass = 'text-xs font-bold text-body';
?>
<main>
  <section class="py-10 sm:py-12 bg-cloud">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php if ($sent): ?>
        <p class="mb-6 rounded-2xl bg-mint/10 border border-mint/20 text-mint text-sm font-semibold px-5 py-4 reveal">
          Registration submitted successfully. Your industrial attachment details have been recorded for your class group.
        </p>
      <?php elseif ($error): ?>
        <p class="mb-6 rounded-2xl bg-red-50 text-red-700 text-sm font-semibold px-5 py-4 reveal"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <div class="grid lg:grid-cols-3 gap-6 lg:gap-8 items-start">
        <aside class="reveal reveal-left lg:col-span-1 lg:sticky lg:top-28 lg:self-start rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm z-10">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Second semester</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body">
            Use this form to register your industrial attachment placement details. Select the correct class group so records stay separated.
          </p>
          <ul class="mt-5 space-y-3 text-xs sm:text-sm text-body leading-relaxed">
            <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span>BTECH IT GROUP A — morning class students</span></li>
            <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span>BTECH I.T GROUP E — evening class students</span></li>
            <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span>Use your official index number exactly as issued</span></li>
          </ul>
        </aside>

        <div class="reveal reveal-right reveal-delay-1 lg:col-span-2 min-w-0 rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Registration form</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body">Fill in your personal and company details below. All fields are required.</p>

          <?php if (!$sent): ?>
          <form class="mt-6 space-y-4" method="post" action="<?= page_url('attachment.php') ?>">
            <div>
              <label class="<?= $labelClass ?>">Class group *</label>
              <select class="<?= $inputClass ?> mt-1" name="class_group" required>
                <option value="">Select your class group</option>
                <?php foreach ($classGroups as $val => $label): ?>
                  <option value="<?= htmlspecialchars($val) ?>" <?= ($post['class_group'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Full name *</label>
                <input class="<?= $inputClass ?> mt-1" name="full_name" required placeholder="e.g. Emmanuel Kwofie" value="<?= htmlspecialchars($post['full_name'] ?? '') ?>" autocomplete="name" />
              </div>
              <div>
                <label class="<?= $labelClass ?>">Index number *</label>
                <input class="<?= $inputClass ?> mt-1" name="index_number" required placeholder="e.g. BC/ITS/24/O47" value="<?= htmlspecialchars($post['index_number'] ?? '') ?>" />
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Contact *</label>
                <input class="<?= $inputClass ?> mt-1" name="contact" type="tel" required placeholder="e.g. 0244123456" value="<?= htmlspecialchars($post['contact'] ?? '') ?>" autocomplete="tel" />
              </div>
              <div>
                <label class="<?= $labelClass ?>">Company name *</label>
                <input class="<?= $inputClass ?> mt-1" name="company_name" required placeholder="e.g. Tech Solutions Ltd" value="<?= htmlspecialchars($post['company_name'] ?? '') ?>" />
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="<?= $labelClass ?>">Location *</label>
                <input class="<?= $inputClass ?> mt-1" name="location" required placeholder="e.g. Accra, Greater Accra" value="<?= htmlspecialchars($post['location'] ?? '') ?>" />
              </div>
              <div>
                <label class="<?= $labelClass ?>">Official's position *</label>
                <input class="<?= $inputClass ?> mt-1" name="official_position" required placeholder="e.g. IT Manager" value="<?= htmlspecialchars($post['official_position'] ?? '') ?>" />
              </div>
            </div>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-blue text-white px-8 py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all">
              Submit registration <?= icon('arrow-right', 'w-4 h-4') ?>
            </button>
          </form>
          <?php else: ?>
            <p class="mt-6 text-sm text-body">Need to correct your details? Contact your class representative before submitting again.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
