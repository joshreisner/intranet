<?php
$language = 'en';
$ext = ($language == 'en') ? '' : '_' . $language;
$ext_name = ($language == 'ru') ? '_ru' : '';

if ($language == 'en') {
	$email = 'email';
	$featured_member = 'Featured Member';
} elseif ($language == 'es') {
	$email = 'correo electrónico';
	$featured_member = 'Destacados miembros';
} elseif ($language == 'fr') {
	$email = 'e-mail';
	$featured_member = 'Membre à la une';
} elseif ($language == 'ru') {
	$email = 'электронная почта';
	$featured_member = 'Избранные члены';
}

global $wpdb;

$wpdb->query('SET NAMES "UTF8"');

if ($feature = $wpdb->get_row('SELECT m.id, m.name' . $ext_name . ' name, m.description' . $ext . ' description FROM soc_members m WHERE m.is_selected = 1 AND m.is_active = 1 ORDER BY RAND() LIMIT 1')) {
	echo '<div class="feature"><span class="heading">' . $featured_member . '</span><span class="name">' . $feature->name . '</span><span class="description">' . $feature->description . '</span></div>';
}

echo '<select onchange="javascript:location.href=\'#\' + this.value;" style="font-size:12px; width:auto;"><option selected value=""></option>';

$countries = $wpdb->get_results('SELECT
		c.id,
		c.' . $language . ' country
	FROM jr_countries c
	WHERE (SELECT COUNT(*) FROM soc_members m WHERE m.country_id = c.id) > 0
	ORDER BY c.en');
foreach ($countries as $c) echo '<option value="' . $c->id . '">' . $c->country . '</option>';
echo '</select>';

$lastCountry = '';
$members = $wpdb->get_results('SELECT 
	m.id, 
	c.id country_id,
	c.' . $language . ' country,
	m.name' . $ext_name . ' name,
	m.email,
	m.web
	FROM soc_members m
	JOIN jr_countries c ON m.country_id = c.id
	WHERE m.is_active = 1
	ORDER BY c.' . $language . ', m.name' . $ext);
foreach ($members as $m) {
	if ($lastCountry != $m->country) {
		if (!empty($lastCountry)) echo '</ul><br>';
		echo '<h4><a name="' . $m->country_id . '"></a>' . $m->country . '</h4>';
		echo '</ul>';
		$lastCountry = $m->country;
	}
	echo '<li>' . $m->name . '<br>';
	if ($m->web) {
		echo '<a href="' . $m->web . '">' . $m->web . '</a><br>';
	} elseif ($m->email) {
		echo '<a href="mailto:' . $m->email . '">' . $m->email . '</a> <span class="req">[' . $email . ']</span><br>';
	}
	echo '<br></li>';
}
$wpdb->query('SET NAMES "Latin1"');

?>