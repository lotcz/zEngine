<channel>
    <title><?=$this->getFullPageTitle() ?></title>
    <link rel="self"><?=$this->url('') ?></link>
    <description><?=$this->getConfigValue('site_description') ?></description>

    <?php
        $this->renderPageView();
    ?>
</channel>
