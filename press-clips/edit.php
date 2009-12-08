<?
$included = isset($_josh);
if (!$included) include('../include.php');

$r = false;
if ($posting) {
	$id = db_save("press_clips");
	if (getOption('channels')) db_checkboxes('channels', 'press_clips_to_channels', 'clip_id', 'channel_id', $id);
	url_change_post("/press-clips/clip.php?id=" . $id);
} elseif ($included) {
	$action = getString('add_new');
	$_josh["request"]["path_query"] = "edit.php"; //shoddy way of setting the form target
	$r["url"] = "http://";
} elseif (url_id()) {
	$action = getString('edit');
	echo drawTop();
	$r = db_grab("SELECT id, title, url, publication, pub_date, description, type_id from press_clips WHERE id = " . $_GET["id"]);
	$r["title"] = format_title($r["title"], "US");
} else {
	$action = getString('add_new');
	echo drawTop();
	if (isset($_GET["title"])) $r["title"] = format_title($_GET["title"], "US");
	if (isset($_GET["url"])) {
		$r["url"] = $_GET["url"];
		$url = url_parse($r["url"]);
	
		if ($url["domainname"] == "nytimes") {
			$r["publication"] = "NY Times";
			$r["title"] = str_replace("- Nytimes.com", "", $r["title"]);
		} elseif ($url["domainname"] == "latimes") {
			$r["publication"] = "LA Times";
			$r["title"] = str_replace(" - Los Angeles Times", "", $r["title"]);
		} elseif ($url["domainname"] == "washingtonpost") {
			$r["publication"] = "Washington Post";
			//$r["title"] = str_replace("The Associated Press: ", "", $r["title"]);
		} elseif ($url["domainname"] == "reuters") {
			$r["publication"] = "Reuters";
			//$r["title"] = str_replace("The Associated Press: ", "", $r["title"]);
		} elseif (($url["domainname"] == "google") && ($url["subfolder"] == "afp")) {
			$r["publication"] = "AFP";
			$r["title"] = str_replace("Afp: ", "", $r["title"]);
		} elseif (($url["domainname"] == "google") && ($url["subfolder"] == "ap")) {
			$r["publication"] = "AP";
			$r["title"] = str_replace("The Associated Press: ", "", $r["title"]);
		}
	}
}

//to control return_to redirects.  i'm not sure how i should handle this generally.  it's a problem mainly when the page is included
if ($referrer && ($referrer["host"] == $request["host"])) $_josh["referrer"] = false;

$f = new form('press_clips', @$_GET['id'], $action);
if (!$included) $f->set_title_prefix($page['breadcrumbs']);
$f->set_field(array('name'=>'title', 'type'=>'text', 'label'=>getString('title')));
$f->set_field(array('name'=>'url', 'type'=>'text', 'label'=>getString('url')));
$f->set_field(array('name'=>'publication', 'type'=>'text', 'label'=>getString('publication')));
$f->set_field(array('name'=>'pub_date', 'type'=>'text', 'label'=>getString('published')));
$f->set_field(array('name'=>'description', 'type'=>'textarea', 'label'=>getString('description'), 'class'=>'mceEditor'));
$f->set_field(array('name'=>'type_id', 'label'=>getString('category'), 'type'=>'select', 'sql'=>'SELECT id, title' . langExt() . ' title FROM press_clips_types ORDER BY precedence', 'required'=>true));
if (getOption('channels')) $f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>getString('networks'), 'option_title'=>'title' . langExt(), 'options_table'=>'channels', 'linking_table'=>'press_clips_to_channels', 'object_id'=>'clip_id', 'option_id'=>'channel_id'));
echo $f->draw(@$r);

if (!$included) echo drawBottom();
?>