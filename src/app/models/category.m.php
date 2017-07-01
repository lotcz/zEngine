<?php

class CategoryModel extends zModel {
	
	public $table_name = 'categories';
	public $id_name = 'category_id';
	
	static $cache_tree = null;
	
	public $is_selected = false;
	public $is_on_selected_path = false;
	public $level = 0;
	public $total_products = 0;
	
	public function loadByExtId($id) {
		$filter = 'category_ext_id = ?';
		$this->loadSingleFiltered($filter, [$id]);
	}
	
	public function findInChildren($id, $column_name = 'category_id') {
		if (isset($this->children)) {			
			foreach ($this->children as $c) {
				if ($c->ival($column_name) == $id) {
					return $c;
				} else {
					$sc = $c->findInChildren($id, $column_name);
					if (isset($sc)) {
						return $sc;
					}
				}
			}
		} else {
			return null;
		}
	}
	
	public function addChild($c) {
		if (!isset($this->children)) {
			$this->children = [];
		}
		
		// add algoritm to keep alphabetical ordering
		// ...
		
		$this->children[] = $c;
		$c->treeParent = $this;
		$c->level = $this->level + 1;
		
		// update product total
		if ($c->total_products == 0) {
			$c->total_products = $c->ival('category_total_products');
		}
		$this->updateTotalProducts($c->total_products);
	}
	
	public function updateTotalProducts($add) {
		if ($this->total_products == 0) {
			$this->total_products = $this->ival('category_total_products');
		}
		$this->total_products += $add;
		if (isset($this->treeParent)) {			
			$this->treeParent->updateTotalProducts($add);
		}
	}

	private function setSelectedPath() {
		$this->is_on_selected_path = true;
		if (isset($this->treeParent)) {			
			$this->treeParent->setSelectedPath();
		}
	}
	
	public function setSelectedCategory($selected_id) {
		$sc = $this->findInChildren($selected_id);
		$sc->is_selected = true;
		$sc->setSelectedPath();
		return $sc;
	}
	
	public function getSubTreeIDs() {
		$ids = [ $this->ival('category_id') ];		
		if (isset($this->children)) {
			
			for ($i = 0; $i < count($this->children); $i++) {
				$ids = array_merge($ids, $this->children[$i]->getSubTreeIDs());
				
			}
		}
		return $ids;
	}
	
	static function getCategoryTree($db, $selected_id = null) {
		if (isset(Self::$cache_tree)) {
			$tree_root = Self::$cache_tree;
		} else {
			$all = Self::select(
				$db, 
				'viewCategories',
				null,
				null,
				null,
				null,
				'category_name');
			
			$tree_root = new Self();
			$tree_root->data['category_name'] = 'MENU';
			$tree_root->is_on_selected_path = true;
			
			while (count($all) > 0) {
				foreach ($all as $i => $c) {
					$parent = null;
					if ($c->ival('category_parent_id') == null) {						
						$parent = $tree_root;						
					} else {
						$parent = $tree_root->findInChildren($c->ival('category_parent_id'));						
					}
					if (isset($parent)) {
						$parent->addChild($c);													
						unset($all[$i]);
					}
				}
			}			
			Self::$cache_tree = $tree_root;
		}
		
		if (isset($selected_id)) {
			
		}
		
		return $tree_root;		
	}
	
	public function getSelectList() {
		$list = [];
		
		if ($this->ival('category_id') > 0) {
			$cat = new Self();
			$cat->data['category_id'] = $this->ival('category_id');
			$cat->data['category_name'] = '';
			if ($this->level > 1) {
				$cat->data['category_name'] = str_repeat('&nbsp;', $this->level);
				$cat->data['category_name'] .= str_repeat('-', $this->level);
				$cat->data['category_name'] .= '&nbsp;';
			}
			$cat->data['category_name'] .= $this->val('category_name');
			$list[] = $cat;
		}
		
		if (isset($this->children)) {
			foreach ($this->children as $cat) {
				$list = array_merge($list, $cat->getSelectList());
			}
		}
		
		return $list;
	}
	
	static function getTreeForSelect($db) {
		$tree = Self::getCategoryTree($db);
		return $tree->getSelectList();
	}
	
	public function loadChildren() {
		$sql = 'SELECT * FROM viewCategories WHERE category_parent_id = ? ORDER BY category_name';
		if ($statement = $this->db->prepare($sql)) {
			$statement->bind_param('i', $this->val('category_id'));
			if ($statement->execute()) {
				$result = $statement->get_result();
				$this->children = [];
				while ($row = $result->fetch_assoc()) {
					$cat = new Self();
					$cat->setData($row);
					$this->children[] = $cat;
				}				
				$statement->close();
			} else {
				dbErr($this->table_name, 'execute', $sql, $this->db->error);					
			}			
		} else {
			dbErr($this->table_name, 'prepare', $sql, $this->db->error);				
		}		
	}
	
	public function loadParentAlias() {
		if ($this->ival('category_parent_id') > 0) {
			$p = new Self($this->db, $this->ival('category_parent_id'));
			$pa = new AliasModel($this->db, $p->ival('category_alias_id'));
			if (!$pa->is_loaded) {		
				$pa->setUrl($p->getAliasUrl());
				$pa->data['alias_path'] = $p->getAliasPath();
				$pa->save();
				$p->data['category_alias_id'] = $pa->val('alias_id');
				$p->save();
			}
			return $pa;
		}
	}
	
	public function getAliasUrl() {
		$pa = $this->loadParentAlias();
		if (isset($pa) && $pa->is_loaded) {
			return $pa->val('alias_url') . '/' . $this->val('category_name');
		} else {
			return $this->val('category_name');
		}
	}
	
	public function getViewPath() {
		return 'default/default/category/' . $this->val('category_id');
	}
	
	public function getLinkPath() {
		if (strlen($this->val('alias_url')) > 0) {
			$url = $this->val('alias_url');		
		} else {
			$url = $this->getViewPath();
		}
		return $url;
	}	
	
}