<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FhtmlAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-7-9
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: html维护，也就是删除已经存在的html实体文件
// +----------------------------------------------------------------------
defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );

/**
 * @name html维护
 *
 */
class FhtmlAction extends FbaseAction {
	
	/**
	 * @name更新主页
	 */
	public function index() {
		//$filesize = $this->_htmls['htmls']->index ();
		if (file_exists_case(FANGFACMS_ROOT . 'index.html')) {
			@unlink(FANGFACMS_ROOT . 'index.html') or $this->message('<font class="red">首页更新失败！</font>');
		}
		
		$this->message ( '<font class="green">网站首页更新成功！</font>', U('findex/home'), 3);
	}
	
	/**
	 * @name栏目页
	 */
	public function category() {
		$in = &$this->in;
		$template_file_extension = substr(C('TMPL_TEMPLATE_SUFFIX'), 1);
		if ($this->ispost() && $this->in['catids']) {
			$catids = implode(',', $this->in['catids']);
			$_c = D('Category');
			$cats = $_c->field('`catid`, `type`, `name`, `catdir`, `url`')
					->where((0==$catids ? 1 : '`catid` IN(' . $catids . ')') . ' AND ishtml=1')
					->order("`catid` DESC")
					->findAll();
			$success = '';
			$error = '<div style="color:red">';
			foreach ($cats AS $c) {
				if ($c['type'] == 'page') {  //单页
					if ($_c->auto_format) {
						$page_path = FANGFACMS_ROOT . trim(substr($c['url'], strlen(__ROOT__)+1), '/\\');
					} else {
						$page_path = FANGFACMS_ROOT . trim($c['url'], '/\\');
					}
					
					$path_info = pathinfo($page_path);
					if (is_file($page_path) && $path_info['extension'] == $template_file_extension) {
						//路径
						$basepath = $path_info['dirname'] . '/';
						$d = dir($basepath);
						while (false !== ($entry = $d->read())) {
							if (preg_match('/^'.$path_info['filename'].'(_\d*)?\.'.$template_file_extension.'$/', $entry)) {
								@unlink($basepath . $entry);
							}
						}
					}
					
					$success .= $c['name'] . ' 更新成功！</font>';
				} else {  //内部栏目
					$basepath = rtrim(FANGFACMS_ROOT, '/\\') . '/' . trim($c['catdir'], '/\\') . '/';
					if (is_dir($basepath)) {  //存在栏目文件夹
						$d = dir($basepath);
						while (false !== ($entry = $d->read())) {  //循环删除栏目主页文件，包括栏目分页文件
							if (preg_match('/^index(_\d*)?\.'.$template_file_extension.'$/', $entry)) {
								@unlink($basepath . $entry);
							}
						}
					}
					
					$success .= $c['name'] . ' 更新成功！';
				}
			}
			$error .= '</div>';
			
			$this->message('<font class="green">' . $success . $error . '</font>' , U('fhtml/category'), 30);
		} else {  //所有栏目列表
			$data = D('Category')->field("`catid` AS `id`,`name`,`modelid`,`parentid`")->order("`sort` ASC,`catid` DESC")->findAll();
			import ( 'Tree', INCLUDE_PATH );
			$tree = get_instance_of ( 'Tree' );
			$tree->init ( $data );
			$str = "<option value='\$id'>\$spacer\$name</option>";
			$html = $tree->get_tree ( 0, $str );
			$this->assign('html',$html);
		}
		$this->display();
	}
	
	/**
	 * @name更新详细内容页
	 */
	public function show() {
		$in = &$this->in;
		$template_file_extension = substr(C('TMPL_TEMPLATE_SUFFIX'), 1);
		
		if ($this->ispost() && $in['catids']) {  //获取需要更新的Html列表			
			$catids = implode(',', $this->in['catids']);
			$where = "c.`url`!=''";
			$where .= (0==$catids ? "" : " AND c.`catid` IN($catids)");
			!empty($in['startid']) && $where .= " AND c.cid >= " . $in['startid'];
			!empty($in['endid']) && $where .= " AND c.cid <= " . $in['endid'];
			!empty($in['starttime']) && $where .= " AND c.create_time >= " . strtotime($in['starttime']);
			!empty($in['endtime']) && $where .= " AND c.create_time <= " . strtotime($in['endtime']);
			$Content = D('Content');
			
			//获取需要更新的内容列表
			$content_list = $Content->query('SELECT c.cid, c.catid, c.url, cat.catdir FROM `'.C('DB_PREFIX').'content` as c LEFT JOIN `'.C('DB_PREFIX').'category` as cat ON c.catid=cat.catid WHERE ' . $where . ' ORDER BY c.sort ASC,c.catid DESC');
			if (!empty($content_list) && is_array($content_list)) {
				foreach ($content_list AS &$cl)
				{
					//获取Html绝对地址
					$cl['url'] = rtrim(FANGFACMS_ROOT, '/\\') . '/' . trim($cl['catdir'], '/\\') . '/' . $cl['url'];
				}
			} else {
				$this->message('<font class="red">没有需要更新的数据！</font>', U('fhtml/show'), 3);
			}

			$this->assign('pagesize', $in['pagesize']);
			$this->assign('total', count($content_list));
			$this->assign('complete', 0);
			$this->assign('content_list', $content_list);
		} else if ($this->ispost() && $in['content_list']) {  //开始更新HTML
			$in['pagesize'] = empty($in['pagesize']) ? 50 : intval($in['pagesize']);
			$i = 0;
			if (($_total = count($in['content_list'])) > 0) {
				foreach ($in['content_list'] AS $k=>$html) {
					if ($i >= $in['pagesize']) {
						break;
					}
					
					$base_info = pathinfo($html);
					//路径
					$basepath = $base_info['dirname'] . '/';
					
					if (is_file($html) && $base_info['extension'] == $template_file_extension) {
						$d = dir($basepath);
						while (false !== ($entry = $d->read())) {
							if (preg_match('/^'.$base_info['filename'].'(_\d*)?\.'.$template_file_extension.'$/', $entry)) {
								@unlink($basepath . $entry);
							}
						}
					}
					
					$i++;
					$in['complete']++;
					unset($in['content_list'][$k]);
					if($k+1 == $_total) $this->message('<font class="green">全部更新完成！</font>', U('fhtml/show'), 5);
				}
			}
			
			$this->assign('pagesize', $in['pagesize']);
			$this->assign('total', $in['total']);
			$this->assign('complete', $in['complete']);
			$this->assign('content_list', $in['content_list']);
		} else {  //显示界面
			$data = D('Category')
					->field("`catid` AS `id`,`name`,`modelid`,`parentid`")
					//->where("`ishtml`='1' AND `type`='normal'")
					->order("`sort` ASC,`catid` DESC")
					->findAll();
			import ( 'Tree', INCLUDE_PATH );
			$tree = get_instance_of ( 'Tree' );
			$tree->init ( $data );
			$str = "<option value='\$id'>\$spacer\$name</option>";
			$html = $tree->get_tree ( 0, $str );
			$this->assign('html',$html);
		}
		
		$this->display();
	}
	
	/**
	 * @name生成 sitemap文件
	 */
	public function sitemaps()
	{
		$in = &$this->in;
		
		if ($this->ispost()) {
			$Setting = M('setting');
			$cfg_setting = $Setting->getField('var,value');
			
			$items = array();   //sitemaps
			$baidunews_items = array();  //baidunews
			//首页
			$items[0] = array(
				'loc'		=>	$cfg_setting['siteurl'],
				'lastmod'	=>	date('Y-m-d'),
				'changefreq'	=>	'daily',
				'priority'	=>	'1.0'
			);
			
			$Category = M('category');
			$category_list = $Category->where('`type` IN("normal", "page")')->findAll();
			
			foreach ($category_list AS $c) {
				$status = false;   //判断是否插入到xml
				switch ($c['type']) {
					case 'normal':
						if (file_exists(FANGFACMS_ROOT . $c['url'] . 'index' . C('TMPL_TEMPLATE_SUFFIX'))) {
							$status = true;
						}
						break;
					case 'page':
						if (file_exists(FANGFACMS_ROOT . $c['url'])) {
							$status = true;
						}
						break;
				}
				$status && $items[] = array(
					'loc'		=>	$cfg_setting['siteurl'] . '/' . $c['url'],
					'lastmod'	=>	date('Y-m-d'),
					'changefreq'	=>	'daily',
					'priority'	=>	'0.9'
				);
			}
			
			$Content = D('Content');
			$limit = $in['baidunum'] > $in['num'] ? $in['baidunum'] : $in['num'];
			$query = "SELECT {db_prefix}content.*, {db_prefix}category.name AS category_name, {db_prefix}model.tablename FROM {db_prefix}content "
				   . " LEFT JOIN {db_prefix}category ON {db_prefix}category.catid={db_prefix}content.catid "
				   . " LEFT JOIN {db_prefix}model ON {db_prefix}category.modelid={db_prefix}model.modelid "
				   . " ORDER BY update_time DESC LIMIT 0," . $limit;
			$query = str_replace('{db_prefix}', C('DB_PREFIX'), $query);
			
			$content_list = $Content->query($query);
			//sitemaps
			require_cache(INCLUDE_PATH . 'sitemaps.class.php');
			$google_sitemap = new google_sitemap();
			
			foreach ($content_list AS $k=>$c) {
				$link = $cfg_setting['siteurl'] . '/' . ltrim($Content->getUrl($c), '/\\');
				if ($k < $in['baidunum']) {
					$items[] = array(
						'loc'		=>	$link,
						'lastmod'	=>	date('Y-m-d', $c['update_time']),
						'changefreq'	=>	$in['content_changefreq'],
						'priority'		=>	$in['content_priority']
					);
				}
				
				//获取正文内容，用于baidunews
				if ($in['mark'] == 1 && $k < $in['num']) {
					$baidunews_content = $Content->query('SELECT content FROM fangfa_' . $c['tablename'] . ' WHERE cid=' . $c['cid']);
					$baidunews_items[$k] = $c;
					$baidunews_items[$k]['content'] = $baidunews_content[0]['content'];
					$baidunews_items[$k]['siteurl'] = $cfg_setting['siteurl'];
					$baidunews_items[$k]['link'] = $link;
					$baidunews_items[$k]['thumb'] = $cfg_setting['siteurl'] . '/' . C('UPLOAD_DIR') . $c['thumb'];
				}
			}
			
			$google_sitemap->items = $items;
			$google_sitemap->build(FANGFACMS_ROOT . 'sitemaps.xml');
			
			//baidunews
			if ($in['mark'] == 1) {
				require_cache(INCLUDE_PATH . 'baidunews.class.php');
				$baidunews = new baidunews($cfg_setting['seotitle'], $in['email'], $in['time'], $in['num']);
				$baidunews->baidunews_items = $baidunews_items;
				$baidunews->file_name = FANGFACMS_ROOT . 'baidunews.xml';
				$baidunews->set_xml();
			}
			
			$this->message('<font class="green">XML更新成功！</font>', $in['forward']);
		}
		
		$User = M('user');
		$admin_user = $User->field('email')->order('user_id ASC')->find();
		$this->assign('admin_email', $admin_user['email']);
		$this->display();
	}
	
	/**
	 * @name生成rss文件
	 */
	public function rss()
	{
		$in = &$this->in;
		
		if ($this->ispost()) {
			$Setting = M('setting');
			$cfg_setting = $Setting->getField('var,value');
			
			$Content = D('content');
			$limit = $in['baidunum'] > $in['num'] ? $in['baidunum'] : $in['num'];
			$query = "SELECT {db_prefix}content.*, {db_prefix}category.name AS category_name, {db_prefix}model.tablename FROM {db_prefix}content "
				   . " LEFT JOIN {db_prefix}category ON {db_prefix}category.catid={db_prefix}content.catid "
				   . " LEFT JOIN {db_prefix}model ON {db_prefix}category.modelid={db_prefix}model.modelid "
				   . " ORDER BY update_time DESC LIMIT 0," . $limit;
			$query = str_replace('{db_prefix}', C('DB_PREFIX'), $query);
			
			$content_list = $Content->query($query);
			
			require_cache(INCLUDE_PATH . 'Rss.class.php');
			$encoding =(string) 'UTF-8';
			$about = (string) $cfg_setting['siteurl'];
			$title = (string) $cfg_setting['seotitle'];
			$description = (string) $cfg_setting['seodescription'];
			$image_link = (string) '';
			$category = (string) $cfg_setting['companyname'];
			$cache = (string) 60; // in minutes (only rss 2.0)
			$rssfile = new RSSBuilder($encoding, $about, $title, $description, $image_link, $category, $cache);
			
			/* if you want you can add additional Dublic Core data to the basic 
			rss file (if rss version supports it) */
			$publisher = (string) $cfg_setting['companyname']; // person, an organization, or a service
			$creator = (string) $cfg_setting['companyname']; // person, an organization, or a service
			$date = (string) time();
			$language = (string) 'zh';
			$rights = (string) $cfg_setting['copyright'];
			$coverage = (string) ''; // spatial location , temporal period or jurisdiction
			$contributor = (string) $cfg_setting['companyname']; // person, an organization, or a service
			$rssfile->addDCdata($publisher,	$creator, $date, $language,	$rights, $coverage, $contributor);
			
			/* if you want you can add additional Syndication data to the basic rss 
			file (if rss version supports it) */
			$period = (string) 'daily'; // hourly / daily / weekly / …
			$frequency = (int) 1; // every x hours / days / …
			$base = (string) time()-10000;
			$rssfile->addSYdata($period, $frequency, $base);
			
			/* data for a single RSS item */
			
			foreach ($content_list AS $k=>$c) {
				$about = $link = $cfg_setting['siteurl'] . '/' . ltrim($Content->getUrl($c), '/\\');
				$title = (string) $c['title'];
				$description = (string) $c['description'];
				$subject = (string) $c['category_name']; // optional DC value
				$date = (string) $c['update_time']; // optional DC value
				$author = (string) $c['username']; // author of item
				$comments = (string) $link; // url to comment page rss 2.0 value
				$image = (string) (!empty($c['thumb']) ? $cfg_setting['siteurl'] . '/' . C('UPLOAD_DIR') . $c['thumb'] : ''); // optional mod_im value for dispaying a different pic for every item
				$rssfile->addItem($about, $title, $link, $description, $subject, $date,	$author, $comments, $image);
			}
			
			$version = '2.0'; // 0.91 / 1.0 / 2.0
			//$rssfile->outputRSS($version);
			
			$foo = $rssfile->getRSSOutput($version);
			echo FANGFACMS_ROOT;
			file_put_contents(FANGFACMS_ROOT . 'rss.xml', $foo);
			
			$this->message('<font class="green">RSS更新成功！</font>', $in['forward']);
		}
		
		$this->display();
	}
}