<?php
$page = 'contact';
require __DIR__ . '/includes/config.php';
$page_title = seo_title('contact', $t['contact_title']);
$page_meta  = seo_desc('contact', $t['contact_meta']);
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_contact']) ?></span>
    </div>
    <h1><?= e($t['contact_h']) ?></h1>
    <p class="lead"><?= e($t['contact_d']) ?></p>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <div class="contact-grid">
      <a class="contact-card wa reveal" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <span class="ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg></span>
        <span>
          <h3><?= e($t['contact_wa']) ?></h3>
          <span class="val"><?= PHONE_DISPLAY ?></span>
          <small><?= e($t['contact_wa_d']) ?></small>
        </span>
      </a>
      <a class="contact-card reveal" href="tel:<?= PHONE_TEL ?>">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9Z"/></svg></span>
        <span>
          <h3><?= e($t['contact_phone']) ?></h3>
          <span class="val"><?= PHONE_DISPLAY ?></span>
        </span>
      </a>
      <a class="contact-card ig reveal" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg></span>
        <span>
          <h3><?= e($t['contact_ig']) ?></h3>
          <span class="val"><?= IG_HANDLE ?></span>
          <small><?= e($t['contact_ig_d']) ?></small>
        </span>
      </a>
      <a class="contact-card reveal" href="mailto:<?= EMAIL ?>">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 6 9 7 9-7"/></svg></span>
        <span>
          <h3><?= e($t['contact_email']) ?></h3>
          <span class="val"><?= EMAIL ?></span>
        </span>
      </a>
      <div class="contact-card reveal">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></span>
        <span>
          <h3><?= e($t['contact_hours']) ?></h3>
          <span class="val" style="font-size:14.5px"><?= e($t['hours_full']) ?></span>
        </span>
      </div>
      <div class="contact-card reveal">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3V6a1 1 0 0 1 1-1h9v12"/><path d="M13 8h4l3 4v5h-2"/><circle cx="7.5" cy="17.5" r="2"/><circle cx="16.5" cy="17.5" r="2"/></svg></span>
        <span>
          <h3><?= e($t['delivery_t']) ?></h3>
          <span class="val" style="font-size:14.5px"><?= e($t['delivery_d']) ?></span>
        </span>
      </div>
    </div>

    <div class="contact-note reveal">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
      <div>
        <b><?= e($t['contact_note_t']) ?></b>
        <p><?= e($t['contact_note_d']) ?></p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
