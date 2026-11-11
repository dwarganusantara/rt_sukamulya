<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div style="border:1px solid #d6453d;padding:12px 16px;margin:10px;border-radius:10px;background:#fff4f2;color:#082654;font:13px/1.5 system-ui,sans-serif">
<h4 style="margin:0 0 6px;color:#b4332b">An uncaught Exception was encountered</h4>
<p style="margin:0">Type: <?php echo get_class($exception); ?></p>
<p style="margin:0">Message: <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<p style="margin:0">Filename: <?php echo $exception->getFile(); ?></p>
<p style="margin:0">Line Number: <?php echo $exception->getLine(); ?></p>
<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>
<p style="margin:6px 0 0">Backtrace:</p>
<?php foreach ($exception->getTrace() as $error): ?><?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>
<p style="margin:0 0 0 10px">File: <?php echo $error['file']; ?> · Line: <?php echo $error['line']; ?> · Function: <?php echo $error['function']; ?></p>
<?php endif; endforeach; endif; ?>
</div>
