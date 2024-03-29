<?php

require_once __DIR__ . '/../classes/menu.php';

/**
* Module that handles rendering of html menus.
*/
class menuModule extends zModule {

	public function renderMenuItem($item) {
		switch ($item->type) {
			case 'link':
				$this->renderMenuLink($item->href, $item->label);
			break;
			case 'submenu':
				$this->renderSubMenu($item);
			break;
		}
	}

	public function renderSubmenuItem($item) {
		switch ($item->type) {
			case 'link':
				$this->renderSubmenuLink($item->href, $item->label);
			break;
			case 'header':
				$this->renderHeader($item->label);
			break;
			case 'separator':
				$this->renderSeparator();
			break;
			case 'submenu':
				$this->renderSubmenu($item);
			break;
		}
	}

	public function renderMenuLink($href, $title) {
		if ($this->z->core->raw_path == $href) {
			$css = 'active';
		} else {
			$css = '';
		}
		echo sprintf('<li class="nav-item %s"><a class="nav-link %s" href="%s" >%s</a></li>', $css, $css, $this->z->core->url($href), $this->z->core->t($title));
	}

	public function renderSubmenuLink($href, $title) {
		if ($this->z->core->raw_path == $href) {
			$css = 'active';
		} else {
			$css = '';
		}
		echo sprintf('<li><a class="dropdown-item %s" href="%s" >%s</a></li>', $css, $this->z->core->url($href), $this->z->core->t($title));
	}

	public function renderSeparator() {
		?>
			<div class="dropdown-divider"></div>
		<?php
	}

	public function renderHeader($label) {
		?>
			<li class="dropdown-header"><?=$this->z->core->t($label) ?></li>
		<?php
	}

	public function renderSubmenu($submenu) {
		?>
			<li class="nav-item dropdown ">
				<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=$this->z->core->t($submenu->label) ?><span class="caret">&nbsp;</span></a>
				<ul class="dropdown-menu <?=$submenu->css ?>">
					<?php
						foreach ($submenu->items as $item) {
							$this->renderSubmenuItem($item);
						}
					?>
			  	</ul>
			</li>
		<?php
	}

	public function renderMenu($menu) {
		?>
			<nav class="navbar navbar-dark bg-dark navbar-expand-md sticky-top">
				<div class="container-fluid">

					 <?php
						if (isset($menu->href)) {
							?>
								<a class="navbar-brand" href="<?=$this->z->core->url($menu->href) ?>"><?=$this->z->core->t($menu->label) ?></a>
							<?php
						} else {
							?>
								<span class="navbar-brand"><?=$this->z->core->t($menu->label) ?></span>
							<?php
						}
					?>

					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbar">
						<ul class="navbar-nav me-auto">
							<?php
							foreach ($menu->items as $item) {
								$this->renderMenuItem($item);
							}
							?>
						</ul>
						<ul class="navbar-nav">
							<?php
							foreach ($menu->right_items as $item) {
								$this->renderMenuItem($item);
							}
							?>
						</ul>
					</div>
				</div>
			</nav>
		<?php
	}

}
