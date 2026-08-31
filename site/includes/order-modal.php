<?php
// Попап оформления заказа. Ожидает: $modalName (строка), $modalImg (путь или '').
?>
<div class="modal-ov" id="order-modal" hidden>
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <button type="button" class="modal-x" id="modal-close" aria-label="Close">×</button>
    <h3 class="modal-t" id="modal-title"><?= e($t['ord_t']) ?></h3>
    <div class="modal-sum">
      <?php if (!empty($modalImg)): ?>
      <img src="<?= e($modalImg) ?>" alt="" width="56" height="56">
      <?php endif; ?>
      <div><b><?= e($modalName) ?></b><span id="modal-sum-line"></span></div>
    </div>
    <div class="modal-body">
      <div class="opt-row">
        <label for="opt-dl"><?= e($t['opt_dl']) ?></label>
        <select id="opt-dl">
          <option value="courier"><?= e($t['dl_courier']) ?></option>
          <option value="bolt"><?= e($t['dl_bolt']) ?></option>
          <option value="pickup"><?= e($t['dl_pickup']) ?></option>
        </select>
      </div>
      <div class="bolt-note" id="bolt-note" hidden>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
        <span><?= e($t['dl_bolt_note']) ?> <a href="<?= MAP_URL ?>" target="_blank" rel="noopener"><?= e($t['dl_bolt_addr']) ?></a></span>
      </div>
      <div class="opt-row">
        <label><?= e($t['opt_date']) ?> · <?= e($t['opt_time']) ?></label>
        <div class="duo">
          <div class="date-wrap">
            <button type="button" class="date-btn" id="opt-date">
              <span id="date-val"><?= e($t['date_ph']) ?></span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="17" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
            </button>
            <div class="cal" id="cal" hidden>
              <div class="cal-head">
                <button type="button" id="cal-prev" aria-label="Prev">‹</button>
                <b id="cal-title"></b>
                <button type="button" id="cal-next" aria-label="Next">›</button>
              </div>
              <div class="cal-grid" id="cal-grid"></div>
              <div class="cal-note"><?= e($t['date_note']) ?></div>
            </div>
          </div>
          <select id="opt-time">
            <option value=""><?= e($t['time_ph']) ?></option>
          </select>
        </div>
      </div>
      <div class="opt-row" id="row-address">
        <label for="f-address"><?= e($t['f_address']) ?> <i class="req" title="<?= e($t['req_mark']) ?>">*</i></label>
        <input class="txt" type="text" id="f-address" placeholder="<?= e($t['f_address_ph']) ?>" maxlength="120" required>
      </div>
      <div class="opt-row">
        <label for="f-name"><?= e($t['f_name']) ?> <i class="req" title="<?= e($t['req_mark']) ?>">*</i></label>
        <input class="txt" type="text" id="f-name" placeholder="<?= e($t['f_name_ph']) ?>" maxlength="60" required>
      </div>
      <div class="opt-row">
        <label for="f-phone"><?= e($t['f_phone']) ?> <i class="req" title="<?= e($t['req_mark']) ?>">*</i></label>
        <input class="txt" type="tel" id="f-phone" placeholder="<?= e($t['f_phone_ph']) ?>" maxlength="30" required>
      </div>
      <label class="chk">
        <input type="checkbox" id="f-other">
        <span><?= e($t['f_other']) ?></span>
      </label>
      <div class="opt-row" id="row-rname" hidden>
        <label for="f-rname"><?= e($t['f_recipient']) ?></label>
        <input class="txt" type="text" id="f-rname" placeholder="<?= e($t['f_rname_ph']) ?>" maxlength="60">
      </div>
      <div class="opt-row" id="row-rphone" hidden>
        <label for="f-rphone"><?= e($t['f_phone']) ?></label>
        <input class="txt" type="tel" id="f-rphone" placeholder="<?= e($t['f_rphone_ph']) ?>" maxlength="30">
      </div>
    </div>
    <div class="modal-err" id="modal-err" hidden></div>
    <a class="btn btn-wa modal-send" id="modal-send" href="#" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
      <?= e($t['ord_send']) ?>
    </a>
    <p class="modal-note"><b><?= e($t['ord_confirm']) ?></b><?= e($t['pd_note']) ?></p>
  </div>
</div>
