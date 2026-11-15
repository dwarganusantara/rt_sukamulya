<?php if ($this->session->flashdata('notice')): ?><div class="portal-success cdx-flash" role="status"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
<?php if ($this->session->flashdata('ops_error')): ?><div class="error cdx-flash" role="alert"><?= html_escape($this->session->flashdata('ops_error')) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="error cdx-flash" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
