<?php

/**
* This class simplifies paging in mysql recordsets.
*/
class zPaging {

	public $offset = 0;
	public $limit = 10;

	public $url_name = 'p';
	public $sorting_url_name = 's';
	public $sorting_desc_url_name = 'sd';
	public $filter_url_name = 'f';

	public $total_records = null;

	public $current_page = 0;
	public $total_pages = 0;

	public $max_pages_links = 20;

	public $filter = null;
	public $orderby = null;

	public $allowed_sorting_items = [];
	public $active_sorting = null;
	public $sorting_desc = false;

	function __construct($custom_offset = null, $custom_limit = null, $custom_max_pages_links = null) {
		if (isset($custom_offset)) {
			$this->offset = z::parseInt($custom_offset);
		}
		if (isset($custom_limit)) {
			$this->limit = z::parseInt($custom_limit);
		}
		if (isset($custom_max_pages_links)) {
			$this->max_pages_links = z::parseInt($custom_max_pages_links);
		}
	}

	static function getFromUrl($default_paging = null) {
		if ($default_paging == null) {
			$paging = new zPaging();
		} else {
			$paging = $default_paging;
		}
		$paging->loadFromUrl();
		return $paging;
	}

	public function loadFromUrl() {
		if (isset($_GET[$this->url_name])) {
			$arr = explode(',', $_GET[$this->url_name]);
			$this->offset = z::parseInt($arr[0]);
			$this->limit = z::parseInt($arr[1]);
		}
		if (isset($_GET[$this->sorting_url_name])) {
			$sort = $_GET[$this->sorting_url_name];
			if (in_array($sort, $this->allowed_sorting_items)) {
				$this->active_sorting = $_GET[$this->sorting_url_name];
				if (isset($_GET[$this->sorting_desc_url_name])) {
					$this->sorting_desc = true;
				}
			}
		}
	}

	public function getLinkUrl($offset = null, $limit = null, $sorting = null, $sorting_desc = null, $filter = null) {
		$offset = isset($offset) ? $offset : $this->offset;
		$limit = isset($limit) ? $limit : $this->limit;
		$sorting = isset($sorting) ? $sorting : $this->active_sorting;
		$desc = isset($sorting_desc) ? $sorting_desc : $this->sorting_desc;
		$filter = isset($filter) ? $filter : $this->filter;

		$url = '?';
		$url .= sprintf('%s=%d,%d', $this->url_name, $offset, $limit);
		if (isset($sorting) && strlen($sorting)>0) {
			$url .= sprintf('&%s=%s', $this->sorting_url_name, $sorting);
		}
		if (isset($filter) && strlen($filter)>0) {
			$url .= sprintf('&%s=%s', $this->filter_url_name, $filter);
		}
		if ($desc) {
			$url .= sprintf('&%s=%s', $this->sorting_desc_url_name, 'desc');
		}
		return $url;
	}

	public function getLinks($base_url) {
		if (!isset($this->cache_links)) {
			$links = [];
			$this->total_pages = ceil($this->total_records / $this->limit);
			$this->current_page = ceil($this->offset / $this->limit) + 1;

			if ($this->total_pages > 1) {

				// do not render all pages links
				// if there is too many of them at the beginning or ending
				$render_start = 1;
				$render_end = $this->total_pages;
				if ($this->total_pages > $this->max_pages_links) {
					$allowed_links = floor(($this->max_pages_links-1)/2);
					if (($this->current_page - $allowed_links) <= 1) { // only in ending
						$render_end = $this->max_pages_links - 1;
					} elseif ($this->current_page > ($this->total_pages - $allowed_links)) { // only in beginning
						$render_start = $this->total_pages - $this->max_pages_links + 2;
					} else { // both
						$render_start = $this->current_page - $allowed_links + 1;
						$render_end = $this->current_page + $allowed_links - 1;
					}
				}

				// render Fast Prev button
				/*
				if ($render_start > 1) {
					$offset = max($render_start-2, 0) * $this->limit;
					$href = $this->getLinkUrl($offset);
					$links[] = [
						'href' => $href,
						'title' => '<span class="glyphicon glyphicon-backward"></span>'
					];
				}
				*/

				// render Previous button
				if ($this->offset <= 0) {
					$class = 'disabled';
					$href = $this->getLinkUrl(0);
				} else {
					$class = '';
					$offset = $this->offset - $this->limit;
					if ($offset < 0) {
						$offset = 0;
					}
					$href = $this->getLinkUrl($offset);
				}

				$links[] = [
					'class' => $class,
					'href' => $href,
					'title' => '<span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span>'
				];

				//render page buttons
				for ($i = $render_start; $i <= $render_end; $i++ ) {
					$link = [];
					$link['class'] = '';
					$offset = ($i - 1) * $this->limit;
					$link['href'] = $this->getLinkUrl($offset);

					if ($this->offset == $offset) {
						$link['class'] = 'active';
					}
					$link['title'] = $i;
					$links[] = $link;
				}

				//render Next button
				$offset = $this->offset + $this->limit;
				if ($offset >= $this->total_records) {
					$class = 'disabled';
					$href = $this->getLinkUrl($this->offset);
				} else {
					$class = '';
					$href = $this->getLinkUrl($offset);
				}

				$links[] = [
					'class' => $class,
					'href' => $href,
					'title' => '<span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span>'
				];

				// render Fast Next button
				/*
				if ($render_end < $this->total_pages) {
					$offset = min($render_end, $this->total_pages) * $this->limit;
					$href = $this->getLinkUrl($offset);
					$links[] = [
						'href' => $href,
						'title' => '<span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span>'
					];
				}
				*/
			}
			$this->cache_links = $links;
		}
		return $this->cache_links;
	}

	public function getInfo() {
		$upper = $this->offset + $this->limit;
		if ($upper > $this->total_records) {
			$upper = $this->total_records;
		}
		return t('%d - %d of %d', $this->offset+1, $upper, $this->total_records);
	}

	public function getOrderBy() {
		if (isset($this->active_sorting)) {
			return $this->active_sorting . ($this->sorting_desc ? ' DESC' : ' ASC');
		}
	}

	public function getLimit() {
		return sprintf('%d, %d', $this->offset, $this->limit);
	}

	public function renderLinks() {

		$links = $this->getLinks('');

		if (count($links) > 0) {
			?>
				<div class="mb-2">
					<nav>
						<ul class="pagination">
							<?php
								foreach ($links as $link) {
									?>
										<li class="page-item <?=$link['class'] ?>"><a class="page-link" href="<?=$link['href']?>"><?=$link['title']?></a></li>
									<?php
								}
							?>
							<li><div class="d-inline-block p-2"><?=sprintf('%d / %d', $this->current_page, $this->total_pages);?></div></li>
						</ul>
					</nav>
				</div>
			<?php


		}
	}

}
