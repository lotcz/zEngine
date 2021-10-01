<channel>
    <title><?=$this->getFullPageTitle() ?></title>
    <link><?=$this->url('') ?></link>
    <description><?=$this->getConfigValue('site_description') ?></description>

    <?php
        $this->renderPageView();
    ?>
</channel>