<h1><?=$this->z->core->t('Thank you for your registration') ?></h1>

<p>
<?=$this->z->core->t('Dear customer') ?>,
<br/>
<?=$this->z->core->t('Thank you for your registration') ?>.
</p>

<p>
<?=$this->z->core->t('E-mail') ?>: <strong><?=$data->val('customer_email') ?></strong>
</p>

<p>
<?=$this->z->core->t('If you forget your password, you can reset it <a href="%s">here</a>.', $this->z->core->url('reset-password')) ?>
</p>
