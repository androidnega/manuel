<?php
require_once __DIR__ . '/includes/data.php';

$pdo = cms_db();
$attachmentGroups = cms_attachment_groups($pdo);
$classGroups = cms_attachment_class_groups($pdo);
$registrationConfig = cms_attachment_registration_config($pdo);
$registrationOpen = cms_attachment_registration_is_open($pdo);
$sent = false;
$error = '';
$post = $_POST;
$lookupUrl = page_url('attachment-lookup.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$registrationOpen) {
    $error = $registrationConfig['closed_message'];
  } else {
    $existingId = (int) ($post['existing_id'] ?? 0);
    $classGroup = $post['class_group'] ?? '';
    $indexNumber = cms_form_upper($post['index_number'] ?? '');
    $companiesRaw = json_decode($post['companies_json'] ?? '[]', true);
    $companies = cms_attachment_normalize_companies_list(is_array($companiesRaw) ? $companiesRaw : []);

    if (!isset($classGroups[$classGroup]) || $indexNumber === '') {
      $error = 'Select your class group and enter your index number.';
    } elseif ($existingId > 0) {
      $existing = cms_attachment_get_by_id($pdo, $existingId);
      if (!$existing
        || strtoupper($existing['index_number']) !== strtoupper($indexNumber)
        || $existing['class_group'] !== $classGroup) {
        $error = 'We could not verify your registration. Check your class group and index number.';
      } else {
        $existingCompanies = cms_attachment_companies_from_row($existing);
        $existingCount = count($existingCompanies);
        if (count($companies) <= $existingCount) {
          $error = 'Add at least one new company before saving.';
        } elseif (count($companies) > cms_attachment_max_companies()) {
          $error = 'You can register up to ' . cms_attachment_max_companies() . ' companies only.';
        } else {
          $newCompanies = array_slice($companies, $existingCount);
          $companyError = cms_attachment_validate_companies($newCompanies, true);
          if ($companyError !== null) {
            $error = $companyError;
          } else {
            cms_update_industrial_attachment_companies($existingId, $companies);
            $sent = true;
            $post = [];
          }
        }
      }
    } else {
      $fullName = cms_form_upper($post['full_name'] ?? '');
      $contact = cms_form_upper($post['contact'] ?? '');

      if ($fullName === '' || $contact === '') {
        $error = 'Please complete your name and contact details.';
      } elseif (cms_attachment_index_exists($indexNumber, $classGroup)) {
        $groupInfo = $attachmentGroups[$classGroup] ?? null;
        $groupName = $groupInfo ? cms_attachment_group_display($groupInfo) : ($classGroups[$classGroup] ?? $classGroup);
        $error = 'This index number is already registered for ' . $groupName . '. Select your class, enter your index again, and add optional companies below.';
      } else {
        $companyError = cms_attachment_validate_companies($companies, true);
        if ($companyError !== null) {
          $error = $companyError;
        } else {
          cms_save_industrial_attachment([
            'full_name' => $fullName,
            'index_number' => $indexNumber,
            'contact' => $contact,
            'class_group' => $classGroup,
          ], $companies);
          $sent = true;
          $post = [];
        }
      }
    }
  }
}

$pageTitle = 'Industrial Attachment Registration | Manuelcode.info';
$heroLabel = 'Industrial Attachment';
$heroTitle = 'End of semester attachment register';
$heroDesc = 'Submit your company and placement details for second semester industrial attachment.';
$pageScripts = ['assets/js/attachment-form.js'];
include 'includes/header.php';
include 'includes/page-hero.php';

$inputClass = 'w-full rounded-xl border border-line bg-cloud px-3 py-3 text-sm uppercase outline-none focus:border-blue focus:ring-2 focus:ring-blue/10';
$labelClass = 'text-xs font-bold text-body';
?>
<main>
  <section class="py-10 sm:py-12 bg-cloud">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <?php if ($sent): ?>
        <p class="mb-6 rounded-2xl bg-mint/10 border border-mint/20 text-mint text-sm font-semibold px-5 py-4 reveal">
          Registration saved successfully. Your industrial attachment details have been recorded.
        </p>
      <?php elseif ($error): ?>
        <p class="mb-6 rounded-2xl bg-red-50 text-red-700 text-sm font-semibold px-5 py-4 reveal"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <div class="grid lg:grid-cols-3 gap-6 lg:gap-8 items-start">
        <aside class="reveal reveal-left lg:col-span-1 lg:sticky lg:top-28 lg:self-start rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm z-10">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Second semester</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body">
            Register up to three companies for industrial attachment. Your first company is required; two more are optional.
          </p>
          <ul class="mt-5 space-y-3 text-xs sm:text-sm text-body leading-relaxed">
            <?php foreach ($attachmentGroups as $group): ?>
              <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span><?= htmlspecialchars(cms_attachment_group_display($group)) ?></span></li>
            <?php endforeach; ?>
            <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span>Already registered? Enter class + index to add optional companies</span></li>
            <li class="flex gap-2"><span class="text-blue font-extrabold shrink-0">•</span><span>Type a company name and press comma to add it</span></li>
          </ul>
        </aside>

        <div class="reveal reveal-right reveal-delay-1 lg:col-span-2 min-w-0 rounded-2xl bg-white border border-line p-5 sm:p-6 lg:p-8 shadow-sleek-sm">
          <p class="text-xs font-extrabold text-blue uppercase tracking-[0.2em]">Registration form</p>
          <p class="mt-3 text-[0.9375rem] leading-relaxed text-body">Add one required company, or up to three in total. Returning students can add the optional slots they have left.</p>

          <?php if (!$registrationOpen): ?>
            <p class="mt-6 rounded-2xl bg-amber/10 border border-amber/20 text-amber text-sm font-semibold px-5 py-4">
              <?= htmlspecialchars($registrationConfig['closed_message']) ?>
            </p>
            <?php if (!empty($registrationConfig['closes_at'])): ?>
              <p class="mt-3 text-xs text-body">Registration closed on <?= htmlspecialchars(date('M j, Y g:i A', strtotime($registrationConfig['closes_at']))) ?>.</p>
            <?php endif; ?>
          <?php elseif (!$sent): ?>
          <form id="attachment-form" class="mt-6 space-y-4" method="post" action="<?= page_url('attachment.php') ?>" data-lookup-url="<?= htmlspecialchars($lookupUrl) ?>" data-no-uppercase="0">
            <input type="hidden" name="existing_id" id="existing_id" value="<?= htmlspecialchars($post['existing_id'] ?? '') ?>" />
            <input type="hidden" name="companies_json" id="companies_json" value="<?= htmlspecialchars($post['companies_json'] ?? '[]') ?>" />

            <div id="attachment-lookup-status" class="hidden" hidden></div>

            <div>
              <label class="<?= $labelClass ?>">Class group *</label>
              <select class="<?= $inputClass ?> mt-1 no-uppercase" name="class_group" required>
                <option value="">Select your class group</option>
                <?php foreach ($attachmentGroups as $val => $group): ?>
                  <option value="<?= htmlspecialchars($val) ?>" <?= ($post['class_group'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars(cms_attachment_group_display($group)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="<?= $labelClass ?>">Index number *</label>
              <input class="<?= $inputClass ?> mt-1" name="index_number" required placeholder="e.g. BC/ITS/24/O47" value="<?= htmlspecialchars($post['index_number'] ?? '') ?>" autocomplete="off" />
            </div>

            <div id="attachment-personal-fields" class="space-y-4">
              <div class="grid sm:grid-cols-2 gap-4">
                <div>
                  <label class="<?= $labelClass ?>">Full name *</label>
                  <input class="<?= $inputClass ?> mt-1" name="full_name" required placeholder="e.g. Emmanuel Kwofie" value="<?= htmlspecialchars($post['full_name'] ?? '') ?>" autocomplete="name" />
                </div>
                <div>
                  <label class="<?= $labelClass ?>">Contact *</label>
                  <input class="<?= $inputClass ?> mt-1" name="contact" type="tel" required placeholder="e.g. 0244123456" value="<?= htmlspecialchars($post['contact'] ?? '') ?>" autocomplete="tel" />
                </div>
              </div>
            </div>

            <div id="attachment-companies-section" class="rounded-2xl border border-line bg-cloud/40 p-4 sm:p-5 space-y-3">
              <div>
                <label class="<?= $labelClass ?>">Companies *</label>
                <p id="companies-hint" class="mt-1 text-xs leading-relaxed text-body">Add at least one company (max 3). Type a company name and press comma to add it.</p>
              </div>

              <div id="company-tags" class="flex flex-wrap gap-2 min-h-[2rem]"></div>

              <div id="company-tag-field">
                <input type="text" id="company-tag-input" class="<?= $inputClass ?> no-uppercase" autocomplete="off" placeholder="Company name — press comma to add" />
              </div>

              <div id="company-details-list" class="space-y-3"></div>
            </div>

            <button type="submit" id="attachment-submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-blue text-white px-8 py-3 text-sm font-extrabold hover:bg-blue/90 shadow-sleek-sm transition-all disabled:cursor-not-allowed disabled:opacity-50">
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
