<?php
extract(joshlib());

function joshlib() {
	//look for joshlib at joshlib/index.php, ../joshlib/index.php, all the way down
	global $_josh;
	$count = substr_count($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'], '/');
	for ($i = 0; $i < $count; $i++) if (@include(str_repeat('../', $i) . 'joshlib/index.php')) return $_josh;
	die('Could not find Joshlib.');
}
echo url_header_utf8() . draw_meta_utf8();


//echo language_translate('hello world', 'en', 'ru');
debug();

echo language_translate('<p><span class="source">Computerworld -</span>&nbsp;Multi-core processors for tablets and smartphones&nbsp;<a href="http://www.computerworld.com/s/article/9223324/Quad_core_chips_boost_tablet_price_vs._performance_battle">are being touted</a>&nbsp;by chip maker Nvidia and others at the&nbsp;<a href="http://www.computerworld.com/s/article/9223141/CES_2012_What_you_need_to_know">CES</a>&nbsp;trade show, but some in the industry question their value.</p><p>Some of the latest mobile operating systems, such as&nbsp;<a href="http://www.computerworld.com/s/article/9220605/Hands_on_Windows_Phone_7_Mango_edition_adds_features_polish_">Windows Phone 7.5 (Mango)</a>, aren\'t designed to support dual-core processors, analysts noted. At the same time, they said, most&nbsp;<a href="http://www.computerworld.com/s/topic/75/Smartphones">smartphone</a>&nbsp;and&nbsp;<a href="http://www.computerworld.com/s/article/9221711/Latest_on_tablets">tablet</a>&nbsp;applications don\'t need and can\'t benefit from dual-core or quad-core processing power, except for some video and games.</p><p>Given that fact,&nbsp;<a href="http://www.computerworld.com/s/article/9137060/Microsoft_Update_Latest_news_features_reviews_opinions_and_more">Microsoft</a>&nbsp;and its partner Nokia practically dismissed dual-core smartphones that are running Android and are built by various makers, including Samsung and HTC.</p><p>To emphasize the point, Microsoft set up a challenge at CES where Windows Phone Evangelist Ben Randolph bet $100 that his Windows Phone, an HTC Titan, would operate faster than any other smartphone in running apps, searching the Web and other functions.</p>', 'en', 'fr');