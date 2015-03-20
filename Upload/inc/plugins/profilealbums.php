<?php 

/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 *  VERSION 1.0
 * Email: edsordaz@gmail.com
 *
 * $Id: profilealbums.php 5126 2011-07-12 Edson Ordaz $
 */
 
$plugins->add_hook("usercp_start", "usercp_profilealbums");
$plugins->add_hook("member_profile_start", "profilealbums_albums");
$plugins->add_hook("global_start", "profilealbums_newcomments");
$plugins->add_hook("profilealbums_showimage_start", "profilealbums_newcomments_as_read");
$plugins->add_hook("build_friendly_wol_location_end", "profilealbums_whosonline");


function profilealbums_info()
{
	global $lang;
	$lang->load("config_profilealbums", false, true);
	return array(
		"name"			=> $lang->name,
		"description"	=> $lang->description,
		"website"		=> "http://mods.mybb.com/profile/25549/downloads",
		"author"		=> "Edson Ordaz",
		"authorsite"	=> "http://mods.mybb.com/profile/25549/downloads",
		"version"		=> "1.0",
		"guid" 			=> "866fa4c99089e707411f4b85c561836b",
		"compatibility" => "18*"
	);
}

function profilealbums_is_installed(){
	global $mybb, $db;
  	if($db->table_exists("albumsimages") && $db->table_exists("albums") && $db->table_exists("albumscomments") && $db->table_exists("albumsalert"))
	{
		return true;
	}
}

function profilealbums_install()
{
	global $db,$lang;
	$lang->load("config_profilealbums", false, true);
	if(!$db->table_exists("albumsimages"))
	{
		$db->query("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."albumsimages` (
		  `pid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `aid` varchar(120) NOT NULL DEFAULT '',
		  `uid` varchar(120) NOT NULL DEFAULT '',
		  `image` varchar(220) NOT NULL DEFAULT '',
		  `height` varchar(220) NOT NULL DEFAULT '',
		  `width` varchar(220) NOT NULL DEFAULT '',
		  `name` text NOT NULL DEFAULT '',
		  `description` text NOT NULL DEFAULT '',
		  `date` int(10) NOT NULL,
		  PRIMARY KEY (`pid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	if(!$db->table_exists("albums"))
	{
		$db->query("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."albums` (
		  `aid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `uid` varchar(120) NOT NULL DEFAULT '',
		  `name` varchar(220) NOT NULL DEFAULT '',
		  `image` varchar(220) NOT NULL DEFAULT '',
		  `height` varchar(220) NOT NULL DEFAULT '',
		  `width` varchar(220) NOT NULL DEFAULT '',
		  `description` text NOT NULL DEFAULT '',
		  `date` int(10) NOT NULL,
		  PRIMARY KEY (`aid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	if(!$db->table_exists("albumscomments"))
	{
		$db->query("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."albumscomments` (
		  `cid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `pid` varchar(120) NOT NULL DEFAULT '',
		  `aid` varchar(120) NOT NULL DEFAULT '',
		  `uid` varchar(120) NOT NULL DEFAULT '',
		  `text` text NOT NULL DEFAULT '',
		  `date` varchar(120) NOT NULL DEFAULT '',
		  PRIMARY KEY (`cid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	if(!$db->table_exists("albumsalert"))
	{
		$db->query("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."albumsalert` (
		  `alid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `aid` varchar(120) NOT NULL DEFAULT '',
		  `pid` varchar(120) NOT NULL DEFAULT '',
		  `uid` varchar(120) NOT NULL DEFAULT '',
		  `commid` varchar(120) NOT NULL DEFAULT '',
		  `username` varchar(120) NOT NULL DEFAULT '',
		  `image` varchar(120) NOT NULL DEFAULT '',
		  `url` text NOT NULL DEFAULT '',
		  PRIMARY KEY (`alid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	$profilealbums_groups = array(
		"gid"			=> "NULL",
		"name"			=> "profilealbums",
		"title" 		=> $lang->name,
		"description"	=> $lang->description,
		"disporder"		=> "0",
		"isdefault"		=> "no",
	);
	$db->insert_query("settinggroups", $profilealbums_groups);
	$gid = $db->insert_id();
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` ADD `imagecomment` INT(10) NOT NULL DEFAULT '0';");
	$profilealbums = array(
		array(
			"name"			=> "profilealbums_resize_image",
			"title"			=> $lang->sizemaximg,
			"description"	=> $lang->sizemaximg_des,
			"optionscode"	=> "text",
			"value"			=> "800x600",
			"disporder"		=> 1,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_resize_albums_images",
			"title"			=> $lang->sizemaximgalbums,
			"description"	=> $lang->sizemaximgalbums_des,
			"optionscode"	=> "text",
			"value"			=> "150x150",
			"disporder"		=> 2,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_resize_albums_profile",
			"title"			=> $lang->sizemaxalbpro,
			"description"	=> $lang->sizemaxalbpro_des,
			"optionscode"	=> "text",
			"value"			=> "80x80",
			"disporder"		=> 3,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_resize_images",
			"title"			=> $lang->sizemaximgdentro,
			"description"	=> $lang->sizemaximgdentro_des,
			"optionscode"	=> "text",
			"value"			=> "200x200",
			"disporder"		=> 4,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_pagination_profile",
			"title"			=> $lang->showalbumsperpage,
			"description"	=> $lang->showalbumsperpage_des,
			"optionscode"	=> "text",
			"value"			=> "8",
			"disporder"		=> 5,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_pagination_albums",
			"title"			=> $lang->showalbperpagedes,
			"description"	=> $lang->showalbperpagedes_des,
			"optionscode"	=> "text",
			"value"			=> "7",
			"disporder"		=> 6,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_pagination_images",
			"title"			=> $lang->showimgalperpag,
			"description"	=> $lang->showimgalperpag_des,
			"optionscode"	=> "text",
			"value"			=> "10",
			"disporder"		=> 7,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_pagination_comments",
			"title"			=> $lang->commentperpageimg,
			"description"	=> $lang->commentperpageimg_des,
			"optionscode"	=> "text",
			"value"			=> "10",
			"disporder"		=> 8,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_allow_html",
			"title"			=> $lang->canhtmlcomment,
			"description"	=> $lang->canhtmlcomment_des,
			"optionscode"	=> "yesno",
			"value"			=> "0",
			"disporder"		=> 9,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_allow_mycode",
			"title"			=> $lang->canbbcodecomments,
			"description"	=> $lang->canbbcodecomments_des,
			"optionscode"	=> "yesno",
			"value"			=> "1",
			"disporder"		=> 10,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_allow_smilies",
			"title"			=> $lang->cansmiliescomments,
			"description"	=> $lang->cansmiliescomments_des,
			"optionscode"	=> "yesno",
			"value"			=> "0",
			"disporder"		=> 11,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_allow_imgcode",
			"title"			=> $lang->canimgcomments,
			"description"	=> $lang->canimgcomments_des,
			"optionscode"	=> "yesno",
			"value"			=> "0",
			"disporder"		=> 12,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_filter_badwords",
			"title"			=> $lang->canbadwordcomments,
			"description"	=> $lang->canbadwordcomments_des,
			"optionscode"	=> "yesno",
			"value"			=> "1",
			"disporder"		=> 13,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_moderation_comments",
			"title"			=> $lang->groupsmodcomments,
			"description"	=> $lang->groupsmodcomments_des,
			"optionscode"	=> "text",
			"value"			=> "4,6",
			"disporder"		=> 14,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_sizeimages",
			"title"			=> $lang->sizeimagesmax,
			"description"	=> $lang->sizeimagesmax_des,
			"optionscode"	=> "text",
			"value"			=> "150",
			"disporder"		=> 15,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_uploadsimages",
			"title"			=> $lang->uploadsimages,
			"description"	=> $lang->uploadsimages_des,
			"optionscode"	=> "radio \n1=".$lang->uploadvaluebypc."\n2=".$lang->uploadvaluebyurl."\n3=".$lang->uploadvaluebytwooptions,
			"value"			=> "3",
			"disporder"		=> 16,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_show_bbcode_editor",
			"title"			=> $lang->showbbcodeeditor,
			"description"	=> $lang->showbbcodeeditor_des,
			"optionscode"	=> "yesno",
			"value"			=> "1",
			"disporder"		=> 17,
			"gid"			=> $gid,
		),
		array(
			"name"			=> "profilealbums_show_bbcode_editor_comments",
			"title"			=> $lang->showbbcodeeditorcomments,
			"description"	=> $lang->showbbcodeeditorcomments_des,
			"optionscode"	=> "yesno",
			"value"			=> "1",
			"disporder"		=> 18,
			"gid"			=> $gid,
		)
	);
	foreach($profilealbums as $pa_install)
	$db->insert_query("settings", $pa_install);
	rebuildsettings();
}

function profilealbums_activate()
{
	global $db,$mybb,$templates,$lang;
	$lang->load("config_profilealbums", false, true);

	$profilealbums_usercp = array(
		"title"		=> 'profilealbums_usercp',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->createalbums}</title>
{$headerinclude}
<script type="text/javascript" src="{$mybb->settings[\\\'bburl\\\']}/jscripts/ProfileAlbums.js"></script>
<script type="text/javascript">
	var radioprofilealbums = "usercp";
</script>
</head>
<body>
{$header}
<form method="post" enctype="multipart/form-data" action="usercp.php">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<table width="100%" border="0" align="center">
<tr>
{$usercpnav}
<td valign="top">
{$errors}
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->profile_albums}</strong></td>
</tr>
<tr>
<td class="tcat" colspan="2"><strong>{$lang->createalbums}</strong></td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->namealbum}:</strong></td>
<td class="trow1" width="50"><input type="text" size="50" name="albumname" value="{$mybb->input[\\\'albumname\\\']}" class="textbox"/></td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->descalbum}:</strong></td>
<td class="trow1" width="50"><textarea rows="10" cols="70" name="albumdescription" id="message" tabindex="2"/>{$mybb->input[\\\'albumdescription\\\']}</textarea>
{$codebuttons}</td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->uploadimg}</strong></td>
<td class="trow1" width="50">
<input type="radio" name="radioNewAlbum"  value="Pc" onclick="ProfileAlbums.PcNewAlbum();"  /> {$lang->bypc}
<input type="radio" name="radioNewAlbum"  value="Url" onclick="ProfileAlbums.UrlNewAlbum();" /> {$lang->byurl}
</td>
</tr>
<tr id="PcNewAlbum">
<td class="trow1" width="50"><strong>{$lang->imgalbum}:</strong></td>
<td class="trow1" width="50">
<input type="file" size="50" name="albumpicture" class="fileupload"/></td>
</tr>
<tr id="UrlNewAlbum">
<td class="trow1" width="50"><strong>{$lang->imgalbum}:</strong></td>
<td class="trow1" width="50">
<input type="text" size="50" name="albumpicture_url" class="textbox" /> {$lang->urlimgtextbox}</td>
</tr>
</table>
<br />
<div align="center">
<input type="hidden" name="action" value="do_albumscreate" />
<input type="submit" class="button" name="submit" value="{$lang->createalbum}" />
</div>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_album = array(
		"title"		=> 'profilealbums_album',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->albums}</title>
{$headerinclude}
</head>
<body>
{$header}
<div align="right">{$pagination}</div>
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->imagesalbum}</strong></td>
</tr>
{$albums_list}
</table>
<div align="right">{$pagination}</div>
{$manage_albums}
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_member_profile_table = array(
		"title"		=> 'profilealbums_member_profile_table',
		"template"	=> '<tr valign="top" align="left">
<td class="{$bgcolor}" width="10">{$name_album}</td>
<td class="{$bgcolor}" width="10">{$image_album}</td>
<td class="{$bgcolor}" width="80">{$description_album}</td>
</tr>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_member_profile = array(
		"title"		=> 'profilealbums_member_profile',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->profile_albums}</title>
{$headerinclude}
</head>
<body>
{$header}
<div align="right">{$pagination}</div>
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="3"><strong>{$lang->profile_albums}</strong></td>
</tr>
<tr>
<td class="tcat"><strong>{$lang->namealbum}</strong></td>
<td class="tcat"><strong>{$lang->imgalbum}</strong></td>
<td class="tcat"><strong>{$lang->descalbum}</strong></td>
</tr>
{$albums}
</table>
<div align="right">{$pagination}</div>
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_show_image = array(
		"title"		=> 'profilealbums_show_image',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->imgshow}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="3"><strong>{$imagename}</strong></td>
</tr>
<tr valign="top" align="center">
<td class="trow1">{$picture}
<br />
<div style="float: left">{$date} {$lang->at} {$time}</div>
</td>
</tr>
<tr valign="top">
<td class="trow2">
{$description}
</td>
</tr>
</table>
{$comments}
{$image_manage}
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_upload_image = array(
		"title"		=> 'profilealbums_upload_image',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->uploadimg}</title>
{$headerinclude}
<script type="text/javascript" src="{$mybb->settings[\\\'bburl\\\']}/jscripts/ProfileAlbums.js"></script>
<script type="text/javascript">
	var radioprofilealbums = "uploadimage";
</script>
</head>
<body>
{$header}
{$errors}	
<form method="post" enctype="multipart/form-data" action="albums.php?action=upload">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="aid" value="{$mybb->input[\\\'aid\\\']}" />
<input type="hidden" name="uid" value="{$mybb->input[\\\'uid\\\']}" />
<table width="100%" border="0" align="center">
<tr>
<td valign="top">
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->uploadimg}</strong></td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->nameimage}:</strong></td>
<td class="trow1" width="50"><input type="text" class="textbox" name="imagename" size="50" value="{$mybb->input[\\\'imagename\\\']}" /></td>
</tr>
<tr>
<td class="trow2" width="50"><strong>{$lang->descimage}:</strong></td>
<td class="trow2" width="50"><textarea rows="10" cols="70" name="imagedescription" id="message" tabindex="2"/>{$mybb->input[\\\'imagedescription\\\']}</textarea>
{$codebuttons}</td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->uploadimg}</strong></td>
<td class="trow1" width="50">
<input type="radio" name="radioNewImage"  value="Pc" onclick="ProfileAlbums.PcNewImage();"  /> {$lang->bypc}
<input type="radio" name="radioNewImage"  value="Url" onclick="ProfileAlbums.UrlNewImage();" /> {$lang->byurl}
</td>
</tr>
<tr id="PcNewimage">
<td class="trow2" width="50"><strong>{$lang->image}:</strong></td>
<td class="trow2" width="50"><input type="file" class="fileupload" size="50" name="imagepicture" /></td>
</tr>
<tr id="UrlNewimage">
<td class="trow2" width="50"><strong>{$lang->image}:</strong></td>
<td class="trow2" width="50">
<input type="text" size="50" name="imageurl" class="textbox" /> {$lang->urlimgtextbox}</td>
</tr>
<tr>
<td class="trow1" align="center" colspan="2">
<input type="submit" class="button" name="submit" value="{$lang->uploadimg}" />
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_album_list = array(
		"title"		=> 'profilealbums_album_list',
		"template"	=> '<tr valign="top" align="left">
<td class="trow1" width="10">
{$image_album}</td>
</tr>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_album_manage = array(
		"title"		=> 'profilealbums_album_manage',
		"template"	=> '<br /><br />
<table cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" style="background:#81A2C4;width:25%;margin: left;border: 1px solid #0F5C8E;">
<tr>
<td class="thead"><strong>{$lang->managealbum}</strong></td>
</tr>
<tr valign="top" align="left">
<td class="trow1">
<a href="albums.php?action=upload&aid={$mybb->input[\\\'aid\\\']}&uid={$mybb->input[\\\'uid\\\']}">
{$lang->uploadnewimage}</a>
</td>
</tr>
<tr>
<td class="trow2">
<a href="albums.php?action=editalbum&aid={$mybb->input[\\\'aid\\\']}&uid={$mybb->input[\\\'uid\\\']}">
{$lang->editalbum}</a>
</td>
</tr>
<tr>
<td class="trow1">
<a href="albums.php?action=delete&aid={$mybb->input[\\\'aid\\\']}&uid={$mybb->input[\\\'uid\\\']}" onclick="return confirm(\\\'{$lang->deleteimgpopup}\\\');">
{$lang->deletealbum}</a>
</td>
</tr>
</a>
</table>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_albums_all = array(
		"title"		=> 'profilealbums_albums_all',
		"template"	=> '<tr>
<td class="trow1">
<a href="member.php?action=albums&uid={$mybb->input[\\\'uid\\\']}">{$lang->showallalbumsdesc}</a>
</td>
</tr>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_image_manage = array(
		"title"		=> 'profilealbums_image_manage',
		"template"	=> '<br /><br />
<table cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" style="background:#81A2C4;width:25%;margin: left;border: 1px solid #0F5C8E;">
<tr>
<td class="thead"><strong>{$lang->manageimage}</strong></td>
</tr>
<tr valign="top" align="left">
<td class="trow1">
<a href="albums.php?action=deleteimage&image={$mybb->input[\\\'image\\\']}&album={$mybb->input[\\\'album\\\']}" onclick="return confirm(\\\'{$lang->deleteimagepopup}\\\');">
{$lang->deleteimage}</a>
</td>
</tr>
<tr valign="top" align="left">
<td class="trow2">
<a href="albums.php?action=editimage&image={$mybb->input[\\\'image\\\']}&album={$mybb->input[\\\'album\\\']}">
{$lang->editimage}</a>
</td>
</tr>
</a>
</table>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_profile = array(
		"title"		=> 'profilealbums_profile',
		"template"	=> '<br />
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->albumsto}</strong></td>
</tr>
<tr>
<td class="trow1">{$albums_list_profile}</td>
</tr>
{$albums_all}
</table>
{$pagination}',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_album_list_empty = array(
		"title"		=> 'profilealbums_album_list_empty',
		"template"	=> '<div align="center">
<font color="green" size="5">
<strong>{$lang->notalbumcreate}</strong>
</font>
</div>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_image_comments = array(
		"title"		=> 'profilealbums_image_comments',
		"template"	=> '<br /><br />
<div align="right" valign="top">{$pagination}</div>
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->commentsto}</strong></td>
</tr>
<tr valign="top">
{$imagecomments}
</tr>
<tr valign="top">
<td class="trow1" colspan="2">
<div align="right" valign="top">
{$pagination}
</div>
<br />
{$errors}
{$newcomment}</td>
</tr>
</table>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_image_newcomment = array(
		"title"		=> 'profilealbums_image_newcomment',
		"template"	=> '<center>
<form method="post" action="albums.php" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="image" value="{$mybb->input[\\\'image\\\']}" />
<input type="hidden" name="album" value="{$mybb->input[\\\'album\\\']}" />
<textarea name="message" id="message" rows="10" cols="90" tabindex="2">{$message}</textarea>
{$codebuttons}
<br />
<input type="hidden" name="action" value="do_sendcomment" />
<input type="submit" class="button" name="submit" value="{$lang->uploadcomment}" />
</center>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_image_newcomment_guest = array(
		"title"		=> 'profilealbums_image_newcomment_guest',
		"template"	=> '<style type="text/css">
.newsbar {
    background: #fff6bf;
    border-top: 2px solid #ffd324;
    border-bottom: 2px solid #ffd324;
    text-align: center;
    margin: 10px auto;
    padding: 5px 20px;
}
</style>
<p class="newsbar"><strong><br />{$lang->alertnewcommentguest}.<br /><br /></strong></p>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_list_commets = array(
		"title"		=> 'profilealbums_list_commets',
		"template"	=> '<td class={$style} rowspan="2" width="100" style="text-align: center; vertical-align: top;">
<img style="width: 90px;" src="{$user[\\\'avatar\\\']}" />
</td>
<td class="{$style}" >
{$username}<small style="font-size: 10px;"> ({$date} at {$time})</small>
<span style="font-size: 10px;">
{$formedit}
<br />
<a href="member.php?action=albums&uid={$user[\\\'uid\\\']}"><strong>{$lang->showallalbums}</strong></a>
</span>
</td>
</tr>
<tr>
<td class="{$style}" >
{$text}
</td></tr>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_alert_bar = array(
		"title"		=> 'profilealbums_alert_bar',
		"template"	=> '<div class="pm_alert" id="comment_notice">
<div>
{$lang->alertnewcommentinimage}
</div>
</div>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_form_manage = array(
		"title"		=> 'profilealbums_form_manage',
		"template"	=> '<br />
<a href="albums.php?action=editcomment&comment={$comments_image[\\\'cid\\\']}&image={$pid}&album={$aid}">
<strong>{$lang->edit}</a> - <a href=albums.php?action=deletecomment&comment={$comments_image[\\\'cid\\\']}&image={$pid}&album={$aid} onclick=\"return confirm(\\\'{$lang->deletecommentpopup}\\\');\">{$lang->delete}</a></strong>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_form_edit = array(
		"title"		=> 'profilealbums_form_edit',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->editcomment}</title>
{$headerinclude}
</head>
<body>
{$header}
{$errors}
<form method="post" action="albums.php?action=editcomment&comment={$mybb->input[\\\'comment\\\']}&image={$mybb->input[\\\'image\\\']}&album={$mybb->input[\\\'album\\\']}" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="aid" value="{$mybb->input[\\\'album\\\']}" />
<input type="hidden" name="pid" value="{$mybb->input[\\\'image\\\']}" />
<input type="hidden" name="id_comment" value="{$mybb->input[\\\'comment\\\']}" />
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->editcomment}</strong></td>
</tr>
<tr align="center">
<td class="trow1">
<textarea name="comment_edit" id="message" rows="10" cols="90" tabindex="2">{$comment_edit}</textarea>
{$codebuttons}
</td>
</tr>
<tr align="center">
<td class="trow2">
<input type="submit" class="button" name="submit" value="{$lang->editcomment}" />
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_album_edit = array(
		"title"		=> 'profilealbums_album_edit',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->editalbum}</title>
{$headerinclude}
<script type="text/javascript" src="{$mybb->settings[\\\'bburl\\\']}/jscripts/ProfileAlbums.js"></script>
<script type="text/javascript">
	var radioprofilealbums = "editalbum";
</script>
</head>
<body>
{$header}	
{$errors}
<form method="post" action="albums.php?action=editalbum" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="aid" value="{$mybb->input[\\\'aid\\\']}" />
<input type="hidden" name="uid" value="{$mybb->input[\\\'uid\\\']}" />
<table width="100%" border="0" align="center">
<tr>
<td valign="top">
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->editalbum}</strong></td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->namealbum}:</strong></td>
<td class="trow1" width="50"><input type="text" class="textbox" name="albumname" size="50" value="{$albumname}"/></td>
</tr>
<tr>
<td class="trow2" width="50"><strong>{$lang->descalbum}:</strong></td>
<td class="trow2" width="50"><textarea name="albumdescription" id="message" rows="10" cols="90" tabindex="2">{$albumdescription}</textarea>
{$codebuttons}
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->uploadimg}</strong></td>
<td class="trow1" width="50">
<input type="radio" name="radioEditAlbum" value="none" onclick="ProfileAlbums.NoneEditAlbum();" checked="checked" /> {$lang->noteditimage}
<input type="radio" name="radioEditAlbum" value="Pc" onclick="ProfileAlbums.PcEditAlbum();"  /> {$lang->bypc}
<input type="radio" name="radioEditAlbum" value="Url" onclick="ProfileAlbums.UrlEditAlbum();" /> {$lang->byurl} 
<br />
<em><font color="red" size="1"><strong>* {$lang->notchangeimg}</strong></font></em>
</td>
</tr>
<tr id="PcEditAlbum">
<td class="trow2" width="50"><strong>{$lang->imgalbum}:</strong></td>
<td class="trow2" width="50">
<input type="file" class="fileupload" size="50" name="editalbumfile" /></td>
</tr>
<tr id="UrlEditAlbum">
<td class="trow2" width="50"><strong>{$lang->imgalbum}:</strong></td>
<td class="trow2" width="50">
<input type="text" size="50" name="albumpicture_url" class="textbox" /> {$lang->urlimgtextbox}</td>
</tr>
<tr>
<td class="trow1" colspan="2" align="center">
<input type="submit" class="button" name="submit" value="{$lang->editalbum}" />
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$profilealbums_image_edit = array(
		"title"		=> 'profilealbums_image_edit',
		"template"	=> '<html>
<head>
<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->editimage}</title>
{$headerinclude}
<script type="text/javascript" src="{$mybb->settings[\\\'bburl\\\']}/jscripts/ProfileAlbums.js"></script>
<script type="text/javascript">
	var radioprofilealbums = "editimage";
</script>
</head>
<body>
{$header}	
{$errors}
<form method="post" action="albums.php?action=editimage&image={$mybb->input[\\\'image\\\']}&album={$mybb->input[\\\'album\\\']}" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="aid" value="{$mybb->input[\\\'album\\\']}" />
<input type="hidden" name="pid" value="{$mybb->input[\\\'image\\\']}" />
<table width="100%" border="0" align="center">
<tr>
<td valign="top">
<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->editimage}</strong></td>
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->nameimage}:</strong></td>
<td class="trow1" width="50"><input type="text" class="textbox" name="imagename" size="50" value="{$imagename}"/></td>
</tr>
<tr>
<td class="trow2" width="50"><strong>{$lang->descimage}:</strong></td>
<td class="trow2" width="50"><textarea name="imagedescription" id="message" rows="10" cols="90" tabindex="2">{$imagedescription}</textarea>
{$codebuttons}
</tr>
<tr>
<td class="trow1" width="50"><strong>{$lang->uploadimg}</strong></td>
<td class="trow1" width="50">
<input type="radio" name="radioEditImage" value="none" onclick="ProfileAlbums.NoneEditImage();" checked="checked" /> {$lang->noteditimage}
<input type="radio" name="radioEditImage"  value="Pc" onclick="ProfileAlbums.PcEditImage();" /> {$lang->bypc}
<input type="radio" name="radioEditImage"  value="Url" onclick="ProfileAlbums.UrlEditImage();" /> {$lang->byurl}
<br />
<em><font color="red" size="1"><strong>* {$lang->notchangeimg}</strong></font></em>
</td>
</tr>
<tr id="PcEditImage">
<td class="trow2" width="50"><strong>{$lang->image}:</strong></td>
<td class="trow2" width="50">
<input type="file" class="fileupload" size="50" name="imagepicture" /></td>
</tr>
<tr id="UrlEditImage">
<td class="trow2" width="50"><strong>{$lang->image}:</strong></td>
<td class="trow2" width="50">
<input type="text" size="50" name="imageurl" class="textbox" /> {$lang->urlimgtextbox}</td>
</tr>
<tr>
<td class="trow1" colspan="2" align="center">
<input type="submit" class="button" name="submit" value="{$lang->editimage}" />
</td>
</tr>
</table>
</div>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 1.0,
		"dateline"	=> time(),
	);
	$db->insert_query("templates", $profilealbums_album);
	$db->insert_query("templates", $profilealbums_albums_all);
	$db->insert_query("templates", $profilealbums_album_edit);
	$db->insert_query("templates", $profilealbums_album_list);
	$db->insert_query("templates", $profilealbums_album_list_empty);
	$db->insert_query("templates", $profilealbums_album_manage);
	$db->insert_query("templates", $profilealbums_alert_bar);
	$db->insert_query("templates", $profilealbums_form_edit);
	$db->insert_query("templates", $profilealbums_form_manage);
	$db->insert_query("templates", $profilealbums_image_comments);
	$db->insert_query("templates", $profilealbums_image_edit);
	$db->insert_query("templates", $profilealbums_image_manage);
	$db->insert_query("templates", $profilealbums_image_newcomment);
	$db->insert_query("templates", $profilealbums_image_newcomment_guest);
	$db->insert_query("templates", $profilealbums_list_commets);
	$db->insert_query("templates", $profilealbums_member_profile);
	$db->insert_query("templates", $profilealbums_member_profile_table);
	$db->insert_query("templates", $profilealbums_profile);
	$db->insert_query("templates", $profilealbums_show_image);
	$db->insert_query("templates", $profilealbums_upload_image);
	$db->insert_query("templates", $profilealbums_usercp);

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets('member_profile', '#{\$modoptions}#', '{\$modoptions}{$profile_albums}');
	find_replace_templatesets("usercp_nav_misc", '#</tbody>#', '<tr><td class="trow1 smalltext"><img src="images/icons/photo.gif">&nbsp;&nbsp;<a href="usercp.php?action=profilealbums">Albums</a></td></tr></tbody>');
	find_replace_templatesets('header', '#{\$unreadreports}#', '{$unreadreports}<!-- NewCommentInImage -->
			{$alertprofilealbums}<!-- /NewCommentInImage -->');
}

function profilealbums_uninstall()
{
	global $db;
	$deletealbums = array();
	$query_albums = $db->simple_select("albums", "image", "");
	while($albums = $db->fetch_array($query_albums))
	{
		$deletealbums[] = @unlink(MYBB_ROOT.$albums['image']);
	}
	$deleteimages = array();
	$query_images = $db->simple_select("albumsimages", "image", "");
	while($images = $db->fetch_array($query_images))
	{
		$deleteimages[] = @unlink(MYBB_ROOT.$images['image']);
	}
	$db->drop_table("albumsimages");
	$db->drop_table("albums");
	$db->drop_table("albumscomments");
	$db->drop_table("albumsalert");
	
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `imagecomment`;");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='profilealbums'");
	$db->delete_query("settings","name LIKE 'profilealbums_%'");
}
	
function profilealbums_deactivate()
{
	global $db, $mybb, $templates;
	$db->delete_query("templates","title = 'profilealbums_usercp'");	
	$db->delete_query("templates","title = 'profilealbums_album'");	
	$db->delete_query("templates","title = 'profilealbums_albums_all'");	
	$db->delete_query("templates","title = 'profilealbums_album_edit'");	
	$db->delete_query("templates","title = 'profilealbums_album_list'");	
	$db->delete_query("templates","title = 'profilealbums_album_list_empty'");	
	$db->delete_query("templates","title = 'profilealbums_album_manage'");	
	$db->delete_query("templates","title = 'profilealbums_alert_bar'");	
	$db->delete_query("templates","title = 'profilealbums_form_edit'");	
	$db->delete_query("templates","title = 'profilealbums_form_manage'");	
	$db->delete_query("templates","title = 'profilealbums_image_comments'");	
	$db->delete_query("templates","title = 'profilealbums_image_edit'");	
	$db->delete_query("templates","title = 'profilealbums_image_manage'");	
	$db->delete_query("templates","title = 'profilealbums_image_newcomment'");	
	$db->delete_query("templates","title = 'profilealbums_image_newcomment_guest'");	
	$db->delete_query("templates","title = 'profilealbums_list_commets'");	
	$db->delete_query("templates","title = 'profilealbums_member_profile_table'");	
	$db->delete_query("templates","title = 'profilealbums_member_profile'");	
	$db->delete_query("templates","title = 'profilealbums_profile'");	
	$db->delete_query("templates","title = 'profilealbums_show_image'");	
	$db->delete_query("templates","title = 'profilealbums_upload_image'");	
	require "../inc/adminfunctions_templates.php";
	find_replace_templatesets("member_profile", '#{\$profile_albums}#ism', "");
	find_replace_templatesets("usercp_nav_misc", "#".preg_quote('<tr><td class="trow1 smalltext"><img src="images/icons/photo.gif">&nbsp;&nbsp;<a href="usercp.php?action=profilealbums">Albums</a></td></tr>')."#i", '', 0);
	find_replace_templatesets('header', '#\<!--\sNewCommentInImage\s--\>(.+)\<!--\s/NewCommentInImage\s--\>#is', '', 0);
}


function usercp_profilealbums()
{
	global $mybb,$db,$templates,$theme,$lang,$headerinclude,$header,$footer,$usercpnav,$plugins;
	if($mybb->input['action'] != "profilealbums" && $mybb->input['action'] != "do_albumscreate")	{ return; }
	if($mybb->input['action'] == "do_albumscreate" && $mybb->request_method == "post")
	{
		verify_post_check($mybb->input['my_post_key']);
		require_once MYBB_ROOT."inc/functions_albums.php";
		$album = add_album();
		if($album['error'])
		{
			$errors = $album['error'];
		}
		if(!$errors && $album['imagebyurl'])
		{
			$album = add_url_album();
		}
		if($album['error'])
		{
			$errors = $album['error'];
		}
		if(!$errors)
		{
			$update_albums = array(
				'uid'			 => $album['uid'],
				'name'			 => $album['name'],
				'image'	 => $album['image'],
				'height'		 => $album['height'],
				'width' 		 => $album['width'],
				'description'	 => $album['description']
			);
			$aid = $db->insert_query("albums", $update_albums);
			$plugins->run_hooks("usercp_do_uploadalbum_end");
			redirect("usercp.php", $lang->albumscreatesuccess);
		}
	}
	if($errors)
	{
		$errors = inline_error($errors);
	}
	$lang->load("profilealbums", false, true);
	add_breadcrumb("profilealbums", "usercp.php");
	if($mybb->settings['profilealbums_show_bbcode_editor'] == 1)
	{
		$codebuttons = build_mycode_inserter();
	}
	eval("\$profilealbums = \"".$templates->get("profilealbums_usercp")."\";");
	output_page($profilealbums);
}

function profilealbums_albums()
{
	global $db,$mybb,$profile_albums,$theme,$templates,$lang;
	$lang->load("profilealbums", false, true);
	global $pagination,$profileuser;
	require_once MYBB_ROOT."inc/functions_albums.php";
	$query = $db->simple_select('albums', 'COUNT(aid) AS album', 'uid='.intval($mybb->input['uid']), array('limit' => 1));
    $quantity = $db->fetch_field($query, "album");
    $page = intval($mybb->input['page']);
    $perpage = $mybb->settings['profilealbums_pagination_profile'];
	
    if($page > 0)
	{
		$start = ($page - 1) * $perpage;
		$pages = $quantity / $perpage;
		$pages = ceil($pages);
		if($page > $pages || $page <= 0)
		{
			$start = 0;
			$page = 1;
		}
	}
	else
	{
		$start = 0;
		$page = 1;
	}
	$profile_page = 'member.php?action=profile&uid='.$mybb->input['uid'];
	$query = $db->query('SELECT * FROM '.TABLE_PREFIX.'albums WHERE uid='.intval($mybb->input['uid']).' ORDER BY aid DESC LIMIT ' . $start . ', ' . $perpage);
	while($albums = $db->fetch_array($query))
	{
		$albums_list_profile .= image_album_link($albums['aid'],$mybb->input['uid'],$albums['image'],$mybb->settings['profilealbums_resize_albums_profile'])." ";
	}
	$pagination = multipage($quantity,(int)$perpage,(int)$page,$profile_page);
	if(empty($albums_list_profile))
	{
		eval("\$albums_list_profile = \"".$templates->get("profilealbums_album_list_empty")."\";");
	}else{
		eval("\$albums_all = \"".$templates->get("profilealbums_albums_all")."\";");
	}
	$username_profile = profile_username($mybb->input['uid']);
	$lang->albumsto = $lang->sprintf($lang->albumsto, $username_profile);
	eval("\$profile_albums = \"".$templates->get("profilealbums_profile")."\";");
}

function profilealbums_newcomments()
{
	global $mybb, $db, $templates, $lang, $alertprofilealbums, $comment_inmenu;
	$lang->load("profilealbums", false, true);
	// Set New Comments var
	$num_comments = $mybb->user['imagecomment'];

	// If the user is logged in
	if($mybb->user['uid'] != 0 && $num_comments > 0)
	{	
		$query = $db->simple_select("albumsalert", "*", "uid=".$mybb->user['uid']);
		$alert = $db->fetch_array($query);
		$query_commid = $db->simple_select("users", "*", "uid=".$alert[commid]);
		$commid = $db->fetch_array($query_commid);
		$lang->alertnewcommentinimage = $lang->sprintf($lang->alertnewcommentinimage,$num_comments,$alert[commid],$commid[username],$alert[url],$alert[image]);
		eval("\$alertprofilealbums .= \"".$templates->get("profilealbums_alert_bar")."\";");
	}

	if(THIS_SCRIPT == 'member.php' && !$mybb->get_input('action'))
	{
		require_once MYBB_ROOT."inc/functions_albums.php";

		if($mybb->get_input('action') == "albums")
		{
			global $db,$lang;
			$lang->load("profilealbums", false, true);
			$plugins->run_hooks("member_albums_start");
			if((!$db->table_exists("albumsimages")) && (!$db->table_exists("albums")))
			{
				redirect("index.php",$lang->plugdeactivatepa);
			}
			if(!user_exists($mybb->input['uid']))
			{
				error($lang->notexistsusers);
			}
			count_albums($mybb->input['uid']);
			$plugins->run_hooks("member_albums_start");
			$query = $db->simple_select("users", "*", "uid='".intval($mybb->input['uid'])."'");
			$user = $db->fetch_array($query);
			$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
			add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$mybb->input['uid']);
			add_breadcrumb($lang->albums);
			
			$query = $db->simple_select('albums', 'COUNT(aid) AS albums', 'uid='.intval($mybb->input['uid']), array('limit' => 1));
			$quantity = $db->fetch_field($query, "albums");
			$page = intval($mybb->input['page']);
			$perpage = $mybb->settings['profilealbums_pagination_albums'];
			
			if($page > 0)
			{
				$start = ($page - 1) * $perpage;
				$pages = $quantity / $perpage;
				$pages = ceil($pages);
				if($page > $pages || $page <= 0)
				{
					$start = 0;
					$page = 1;
				}
			}
			else
			{
				$start = 0;
				$page = 1;
			}
			$profile_page = "member.php?action=albums&uid=".$mybb->input['uid'];
			$query = $db->query('SELECT * FROM '.TABLE_PREFIX.'albums WHERE uid='.intval($mybb->input['uid']).' ORDER BY aid DESC LIMIT ' . $start . ', ' . $perpage);
			while($album = $db->fetch_array($query))
			{
				$bgcolor = alt_trow();
				$name_album = albums_link($album['aid'],$album['uid'],$album['name']);
				$image_album = image_album_link($album['aid'],$album['uid'],$album['image'],$mybb->settings['profilealbums_resize_albums_images']);
				$description_album = style_message($album['description']);
				eval("\$albums .= \"".$templates->get("profilealbums_member_profile_table")."\";");
			}
			$pagination = multipage($quantity, (int)$perpage, (int)$page, $profile_page);
			$plugins->run_hooks("member_albums_end");
			eval("\$profilealbums = \"".$templates->get("profilealbums_member_profile")."\";");
			output_page($profilealbums);
		}
	}
}

function profilealbums_newcomments_as_read()
{
	global $mybb, $db,$lang;
	$lang->load("profilealbums", false, true);
	if(empty($mybb->user['uid']))
	{
		return false;
	}
	$pid = intval($mybb->input['image']);
	$aid = intval($mybb->input['album']);
	$query = $db->simple_select("albumsalert", "*", "uid=".$mybb->user['uid']);
	$alert = $db->fetch_array($query);
	if($alert['pid'] != $pid)
	{
		return FALSE;
	}
	if($alert['aid'] != $aid)
	{
		return FALSE;
	}
	if($mybb->user['imagecomment'])
	{
		$db->delete_query("albumsalert", "uid='{$mybb->user['uid']}' AND pid='{$pid}' AND aid='{$aid}'");
		$query_count = $db->query("SELECT alid FROM ".TABLE_PREFIX."albumsalert WHERE uid='".$mybb->user['uid']."'");
		$PAC = $db->num_rows($query_count);
		$update = array(
			'imagecomment' => $PAC
		);
		$query = $db->update_query('users', $update, 'uid = ' . $mybb->user['uid'], 1);
		$mybb->user['imagecomment'] = $PAC;
	}
}


function profilealbums_whosonline(&$plugin_array)
{
	global $lang;
	$lang->load("profilealbums", false, true);
	if(preg_match('/member\.php\?action\=albums\&amp\;uid/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = "<a href=\"{$plugin_array['user_activity']['location']}\">{$lang->locprofilalbums}</a>";
	}
	if(preg_match('/usercp\.php\?action\=profilealbums/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = "<a href=\"usercp.php?action=profilealbums\">{$lang->loccreatealbum}</a>";
	}
	if(preg_match('/albums\.php\?aid/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = "<a href=\"{$plugin_array['user_activity']['location']}\">{$lang->locprofilalbums}</a>";
	}
	if(preg_match('/albums\.php\?image/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = "<a href=\"{$plugin_array['user_activity']['location']}\">{$lang->locimagprofi}</a>";
	}
	if(preg_match('/albums\.php\?action\=upload/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = "{$lang->locuploadimg}";
	}
	if(preg_match('/albums\.php\?action\=editalbum/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = $lang->loceditalbums;
	}
	if(preg_match('/albums\.php\?action\=editimage/',$plugin_array['user_activity']['location']))
	{
		$plugin_array['location_name'] = $lang->loceditimageprof;
	}
	return $plugin_array;
}
?>