<?php
	if ($this->z->auth->isAuth()) {
		?>
			<p>
				Gratulujeme, nyní jste registrovaný uživatel!
			</p>
		<?php
			if ($this->z->admin->hasAnyRole()) {
				?>
					<p>
						Máte přístup do administrace.
					</p>
				<?php
			}
			if (strlen($this->z->auth->public_login_home) > 0) {
				?>
					<p>
						Pokračujte do <a href="<?=$this->url($this->z->auth->public_login_home)?>">zákaznické zóny</a>.
					</p>
				<?php
			}
	}
?>

<p>
	Pokračujte na <a href="<?=$this->url('')?>">úvodní stránku</a>.
</p>
