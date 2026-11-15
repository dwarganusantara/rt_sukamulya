<section class="bsx-card bsx-status-main">
 <header class="bsx-card-head">
  <h2><?= official_svg('check-circle') ?>Status Program</h2>
  <span class="bsx-status <?= ops_e($program['status']) ?>"><?= ops_e($program_labels[$program['status']]) ?></span>
 </header>
 <div class="bsx-status-main-body">
  <div class="bsx-status-box <?= $sm[2] ?>"><?= official_svg($sm[3]) ?><div><strong><?= ops_e($sm[0]) ?></strong><span><?= ops_e($sm[1]) ?></span></div></div>
  <?php if ($can('bansos.approve') && !$closed): ?><form method="post" class="bsx-status-act"><?= ops_csrf() ?><input type="hidden" name="act" value="program_status"><?php if ($program['status'] === 'draft'): ?><button class="bsx-btn green wide" name="to" value="aktif"><?= official_svg('check') ?>Aktifkan Program</button><?php else: ?><button class="bsx-btn ghost wide" name="to" value="selesai" onclick="return confirm('Tutup program? Data tidak dapat diubah lagi.')"><?= official_svg('check-circle') ?>Tandai Selesai</button><?php endif; ?></form><?php endif; ?>
 </div>
</section>
