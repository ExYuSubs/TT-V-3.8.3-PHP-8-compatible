<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

require_once("backend/functions.php");
dbconn();

//check permissions
if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["view_torrents"]=="no")
		show_error_msg(T_("ERROR"), T_("NO_TORRENT_VIEW"), 1);
}

function sqlwildcardesc($x){
    return str_replace(array("%","_"), array("\\%","\\_"), mysqli_real_escape_string($GLOBALS["DBconnector"],$x));
}

//GET SEARCH STRING
$searchstr = trim($_GET["search"] ?? ''); // Handle undefined key with default empty string
$cleansearchstr = searchfield($searchstr);

// Check if $cleansearchstr is empty
if (empty($cleansearchstr)) {
    $cleansearchstr = null; // Optionally set to null instead of unsetting
}

$thisurl = "torrents-search.php?";
$addparam = "";
$wherea = ["banned = 'no'"]; // Initialize with default condition

// Initialize arrays for categories
$wherecatina = [];
$wherecatin = "";

// Query categories
$res = SQL_Query_exec("SELECT id FROM categories");
if (!$res) {
    die("Failed to fetch categories."); // Add error handling
}

while ($row = mysqli_fetch_assoc($res)) {
    $id = $row['id']; // Safely access 'id'
    $key = "c$id";    // Construct dynamic key

    if (isset($_GET[$key])) { // Check for key in $_GET
        $wherecatina[] = $id;  // Add to category filter
        $addparam .= "$key=1&amp;"; // Append to additional params
        $thisurl .= "$key=1&amp;";  // Append to URL
    }
}

// Create comma-separated category IDs for the query
$wherecatin = implode(", ", $wherecatina);


$wherecatin = implode(", ", $wherecatina ?? []); // Use null coalescing to ensure the array exists

if ($wherecatin)
    $wherea[] = "category IN ($wherecatin)";


//include dead
// Check for incldead
$incldead = $_GET["incldead"] ?? 0;
if ($incldead == 1) {
    $addparam .= "incldead=1&amp;";
    $thisurl .= "incldead=1&amp;";
} elseif ($incldead == 2) {
    $wherea[] = "visible = 'no'";
    $addparam .= "incldead=2&amp;";
    $thisurl .= "incldead=2&amp;";
} else {
    $wherea[] = "visible = 'yes'";
}

// Include freeleech
$freeleech = $_GET["freeleech"] ?? 0;
if ($freeleech == 1) {
    $addparam .= "freeleech=1&amp;";
    $thisurl .= "freeleech=1&amp;";
    $wherea[] = "freeleech = '0'";
} elseif ($freeleech == 2) {
    $addparam .= "freeleech=2&amp;";
    $thisurl .= "freeleech=2&amp;";
    $wherea[] = "freeleech = '1'";
}

// Include external
$inclexternal = $_GET["inclexternal"] ?? 0;
if ($inclexternal == 1) {
    $addparam .= "inclexternal=1&amp;";
    $wherea[] = "external = 'no'";
} elseif ($inclexternal == 2) {
    $addparam .= "inclexternal=2&amp;";
    $wherea[] = "external = 'yes'";
}

// Category
$cat = $_GET["cat"] ?? null;
if ($cat) {
    $wherea[] = "category = " . sqlesc($cat);
    $wherecatina[] = sqlesc($cat);
    $addparam .= "cat=" . urlencode($cat) . "&amp;";
    $thisurl .= "cat=" . urlencode($cat) . "&amp;";
}

// Language
$lang = $_GET["lang"] ?? null;
if ($lang) {
    $wherea[] = "torrentlang = " . sqlesc($lang);
    $addparam .= "lang=" . urlencode($lang) . "&amp;";
    $thisurl .= "lang=" . urlencode($lang) . "&amp;";
}

// Parent category
$parent_cat = $_GET["parent_cat"] ?? null;
if ($parent_cat) {
    $addparam .= "parent_cat=" . urlencode($parent_cat) . "&amp;";
    $thisurl .= "parent_cat=" . urlencode($parent_cat) . "&amp;";
}

$wherebase = $wherea;

// Search string
if (isset($cleansearchstr)) {
    $wherea[] = "MATCH (torrents.name) AGAINST ('" . mysqli_real_escape_string($GLOBALS["DBconnector"], $searchstr) . "' IN BOOLEAN MODE)";
    $addparam .= "search=" . urlencode($searchstr) . "&amp;";
    $thisurl .= "search=" . urlencode($searchstr) . "&amp;";
}

// Sort and Order
$sort = $_GET["sort"] ?? "id";
$order = $_GET["order"] ?? "desc";

switch ($sort) {
    case 'id': $column = "id"; break;
    case 'name': $column = "name"; break;
    case 'comments': $column = "comments"; break;
    case 'size': $column = "size"; break;
    case 'times_completed': $column = "times_completed"; break;
    case 'seeders': $column = "seeders"; break;
    case 'leechers': $column = "leechers"; break;
    case 'category': $column = "category"; break;
    default: $column = "id"; break;
}

switch ($order) {
    case 'asc': $ascdesc = "ASC"; break;
    case 'desc': $ascdesc = "DESC"; break;
    default: $ascdesc = "DESC"; break;
}

$orderby = "ORDER BY torrents." . $column . " " . $ascdesc;
$pagerlink = "sort=" . $sort . "&amp;order=" . $order . "&amp;";

// Pagination
$page = $_GET["page"] ?? null;
if (is_valid_id($page)) {
    $thisurl .= "page=$page&amp;";
}



$where = implode(" AND ", $wherea);

if ($where != "")
	$where = "WHERE $where";

$parent_check = "";
if ($parent_cat){
	$parent_check = " AND categories.parent_cat=".sqlesc($parent_cat);
}


//GET NUMBER FOUND FOR PAGER
$res = SQL_Query_exec("SELECT COUNT(*) FROM torrents $where $parent_check");
$row = mysqli_fetch_array($res);
$count = $row[0];


if (!$count && isset($cleansearchstr)) {
	$wherea = $wherebase;
	$searcha = explode(" ", $cleansearchstr);
	$sc = 0;
	foreach ($searcha as $searchss) {
		if (strlen($searchss) <= 1)
		continue;
		$sc++;
		if ($sc > 5)
		break;
		$ssa = [];
		foreach (array("torrents.name") as $sss)
		$ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
		$wherea[] = "(" . implode(" OR ", $ssa) . ")";
	}
	if ($sc) {
		$where = implode(" AND ", $wherea);
		if ($where != "")
		$where = "WHERE $where";
		$res = SQL_Query_exec("SELECT COUNT(*) FROM torrents $where $parent_check");
		$row = mysqli_fetch_array($res);
		$count = $row[0];
	}
}

//Sort by
if ($addparam != "") { 
	if ($pagerlink != "") {
		if ($addparam[strlen($addparam)-1] != ";") { // & = &amp;
			$addparam = $addparam . "&amp;" . $pagerlink;
		} else {
			$addparam = $addparam . $pagerlink;
		}
	}
} else {
	$addparam = $pagerlink;
}



if ($count) {

	//SEARCH QUERIES! 
	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "torrents-search.php?" . $addparam);
	$query = "SELECT torrents.id, torrents.anon, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.parent_cat AS cat_parent, categories.image AS cat_pic, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $parent_check $orderby $limit";
	$res = SQL_Query_exec($query);

	}else{
		unset($res);
}

if (isset($cleansearchstr))
	stdhead(T_("SEARCH_RESULTS_FOR")." \"" . htmlspecialchars($searchstr) . "\"");
else
	stdhead(T_("BROWSE_TORRENTS"));

begin_frame(T_("SEARCH_TORRENTS"));

// get all parent cats
echo "<center><b>".T_("CATEGORIES").":</b> ";
$catsquery = SQL_Query_exec("SELECT distinct parent_cat FROM categories ORDER BY parent_cat");
echo " - <a href='torrents.php'>".T_("SHOWALL")."</a>";
while($catsrow = mysqli_fetch_assoc($catsquery)){
		echo " - <a href='torrents.php?parent_cat=".urlencode($catsrow['parent_cat'])."'>$catsrow[parent_cat]</a>";
}
echo "</center>";

?>
<br /><br />

<center>
<form method="get" action="torrents-search.php">
<table border="0" align="center">
<tr align='right'>
<?php
$i = 0;
$cats = SQL_Query_exec("SELECT * FROM categories ORDER BY parent_cat, name");
$catsperrow = 5;
$i = 0; // Ensure $i is initialized before use
while ($cat = mysqli_fetch_assoc($cats)) {
    // Verify keys exist in the $cat array before accessing them
    $cat_id = $cat['id'] ?? null;
    $parent_cat = $cat['parent_cat'] ?? '';
    $name = $cat['name'] ?? '';
    
    // Print a new row after every $catsperrow items
    print(($i && $i % $catsperrow == 0) ? "</tr><tr align='right'>" : "");
    
    // Check if $cat_id is valid before printing the checkbox and link
    if ($cat_id !== null) {
        print("<td style=\"padding-bottom: 2px; padding-left: 2px\">");
        print("<a href='torrents.php?cat={$cat_id}'>" . htmlspecialchars($parent_cat) . " - " . htmlspecialchars($name) . "</a> ");
        
        // Check for selected category in $wherecatina or $_GET
        $is_checked = (in_array($cat_id, $wherecatina ?? []) || ($_GET['cat'] ?? null) == $cat_id) ? "checked='checked' " : "";
        print("<input name='c{$cat_id}' type=\"checkbox\" {$is_checked} value='1' />");
        print("</td>\n");
    }
    $i++;
}

echo "</tr></table>";

//if we are browsing, display all subcats that are in same cat
if ($parent_cat){
    echo "<br /><br /><b>".T_("YOU_ARE_IN").":</b> <a href='torrents.php?parent_cat=$parent_cat'>$parent_cat</a><br /><b>".T_("SUB_CATS").":</b> ";
	$subcatsquery = SQL_Query_exec("SELECT id, name, parent_cat FROM categories WHERE parent_cat='$parent_cat' ORDER BY name");
	while($subcatsrow = mysqli_fetch_assoc($subcatsquery)){
		$name = $subcatsrow['name'];
		echo " - <a href='torrents.php?cat=$subcatsrow[id]'>$name</a>";
	}
}	

echo "<br /><br />";//some spacing

?>

    
	<?php print(T_("SEARCH")); ?>
	<input type="text" name="search" size="40" value="<?php echo  stripslashes(htmlspecialchars($searchstr)) ?>" />
	<?php print(T_("IN")); ?>
	<select name="cat">
	<option value="0"><?php echo "(".T_("ALL")." ".T_("TYPES").")";?></option>
	<?php


	$cats = genrelist();
	$catdropdown = "";
	foreach ($cats as $cat) {
		$catdropdown .= "<option value=\"" . $cat["id"] . "\"";
		if ($cat["id"] == $_GET["cat"])
			$catdropdown .= " selected=\"selected\"";
		$catdropdown .= ">" . htmlspecialchars($cat["parent_cat"]) . ": " . htmlspecialchars($cat["name"]) . "</option>\n";
	}	
	?>
	<?php echo  $catdropdown ?>
	</select>

	<br /><br />
	<select name="incldead">
 	<option value="0"><?php echo T_("ACTIVE_TRANSFERS"); ?></option>
	<option value="1" <?php if (isset($_GET["incldead"]) == 1) echo "selected='selected'"; ?>><?php echo T_("INC_DEAD");?></option>
	<option value="2" <?php if (isset($_GET["incldead"]) == 2) echo "selected='selected'"; ?>><?php echo T_("ONLY_DEAD");?></option>
	</select>
	<select name="freeleech">
	<option value="0"><?php echo T_("ALL"); ?></option>
	<option value="1" <?php if (isset($_GET["freeleech"]) == 1) echo "selected='selected'"; ?>><?php echo T_("NOT_FREELEECH");?></option>
	<option value="2" <?php if (isset($_GET["freeleech"]) == 2) echo "selected='selected'"; ?>><?php echo T_("ONLY_FREELEECH");?></option>
 	</select>

	<?php if ($site_config["ALLOWEXTERNAL"]){?>
		<select name="inclexternal">
 		<option value="0"><?php echo T_("LOCAL_EXTERNAL"); ?></option>
		<option value="1" <?php if (isset($_GET["inclexternal"]) == 1) echo "selected='selected'"; ?>><?php echo T_("LOCAL_ONLY");?></option>
		<option value="2" <?php if (isset($_GET["inclexternal"]) == 2) echo "selected='selected'"; ?>><?php echo T_("EXTERNAL_ONLY");?></option>
 		</select>
	<?php } ?>

	<select name="lang">
	<option value="0"><?php echo "(".T_("ALL").")";?></option>
	<?php
	$lang = langlist();
	$langdropdown = "";
	foreach ($lang as $lang) {
		$langdropdown .= "<option value=\"" . $lang["id"] . "\"";
		if ($lang["id"] == $_GET["lang"])
			$langdropdown .= " selected=\"selected\"";
		$langdropdown .= ">" . htmlspecialchars($lang["name"]) . "</option>\n";
	}
	
	?>
	<?php echo  $langdropdown ?>
	</select>
	<input type="submit" value="<?php print T_("SEARCH"); ?>" />
	<br />
	</form>
	<?php print T_("SEARCH_RULES"); ?><br />
    </center>
    
<?php

if ($count) {
// New code (TorrentialStorm)
	echo "<form id='sort' action=''><div align='right'>".T_("SORT_BY").": <select name='sort' onchange='window.location=\"{$thisurl}sort=\"+this.options[this.selectedIndex].value+\"&amp;order=\"+document.forms[\"sort\"].order.options[document.forms[\"sort\"].order.selectedIndex].value'>";
	echo "<option value='id'" . ($_GET["sort"] == "id" ? " selected='selected'" : "") . ">".T_("ADDED")."</option>";
	echo "<option value='name'" . ($_GET["sort"] == "name" ? " selected='selected'" : "") . ">".T_("NAME")."</option>";
	echo "<option value='comments'" . ($_GET["sort"] == "comments" ? " selected='selected'" : "") . ">".T_("COMMENTS")."</option>";
	echo "<option value='size'" . ($_GET["sort"] == "size" ? " selected='selected'" : "") . ">".T_("SIZE")."</option>";
	echo "<option value='times_completed'" . ($_GET["sort"] == "times_completed" ? " selected='selected'" : "") . ">".T_("COMPLETED")."</option>";
	echo "<option value='seeders'" . ($_GET["sort"] == "seeders" ? " selected='selected'" : "") . ">".T_("SEEDERS")."</option>";
	echo "<option value='leechers'" . ($_GET["sort"] == "leechers" ? " selected='selected'" : "") . ">".T_("LEECHERS")."</option>";
	echo "</select>&nbsp;";
	echo "<select name='order' onchange='window.location=\"{$thisurl}order=\"+this.options[this.selectedIndex].value+\"&amp;sort=\"+document.forms[\"sort\"].sort.options[document.forms[\"sort\"].sort.selectedIndex].value'>";
	echo "<option selected='selected' value='asc'" . ($_GET["order"] == "asc" ? " selected='selected'" : "") . ">".T_("ASCEND")."</option>";
	echo "<option value='desc'" . ($_GET["order"] == "desc" ? " selected='selected'" : "") . ">".T_("DESCEND")."</option>";
	echo "</select>";
    echo "</div>";
	echo "</form>";

// End
	torrenttable($res);
	print($pagerbottom);
}else {
    
     print("<div class='f-border'>");
     print("<div class='f-cat' width='100%'>".T_("NOTHING_FOUND")."</div>");
     print("<div>");
     print T_("NO_RESULTS");
     print("</div>");
     print("</div>");
     
}

if ($CURUSER)
	SQL_Query_exec("UPDATE users SET last_browse=".gmtime()." WHERE id=$CURUSER[id]");


end_frame();
stdfoot();

?>
