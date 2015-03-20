<?php

/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 *  VERSION 1.0
 * Email: edsordaz@gmail.com
 *
 * $Id: functions_albums.php 5126 2011-07-12 Edson Ordaz $
 */

function comment_check($cid, $uid)
{
	global $db;
	$query = $db->simple_select("albumscomments", "*", "cid='".intval($cid)."'", array('limit' => 1));
	$comment_check = $db->fetch_field($query, 'uid');
	if($comment_check == intval($uid))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function resize_image($pid,$width_height)
{
	global $db;
	$query = $db->simple_select("albumsimages", "*", "pid=".intval($pid));
	$image = $db->fetch_array($query);
	list($max_width, $max_height) = explode("x", my_strtolower($width_height));
	$dimensions_width = $image['width'];
	$dimensions_height = $image['height'];
	if($dimensions_width && $dimensions_height)
	{
		if($dimensions_width > $max_width || $dimensions_height > $max_height)
		{
			require_once MYBB_ROOT."inc/functions_image.php";
			$scaled_dimensions = scale_image($dimensions_width, $dimensions_height, $max_width, $max_height);
			$album_width_height = "width=\"{$scaled_dimensions['width']}\" height=\"{$scaled_dimensions['height']}\"";
		}
		else
		{
			$album_width_height = "width=\"{$dimensions_width}\" height=\"{$dimensions_height}\"";	
		}
	}
	return $album_width_height;
}

function same_user($aid,$uid)
{
	global $db;
	$query = $db->simple_select("albums", "*", "aid='".intval($aid)."'", array('limit' => 1));
	$same = $db->fetch_field($query, 'uid');
	if($same == intval($uid))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function ulbum_exists($aid)
{
	global $db;
	$query = $db->simple_select("albums", "COUNT(*) as aid", "aid='".intval($aid)."'", array('limit' => 1));
	if($db->fetch_field($query, 'aid') == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function image_exists($pid)
{
	global $db;
	$query = $db->simple_select("albumsimages", "COUNT(*) as pid", "pid='".intval($pid)."'", array('limit' => 1));
	if($db->fetch_field($query, 'pid') == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function profile_username($uid)
{
	global $db;
	$query = $db->simple_select("users", "username", "uid='".intval($uid)."'", array('limit' => 1));
	$user = $db->fetch_field($query, 'username');
	return $user;
}
	
function albums_link($aid,$uid,$name)
{
	$link = "<a href=albums.php?aid={$aid}&uid={$uid}>{$name}</a>";
	return $link;
}

function image_show($picture,$pid,$aid,$width_height)
{
	$path = "<a href=albums.php?image={$pid}&album={$aid}>
	<img src=\"{$picture}\" ".resize_image($pid,$width_height)." />
	</a>";
	return $path;
	}

function image_album_link($aid,$uid,$picture,$width_height)
{
	global $db;
	$query = $db->simple_select("albums", "*", "aid=".intval($aid));
	$album = $db->fetch_array($query);
	list($max_width, $max_height) = explode("x", my_strtolower($width_height));
	$dimensions_width = $album['width'];
	$dimensions_height = $album['height'];
	if($dimensions_width && $dimensions_height)
	{
		if($dimensions_width > $max_width || $dimensions_height > $max_height)
		{
			require_once MYBB_ROOT."inc/functions_image.php";
			$scaled_dimensions = scale_image($dimensions_width, $dimensions_height, $max_width, $max_height);
			$album_width_height = "width=\"{$scaled_dimensions['width']}\" height=\"{$scaled_dimensions['height']}\"";
		}
		else
		{
			$album_width_height = "width=\"{$dimensions_width}\" height=\"{$dimensions_height}\"";	
		}
	}
	$path = "<img src={$picture} {$album_width_height}/>";
	$image = albums_link($aid,$uid,$path);
	return $image;
}

function add_album($album=array(), $uid=0)
{
	global $mybb,$db,$plugins,$lang;
	if(!$uid)
	{
		$uid = intval($mybb->user['uid']);
	}
	if(!$mybb->input['albumname'])
	{
		$return['error'] = $lang->errornotnameimg;
		return $return;
	}
	if(!$mybb->input['albumdescription'])
	{
		$return['error'] = $lang->errornotdesimg;
		return $return;
	}
	if($mybb->input['radioNewAlbum'] == "Url")
	{
		$return['imagebyurl'] = $mybb->input['albumpicture_url'];
		return $return;
	}
	if(!$album['name'] || !$album['tmp_name'])
	{
		$album = $_FILES['albumpicture'];
	}
	if($mybb->settings['profilealbums_uploadsimages'] == 2)
	{
		$return['error'] = $lang->erroruploadbypc;
		return $return;
	}
	if(!is_uploaded_file($album['tmp_name']))
	{
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	$ext = get_extension(my_strtolower($album['name']));
	if(!preg_match("#^(gif|jpg|jpeg|jpe|bmp|png)$#i", $ext)) 
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;
	}
	$albumpath = "uploads/profilealbums";
	$filename = "album_".$uid."_".date('d_m_y_g_i_s').".".$ext;
	$file = upload_album($album, $albumpath, $filename);
	if($file['error'])
	{
		@unlink(MYBB_ROOT.$albumpath."/".$filename);		
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	if(!file_exists($albumpath."/".$filename))
	{
		$return['error'] = $lang->notcargimgerror;
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		return $return;
	}
	$album['size'] = filesize($albumpath."/".$filename);
	if($album['size'] > ($mybb->settings['profilealbums_sizeimages']*1024) && $mybb->settings['profilealbums_sizeimages'] > 0)
	{
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		$lang->errorsizeimage = $lang->sprintf($lang->errorsizeimage, $mybb->settings['profilealbums_sizeimages']);
		$return['error'] = $lang->errorsizeimage;
		return $return;
	}
	$album_dimensions = @getimagesize($albumpath."/".$filename);
	if(!is_array($album_dimensions))
	{
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		$ret['error'] = $lang->errorsizeimg;
		return $ret;
	}
	switch(my_strtolower($album['type']))
	{
		case "image/gif":
			$img_type =  1;
			break;
		case "image/jpeg":
		case "image/x-jpg":
		case "image/x-jpeg":
		case "image/pjpeg":
		case "image/jpg":
			$img_type = 2;
			break;
		case "image/png":
		case "image/x-png":
			$img_type = 3;
			break;
		case "image/bmp":
			$img_type = 4;
			break;
		default:
			$img_type = 0;
	}
	if($img_type == 0)
	{
		$return['error'] = $lang->notextencionalbum;
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		return $return;		
	}
	
	$return = array(
		'uid'			 => $uid,
		'name'			 => $db->escape_string($mybb->input['albumname']),
		'image'			 => $db->escape_string($albumpath."/".$filename),
		'height'		 => $album_dimensions[1],
		'width' 		 => $album_dimensions[0],
		'description'	 => $db->escape_string($mybb->input['albumdescription'])
	);
	$plugins->run_hooks_by_ref("upload_albums_end", $return);
	return $return;
}

function add_image($image=array(), $uid=0)
{
	global $db,$lang,$mybb,$plugins;
	if(!$uid)
	{
		$uid = intval($mybb->user['uid']);
	}
	if(!$mybb->input['imagename'])
	{
		$return['error'] = $lang->errornotnameimg;
		return $return;
	}
	if(!$mybb->input['imagedescription'])
	{
		$return['error'] = $lang->errornotdesimg;
		return $return;
	}
	if($mybb->input['radioNewImage'] == "Url")
	{
		$return['imagebyurl'] = $mybb->input['imageurl'];
		return $return;
	}
	if(!$image['name'] || !$image['tmp_name'])
	{
		$image = $_FILES['imagepicture'];
	}
	if($mybb->settings['profilealbums_uploadsimages'] == 2)
	{
		$return['error'] = $lang->erroruploadbypc;
		return $return;
	}
	if(!is_uploaded_file($image['tmp_name']))
	{
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	$ext = get_extension(my_strtolower($image['name']));
	if(!preg_match("#^(gif|jpg|jpeg|jpe|bmp|png)$#i", $ext)) 
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;
	}
	$imagepath = "uploads/profilealbums";
	$filename = "image_".$uid."_".date('d_m_y_g_i_s').".".$ext;
	$file = upload_image($image, $imagepath, $filename);
	if($file['error'])
	{
		@unlink(MYBB_ROOT.$imagepath."/".$filename);		
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	if(!file_exists($imagepath."/".$filename))
	{
		$return['error'] = $lang->notcargimgerror;
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		return $return;
	}
	$image['size'] = filesize($imagepath."/".$filename);
	if($image['size'] > ($mybb->settings['profilealbums_sizeimages']*1024) && $mybb->settings['profilealbums_sizeimages'] > 0)
	{
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		$lang->errorsizeimage = $lang->sprintf($lang->errorsizeimage, $mybb->settings['profilealbums_sizeimages']);
		$return['error'] = $lang->errorsizeimage;
		return $return;
	}
	$image_dimensions = @getimagesize($imagepath."/".$filename);
	if(!is_array($image_dimensions))
	{
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		$ret['error'] = $lang->errorsizeimg;
		return $ret;
	}
	switch(my_strtolower($image['type']))
	{
		case "image/gif":
			$img_type =  1;
			break;
		case "image/jpeg":
		case "image/x-jpg":
		case "image/x-jpeg":
		case "image/pjpeg":
		case "image/jpg":
			$img_type = 2;
			break;
		case "image/png":
		case "image/x-png":
			$img_type = 3;
			break;
		case "image/bmp":
			$img_type = 4;
			break;
		default:
			$img_type = 0;
	}
	if($img_type == 0)
	{
		$return['error'] = $lang->notextencionalbum;
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		return $return;		
	}
	
	$return = array(
		'uid'			 => intval($uid),
		'aid'			 => intval($mybb->input['aid']),
		'image'	 		=>  $db->escape_string($imagepath."/".$filename),
		'height'		 => $image_dimensions[1],
		'width' 		 => $image_dimensions[0],
		'name' 			 => $db->escape_string($mybb->input['imagename']),
		'description'	 => $db->escape_string($mybb->input['imagedescription']),
		'date'	 => TIME_NOW
	);
	$plugins->run_hooks_by_ref("profilealbums_upload_image_end", $return);
	return $return;
}

function image_picture($pid,$image,$width_height)
{
	$image = "<img src=\"{$image}\" ".resize_image($pid,$width_height).">";
	return $image;
}

function delete_image($pid,$aid)
{
	global $db,$lang,$plugins;
	$lang->load("profilealbums", false, true);
	$aid = intval($aid);
	$pid = intval($pid);
	$query = $db->simple_select("albumsimages", "*", "aid=".$aid." AND pid=".$pid);
	$album = $db->fetch_array($query);
	$query_cids = $db->query("
		SELECT c.cid
		FROM ".TABLE_PREFIX."albumscomments c
		LEFT JOIN ".TABLE_PREFIX."albumsimages a ON (a.pid=c.pid)
		WHERE c.pid='".$pid."'
	");
	$cids = array();
	while($comments = $db->fetch_array($query_cids))
	{
		$cids[] = $comments['cid'];
	}
	if($cids)
	{
		$cids = implode(',', $cids);
		$db->delete_query("albumscomments", "cid IN ($cids)");
	}
	@unlink(MYBB_ROOT.$album['image']);
	$plugins->run_hooks("profilealbums_delete_image_end");
	$db->delete_query("albumsimages", "aid=".$aid." AND pid=".$pid);
	redirect("albums.php?aid={$aid}&uid=".$album['uid'],$lang->imgcomdeletesucc);
}

function delete_album($aid,$uid)
{
	global $db,$lang,$plugins;
	$lang->load("profilealbums", false, true);
	$aid = intval($aid);
	$query_image = $db->simple_select("albums", "*", "aid=".$aid);
	$albums = $db->fetch_array($query_image);
	$query_pids = $db->query("
		SELECT p.pid, p.image
		FROM ".TABLE_PREFIX."albumsimages p
		LEFT JOIN ".TABLE_PREFIX."albums a ON (a.aid=p.aid)
		WHERE p.aid='".$aid."'
	");
	$query_cids = $db->query("
		SELECT c.cid
		FROM ".TABLE_PREFIX."albumscomments c
		LEFT JOIN ".TABLE_PREFIX."albums a ON (a.aid=c.aid)
		WHERE c.aid='".$aid."'
	");
	$cids = array();
	$pids = array();
	$images = array();
	while($images_albums = $db->fetch_array($query_pids))
	{
		$pids[] = $images_albums['pid'];
		$images[] = @unlink(MYBB_ROOT.$images_albums['image']);
	}
	while($comments = $db->fetch_array($query_cids))
	{
		$cids[] = $comments['cid'];
	}
	if($pids)
	{
		$pids = implode(',', $pids);
		$db->delete_query("albumsimages", "pid IN ($pids)");
	}
	if($cids)
	{
		$cids = implode(',', $cids);
		$db->delete_query("albumscomments", "cid IN ($cids)");
	}
	@unlink(MYBB_ROOT.$albums[image]);
	$plugins->run_hooks("profilealbums_delete_album_end");
	$db->delete_query("albums", "aid=".$aid);
	redirect("member.php?action=albums&uid=".$uid,$lang->albimgdeletesucc);
}

function count_albums($uid)
{
	global $db,$lang;
	$lang->load("profilealbums", false, true);
	$query = $db->query("SELECT aid FROM ".TABLE_PREFIX."albums WHERE uid=".intval($uid));
    $count = $db->num_rows($query);
	if($count == 0)
	{
		error("<b><center>{$lang->notalbumscreateuser}</center></b>",$lang->errorprofilealbums);
	}
}

function count_images($aid,$uid)
{
	global $db,$mybb,$lang;
	$lang->load("profilealbums", false, true);
	$query = $db->query("SELECT pid FROM ".TABLE_PREFIX."albumsimages WHERE aid=".intval($aid));
    $count = $db->num_rows($query);
	if($count == 0)
	{
		if($mybb->user['uid'] == intval($uid))
		{
			return true;
		}
		else
		{
			error($lang->albumnotimage,$lang->errorprofilealbums);
		}
	}
}

function update_comment($comment=array(),$uid=0,$date=0)
{
	global $db,$mybb,$lang;
	$lang->load("profilealbums", false, true);
	if(!$uid)
	{
		$uid = intval($mybb->user['uid']);
	}
	if(!$date)
	{
		$date = TIME_NOW;
	}
	if(($uid != $mybb->user['uid']) || (empty($uid)))
	{
		error($lang->commentuplonotuserexist);
	}
	if(empty($mybb->input['message']))
	{
		$return['error'] = $lang->errorcommentempty;
		return $return;
	}
	if(empty($date))
	{
		$return['error'] = $lang->errorcommentdate;
		return $return;
	}
	$user = get_user($mybb->user['uid']);
	$query_image = $db->simple_select("albumsimages", "*", "aid='".intval($mybb->input['album'])."' AND pid=".intval($mybb->input['image']));
	$image = $db->fetch_array($query_image);
	$query_numcomments = $db->simple_select("users", "*", "uid=".$image['uid']);
	$q = $db->fetch_array($query_numcomments);
	$return['comment'] = array(
		'pid'		 => intval($mybb->input['image']),
		'aid'		 => intval($mybb->input['album']),
		'uid'	 	 => $uid,
		'text'		 => $db->escape_string($mybb->input['message']),
		'date' 		 => $date
	);
	$return['alert'] = array(
		'aid'		=> intval($mybb->input['album']),
		'pid'		=> intval($mybb->input['image']),
		'uid'		=> intval($image['uid']),
		'commid'	=> $uid,
		'username'	=> $user['username'],
		'image'		=> $db->escape_string($image['name']),
		'url'		=> "{$mybb->settings['bburl']}/albums.php?image=".intval($mybb->input['image'])."&album=".intval($mybb->input['album'])
	);
	$return['imgcomm'] = array(
		'imagecomment'	=> ++$q['imagecomment']
	);
	$return['img_uid'] = intval($image['uid']);
	return $return;
}

function style_message($message)
{
	global $mybb;
	require_once MYBB_ROOT."inc/class_parser.php";
	$parser = new postParser;
	$parser_options = array(
			'allow_html' => $mybb->settings['profilealbums_allow_html'],
			'allow_mycode' => $mybb->settings['profilealbums_allow_mycode'],
			'allow_smilies' => $mybb->settings['profilealbums_allow_smilies'],
			'allow_imgcode' => $mybb->settings['profilealbums_allow_imgcode'],
			'filter_badwords' => $mybb->settings['profilealbums_filter_badwords']
		);
	$message = $parser->parse_message($message, $parser_options);
	return $message;
}

function do_editcomment_send($comment=array())
{
	global $db,$lang,$mybb,$plugins;
	$lang->load("profilealbums", false, true);
	if(empty($mybb->input['comment_edit']))
	{
		$error['error'] = $lang->noteditcommentemtpy;
		return $error;
	}
	$return = array(
		'text'	 => $db->escape_string($mybb->input['comment_edit'])
	);
	return $return;
}

function delete_comment($cid,$aid,$pid)
{
	global $db,$lang;
	$lang->load("profilealbums", false, true);
	$db->query("DELETE FROM ".TABLE_PREFIX."albumscomments WHERE cid='".intval($cid)."'");
	redirect("albums.php?image={$pid}&album={$aid}",$lang->deletecommentsuccess);
}

function edit_album($album=array(), $uid=0)
{
	global $mybb,$db,$plugins,$lang;
	if(!$uid)
	{
		$uid = $mybb->user['uid'];
	}
	if(!$mybb->input['albumname'])
	{
		$return['error'] = $lang->errornotnameimg;
		return $return;
	}
	if(!$mybb->input['albumdescription'])
	{
		$return['error'] = $lang->errornotdesimg;
		return $return;
	}
	if(!$album['name'] || !$album['tmp_name'])
	{
		$album = $_FILES['editalbumfile'];
	}
	if($mybb->settings['profilealbums_uploadsimages'] == 2)
	{
		$return['error'] = $lang->erroruploadbypc;
		return $return;
	}
	if($mybb->input['radioEditAlbum'] == "Url" && !empty($mybb->input['albumpicture_url']))
	{
		$return['imagebyurl'] = $mybb->input['albumpicture_url'];
		return $return;
	}
	if(empty($album['name']) && $mybb->input['radioEditAlbum'] == "none")
	{
		$return['update_short'] = array(
			'name'			=> $db->escape_string($mybb->input['albumname']),
			'description'	=> $db->escape_string($mybb->input['albumdescription'])
		);
		return $return;
	}
	if(!is_uploaded_file($album['tmp_name']))
	{
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	$ext = get_extension(my_strtolower($album['name']));
	if(!preg_match("#^(gif|jpg|jpeg|jpe|bmp|png)$#i", $ext)) 
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;
	}
	$albumpath = "uploads/profilealbums";
	$filename = "album_".date('d_m_y_g_i_s')."_".$album['name'];
	$query = $db->simple_select("albums", "image", "aid=".intval($mybb->input['aid']));
	$imagedb = $db->fetch_array($query);
	@unlink(MYBB_ROOT.$imagedb['image']);
	$db->free_result($query);
	unset($imagedb);
	$file = upload_album($album, $albumpath, $filename);
	if($file['error'])
	{
		@unlink(MYBB_ROOT.$albumpath."/".$filename);		
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	if(!file_exists($albumpath."/".$filename))
	{
		$return['error'] = $lang->notcargimgerror;
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		return $return;
	}
	$album_dimensions = @getimagesize($albumpath."/".$filename);
	if(!is_array($album_dimensions))
	{
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		$ret['error'] = $lang->errorsizeimg;
		return $ret;
	}
	switch(my_strtolower($album['type']))
	{
		case "image/gif":
			$img_type =  1;
			break;
		case "image/jpeg":
		case "image/x-jpg":
		case "image/x-jpeg":
		case "image/pjpeg":
		case "image/jpg":
			$img_type = 2;
			break;
		case "image/png":
		case "image/x-png":
			$img_type = 3;
			break;
		case "image/bmp":
			$img_type = 4;
			break;
		default:
			$img_type = 0;
	}
	if($img_type == 0)
	{
		$return['error'] = $lang->notextencionalbum;
		@unlink(MYBB_ROOT.$albumpath."/".$filename);
		return $return;		
	}
	$return = array(
		'uid'			 => intval($mybb->input['uid']),
		'name'			 => $db->escape_string($mybb->input['albumname']),
		'image'			 => $db->escape_string($albumpath."/".$filename),
		'height'		 => $album_dimensions[1],
		'width' 		 => $album_dimensions[0],
		'description'	 => $db->escape_string($mybb->input['albumdescription'])
	);
	$plugins->run_hooks_by_ref("profilealbums_upload_editalbums_end", $return);
	return $return;
}

function edit_image($image=array(), $uid=0)
{
	global $db,$lang,$mybb,$plugins;
	if(!$uid)
	{
		$uid = $mybb->user['uid'];
	}
	if(!$mybb->input['imagename'])
	{
		$return['error'] = $lang->errornotnameimg;
		return $return;
	}
	if(!$mybb->input['imagedescription'])
	{
		$return['error'] = $lang->errornotdesimg;
		return $return;
	}
	if($mybb->input['radioEditImage'] == "Url")
	{
		$return['imagebyurl'] = $mybb->input['imageurl'];
		return $return;
	}
	if(!$image['name'] || !$image['tmp_name'])
	{
		$image = $_FILES['imagepicture'];
	}
	if($mybb->settings['profilealbums_uploadsimages'] == 2)
	{
		$return['error'] = $lang->erroruploadbypc;
		return $return;
	}
	if(empty($image['name']) && $mybb->input['radioEditImage'] == "none")
	{
		$return['update_short'] = array(
			'name'			=> $db->escape_string($mybb->input['imagename']),
			'description'	=> $db->escape_string($mybb->input['imagedescription']),
			'date'	=> TIME_NOW
		);
		return $return;
	}
	if(!is_uploaded_file($image['tmp_name']))
	{
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	$ext = get_extension(my_strtolower($image['name']));
	if(!preg_match("#^(gif|jpg|jpeg|jpe|bmp|png)$#i", $ext)) 
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;
	}
	$imagepath = "uploads/profilealbums";
	$filename = "image_".date('d_m_y_g_i_s')."_".$image['name'];
	$query = $db->simple_select("albumsimages", "*", "pid=".intval($mybb->input['pid']));
	$imagedb = $db->fetch_array($query);
	@unlink(MYBB_ROOT.$imagedb['image']);
	$db->free_result($query);
	unset($imagedb);
	$file = upload_image($image, $imagepath, $filename);
	if($file['error'])
	{
		@unlink(MYBB_ROOT.$imagepath."/".$filename);		
		$return['error'] = $lang->notcargimgerror;
		return $return;
	}
	if(!file_exists($imagepath."/".$filename))
	{
		$return['error'] = $lang->notcargimgerror;
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		return $return;
	}
	$image_dimensions = @getimagesize($imagepath."/".$filename);
	if(!is_array($image_dimensions))
	{
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		$ret['error'] = $lang->errorsizeimg;
		return $ret;
	}
	switch(my_strtolower($image['type']))
	{
		case "image/gif":
			$img_type =  1;
			break;
		case "image/jpeg":
		case "image/x-jpg":
		case "image/x-jpeg":
		case "image/pjpeg":
		case "image/jpg":
			$img_type = 2;
			break;
		case "image/png":
		case "image/x-png":
			$img_type = 3;
			break;
		case "image/bmp":
			$img_type = 4;
			break;
		default:
			$img_type = 0;
	}
	if($img_type == 0)
	{
		$return['error'] = $lang->notextencionalbum;
		@unlink(MYBB_ROOT.$imagepath."/".$filename);
		return $return;		
	}
	
	$return = array(
		'uid'			 => intval($uid),
		'aid'			 => intval($mybb->input['aid']),
		'image'			 => $db->escape_string($imagepath."/".$filename),
		'height'		 => $image_dimensions[1],
		'width' 		 => $image_dimensions[0],
		'name' 			 => $db->escape_string($mybb->input['imagename']),
		'description'	 => $db->escape_string($mybb->input['imagedescription']),
		'date'	 => TIME_NOW
	);
	$plugins->run_hooks_by_ref("profilealbums_upload_editimage_end", $return);
	return $return;
}

function upload_album($file, $albumpath, $filename="")
{
	global $plugins, $db;
	
	if(empty($file['name']) || $file['name'] == "none" || $file['size'] < 1)
	{
		$upload['error'] = 1;
		return $upload;
	}

	if(!$filename)
	{
		$filename = $file['name'];
	}
	
	$upload['original_filename'] = preg_replace("#/$#", "", $file['name']);
	$filename = preg_replace("#/$#", "", $filename); 
	$moved = @move_uploaded_file($file['tmp_name'], $albumpath."/".$filename);
	
	if(!$moved)
	{
		$upload['error'] = 2;
		return $upload;
	}
	@my_chmod($albumpath."/".$filename, '0644');
	$upload['filename'] = $db->escape_string($filename);
	$upload['path'] = $db->escape_string($albumpath);
	$upload['type'] = $file['type'];
	$upload['size'] = $file['size'];
	$plugins->run_hooks_by_ref("profilealbum_upload_album_end", $upload);
	return $upload;
}

function upload_image($file, $imagepath, $filename="")
{
	global $plugins, $db;
	
	if(empty($file['name']) || $file['name'] == "none" || $file['size'] < 1)
	{
		$upload['error'] = 1;
		return $upload;
	}

	if(!$filename)
	{
		$filename = $file['name'];
	}
	
	$upload['original_filename'] = preg_replace("#/$#", "", $file['name']);
	$filename = preg_replace("#/$#", "", $filename); 
	$moved = @move_uploaded_file($file['tmp_name'], $imagepath."/".$filename);
	
	if(!$moved)
	{
		$upload['error'] = 2;
		return $upload;
	}
	@my_chmod($imagepath."/".$filename, '0644');
	$upload['filename'] = $db->escape_string($filename);
	$upload['path'] = $db->escape_string($imagepath);
	$upload['type'] = $file['type'];
	$upload['size'] = $file['size'];
	$plugins->run_hooks_by_ref("profilealbum_upload_image_end", $upload);
	return $upload;
}

function add_url_album($album=array(), $uid=0)
{
	global $mybb,$db,$plugins,$lang;
	if(!$uid)
	{
		$uid = intval($mybb->user['uid']);
	}
	if(!$mybb->input['albumname'])
	{
		$return['error'] = $lang->errornotnameimg;
		return $return;
	}
	if(!$mybb->input['albumdescription'])
	{
		$return['error'] = $lang->errornotdesimg;
		return $return;
	}
	if($mybb->settings['profilealbums_uploadsimages'] == 1)
	{
		$return['error'] = $lang->erroruploadbyurl;
		return $return;
	}
	$album = @getimagesize($mybb->input['albumpicture_url']);
	$ext = get_extension(my_strtolower($mybb->input['albumpicture_url']));
	if(!preg_match("#^(gif|jpg|jpeg|jpe|bmp|png)$#i", $ext)) 
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;
	}
	$query = $db->simple_select("albums", "image", "aid=".intval($mybb->input['aid']));
	$imagedb = $db->fetch_array($query);
	@unlink(MYBB_ROOT.$imagedb['image']);
	$db->free_result($query);
	unset($imagedb);
	$albumpath = $mybb->input['albumpicture_url'];
	if(!is_array($album))
	{
		$ret['error'] = $lang->errorsizeimg;
		return $ret;
	}
	switch(my_strtolower($album['mime']))
	{
		case "image/gif":
			$img_type =  1;
			break;
		case "image/jpeg":
		case "image/x-jpg":
		case "image/x-jpeg":
		case "image/pjpeg":
		case "image/jpg":
			$img_type = 2;
			break;
		case "image/png":
		case "image/x-png":
			$img_type = 3;
			break;
		case "image/bmp":
			$img_type = 4;
			break;
		default:
			$img_type = 0;
	}
	if($img_type == 0)
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;		
	}
	list($width, $height)= @getimagesize($mybb->input['albumpicture_url']);
	$return = array(
		'uid'			 => $uid,
		'name'			 => $db->escape_string($mybb->input['albumname']),
		'image'			 => $db->escape_string($albumpath),
		'height'		 => intval($height),
		'width' 		 => intval($width),
		'description'	 => $db->escape_string($mybb->input['albumdescription'])
	);
	$plugins->run_hooks_by_ref("upload_albums_url_end", $return);
	return $return;
}

function add_url_image($image=array(), $uid=0)
{
	global $mybb,$db,$plugins,$lang;
	if(!$uid)
	{
		$uid = intval($mybb->user['uid']);
	}
	if(!$mybb->input['imagename'])
	{
		$return['error'] = $lang->errornotnameimg;
		return $return;
	}
	if(!$mybb->input['imagedescription'])
	{
		$return['error'] = $lang->errornotdesimg;
		return $return;
	}
	if($mybb->settings['profilealbums_uploadsimages'] == 1)
	{
		$return['error'] = $lang->erroruploadbyurl;
		return $return;
	}
	$image = @getimagesize($mybb->input['imageurl']);
	$ext = get_extension(my_strtolower($mybb->input['imageurl']));
	if(!preg_match("#^(gif|jpg|jpeg|jpe|bmp|png)$#i", $ext)) 
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;
	}
	$query = $db->simple_select("albumsimages", "*", "pid=".intval($mybb->input['pid']));
	$imagedb = $db->fetch_array($query);
	@unlink(MYBB_ROOT.$imagedb['image']);
	$db->free_result($query);
	unset($imagedb);
	$imagepath = $mybb->input['imageurl'];
	if(!is_array($image))
	{
		$ret['error'] = $lang->errorsizeimg;
		return $ret;
	}
	switch(my_strtolower($image['mime']))
	{
		case "image/gif":
			$img_type =  1;
			break;
		case "image/jpeg":
		case "image/x-jpg":
		case "image/x-jpeg":
		case "image/pjpeg":
		case "image/jpg":
			$img_type = 2;
			break;
		case "image/png":
		case "image/x-png":
			$img_type = 3;
			break;
		case "image/bmp":
			$img_type = 4;
			break;
		default:
			$img_type = 0;
	}
	if($img_type == 0)
	{
		$return['error'] = $lang->notextencionalbum;
		return $return;		
	}
	list($width, $height)= @getimagesize($mybb->input['imageurl']);
	$return = array(
		'uid'			 => $uid,
		'aid'			 => intval($mybb->input['aid']),
		'name'			 => $db->escape_string($mybb->input['imagename']),
		'image'			 => $db->escape_string($imagepath),
		'height'		 => intval($height),
		'width' 		 => intval($width),
		'description'	 => $db->escape_string($mybb->input['imagedescription']),
		'date'	 => TIME_NOW
	);
	$plugins->run_hooks_by_ref("upload_image_url_end", $return);
	return $return;
}
?>