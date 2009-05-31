<?
$clips = db_query("SELECT id, title FROM press_clips WHERE is_active = 1 ORDER BY pub_date DESC", 4);
while ($c = db_fetch($clips)) echo "<tr><td colspan='2'><a href='/press-clips/clip.php?id=" . $c["id"] . "'>" . format_string($c["title"], 40) . "</a></td></tr>";
?>