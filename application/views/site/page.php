<?php $title = $page['title'] . ' | ' . ($settings['site_name'] ?? 'Sukamulya'); $this->load->view('site/header', compact('title', 'settings', 'menu')); ?>
<main><section class="page-title"><div class="wrap"><a href="<?= site_url() ?>">Beranda</a> / <?= html_escape($page['title']) ?><h1><?= html_escape($page['title']) ?></h1></div></section>
<article class="wrap article"><?php if ($page['image']): ?><img class="article-image" src="<?= base_url(html_escape($page['image'])) ?>" alt="<?= html_escape($page['title']) ?>"><?php endif; ?><div><?php $this->load->helper('page_body'); ?><?= page_body_html($page['body']) ?></div></article></main>
<?php $this->load->view('site/footer', compact('settings')); ?>
