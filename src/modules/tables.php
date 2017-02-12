<?php

require_once $home_dir . 'classes/paging.php';

class Table {
	
	public $name;
	public $edit_link;	
	public $new_link;	
	public $css;
	public $id_field;
	
	public $show_search = false;
	
	public $bindings = null;
	public $types = null;
	public $where = null;
	public $orderby = null;
	public $fields = [];
	public $data = [];
	
	public $page_size = 20;
	public $max_pages_links = 20;
	
	function __construct($name = 'table or view', $id_field = '', $edit_link = '', $new_link = '', $css = '') {
		$this->name = $name;
		$this->id_field = $id_field;
		$this->edit_link = $edit_link;
		$this->new_link = $new_link;		
		$this->css = $css;			
	}
	
	public function addField($field) {
		$this->fields[$field['name']] = (object)$field;
	}
	
	public function add($fields) {
		if (is_array($fields)) {			
			foreach ($fields as $field) {
				$this->addField($field);
			}			
		} else {
			$this->addField($fields);
		}
	}
	
	public function prepare($db) {
		$this->paging = Paging::getFromUrl();
		$this->paging->limit = $this->page_size;
		Paging::$max_pages_links = $this->max_pages_links;
		$this->search = isset($_GET['s']) ? $_GET['s'] : '';
		
		// add filtering logic here
	
		$this->data = ModelBase::select(
			$db, 
			$this->name, 
			$this->where, 
			$this->bindings, 
			$this->types, 
			$this->paging, 
			$this->orderby
		);
	
	}
	
	public function render() {	
		global $raw_path;
		
		?>
			<form action="" method="GET" class="form form-inline">
				<?php
					if ($this->show_search) {
						?>
							<input type="text" name="s" value="<?=$this->search ?>" class="form-control" />
							<input type="submit" value="<?=t('Search') ?>" class="btn btn-primary form-control" />
						<?php
					}
				
					if (isset($this->new_link)) {
						if (isset($this->new_link_label)) {
							$label = $this->new_link_label;
						} else {
							$label = t('New');
						}
						?>
							<a class="btn btn-success form-control" href="<?=_url($this->new_link, $raw_path) ?>">+ <?=$label ?></a>		
						<?php
					}
					
					foreach ($this->links as $link) {						
						?>
							<a class="btn btn-success form-control" href="<?=_url($link['url'], $raw_path) ?>"><?=$link['title'] ?></a>		
						<?php
					}
					
				?>								
				<div class="text-right">
					<?=$this->paging->getInfo() ?>
				</div>
			</form>

			<?php
				$this->paging->renderLinks();
			?>

			<div class="table-responsive">
				<table class="table <?=$this->css ?>">
					<thead>
						<tr>
							<?php
								foreach ($this->fields as $field) {
									?>
										<th><?=t($field->label) ?></th>
									<?php
								}
							?>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php
						
						foreach ($this->data as $row) {
							$item_url = _url(sprintf($this->edit_link, $row->val($this->id_field)), $raw_path);
							?>
								<tr onclick="javascript:openDetail('<?=$item_url ?>');">
									<?php
										foreach ($this->fields as $field) {
											?>
												<td><?=$row->val($field->name) ?></td>
											<?php
										}
									?>
									<td><a href="<?=$item_url ?>"><?=t('Edit') ?></a></td>
								</tr>
							<?php
						}
					?>
					</tbody>
				</table>
			</div>
			
			<script>
				function openDetail(url) {
					document.location = url;
				}
			</script>
		<?php
	}	
}

class AdminTable extends Table {
	
	public $links = [];
	
	function __construct($view_name = 'table or view', $entity_name = 'entity') {
		parent::__construct(
			$view_name,
			$entity_name . '_id',
			sprintf('admin/%s/edit/', $entity_name) . '%d',
			sprintf('admin/%s', $entity_name),		
			'table-striped table-hover'
		);
	}
	
	public function addLink($url, $title) {
		$this->links[] = ['url'=>$url,'title'=>$title];
	}
}
