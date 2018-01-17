<?php

require_once __DIR__ . '/../classes/menu.php';

/**
* Module that handles rendering of html menus.
*/
class menuModule extends zModule {	
	
	public function renderMenuLink($href, $title) {
		if ($this->z->core->raw_path == $href) {
			$css = 'active';
		} else {
			$css = '';
		}
		echo sprintf('<li class="%s"><a href="%s" >%s</a></li>', $css, $this->z->core->url($href), $this->z->core->t($title));
	}
	
	public function renderItem($item) {
		switch ($item->type) {
			case 'item':
				$this->renderMenuLink($item->href, $item->label);
			break;
			case 'header':
				$this->renderHeader($item->label);
			break;
			case 'separator':
				$this->renderSeparator();
			break;
			case 'submenu':
				$this->renderSubMenu($item);
			break;
		}
	}
	
	public function renderSeparator() {
		?>
			<li role="separator" class="divider">&nbsp;</li>
		<?php
	}
	
	public function renderHeader($label) {
		?>
			<li class="dropdown-header"><?=$this->z->core->t($label) ?></li>
		<?php
	}
	
	public function renderSubMenu($submenu) {
		?>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=$this->z->core->t($submenu->label) ?><span class="caret">&nbsp;</span></a>
				<ul class="dropdown-menu">
					<?php
						foreach ($submenu->items as $item) {
							$this->renderItem($item);
						}
					?>
			  </ul>
			</li>
		<?php
	}
	
	public function renderMenu($menu) {
		?>
			<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
				<div class="not-a-container">
					<!-- grouped for better mobile display -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar">&nbsp;</span>
							<span class="icon-bar">&nbsp;</span>
							<span class="icon-bar">&nbsp;</span>
						 </button>

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
					</div>

					<div class="collapse navbar-collapse" id="navbar">		
						<ul class="nav navbar-nav navbar-left">
							<?php
								foreach ($menu->items as $item) {
									$this->renderItem($item);
								}
							?>
						</ul>
						<ul class="nav navbar-nav navbar-right">
							<?php
								foreach ($menu->right_items as $item) {
									$this->renderItem($item);
								}
							?>
						</ul>
					</div>
							
				</div>
			</nav>
		<?php
	}
	
}