<?php
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/category.php");

$releases = new Releases;
$grp = new Groups;
$c = new Category;

if (!$users->isLoggedIn())
	$page->show403();

$page->meta_title = "Search Nzbs";
$page->meta_keywords = "search,nzb,description,details";
$page->meta_description = "Search for Nzbs";

$results = array();
$searchStr = '';

if (isset($_REQUEST["id"]))
{
	$searchStr = (string) $_REQUEST["id"];

	$categoryId = array();
	if (isset($_GET["t"]))
		$categoryId = explode(",",$_REQUEST["t"]);
	else
		$categoryId[] = -1;		

	$offset = (isset($_REQUEST["offset"]) && ctype_digit($_REQUEST['offset'])) ? $_REQUEST["offset"] : 0;
	$ordering = $releases->getBrowseOrdering();
	$orderby = isset($_REQUEST["ob"]) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST["ob"] : '';
	foreach($ordering as $ordertype) {
		$page->smarty->assign('orderby'.$ordertype, WWW_TOP."/search/".htmlentities($searchStr)."?t=".(implode(',',$categoryId))."&amp;ob=".$ordertype);
	}
	$page->smarty->assign('category', $categoryId);
	$page->smarty->assign('lastvisit', $page->userdata['lastlogin']);
	
	$results = $releases->search($searchStr, $categoryId, $offset, ITEMS_PER_PAGE, $orderby, -1, $page->userdata["categoryexclusions"]);
	
	if (sizeof($results) > 0)
		$totalRows = $results[0]['_totalrows'];
	else
		$totalRows = 0;

	$page->smarty->assign('pagertotalitems',$totalRows);
	$page->smarty->assign('pageroffset',$offset);
	$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
	$page->smarty->assign('pagerquerybase', WWW_TOP."/search/".htmlentities($searchStr)."?t=".(implode(',',$categoryId))."&amp;ob=".$orderby."&amp;offset=");
	$page->smarty->assign('pagerquerysuffix', "#results");
	
	$pager = $page->smarty->fetch($page->getCommonTemplate("pager.tpl"));
	$page->smarty->assign('pager', $pager);

}

$grouplist = $grp->getGroupsForSelect();
$page->smarty->assign('grouplist', $grouplist);	

$catlist = $c->getForSelect();
$page->smarty->assign('catlist', $catlist);	

$sizelist = array( -1 => '--Select--',
					0 => '0 bytes',
					0.5 => '500 mb',
					1 => '1 gb',
					2 => '2 gb', 
					3	=> '3 gb',			
					4	=> '4 gb'			
					) ;

$page->smarty->assign('sizelist', $sizelist);

$page->smarty->assign('search', $searchStr);
$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('search.tpl');
$page->render();

?>
