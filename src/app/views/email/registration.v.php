<h1><?=$this->z->core->t('Thank you for your registration') ?></h1>

<p>
<?=$this->z->core->t('Thank you for your registration on our website.') ?>.
</p>

<p>
<?=$this->z->core->t('Full name') ?>: <strong><?=$data['customer']->val('customer_name') ?></strong>
<br/>
<?=$this->z->core->t('E-mail') ?>: <strong><?=$data['customer']->val('customer_email') ?></strong>
</p>

<p>
<?=$this->z->core->t('To activate your account, click this <a href="%s">link</a>.', $data['activation_link']) ?>
</p>

<p>
<?=$this->z->core->t('If you forget your password, you can reset it <a href="%s">here</a>.', $this->z->core->url('reset-password')) ?>
</p>
