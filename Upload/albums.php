<?php

/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 *  VERSION 1.0
 * Email: edsordaz@gmail.com
 *
 * $Id: albums.php 5126 2011-07-12 Edson Ordaz $
 */
 
 
define("IN_MYBB", 1);
define("KILL_GLOBALS", 1);
require_once "./global.php";
require_once MYBB_ROOT."inc/functions_albums.php";
require_once MYBB_ROOT."inc/functions_user.php";
$lang->load("profilealbums");

if($mybb->input['action'] == "editimage")
{
	if($mybb->request_method == "post")
	{
		verify_post_check($mybb->input['my_post_key']);
		$plugins->run_hooks("profilealbums_do_edit_image");
		$image = edit_image();
		if($image['error'])
		{
			$errors = $image['error'];
		}
		if(!$errors && $image['imagebyurl'])
		{
			$image = add_url_image();
		}
		if($image['error'])
		{
			$errors = $image['error'];
		}
		if($image['update_short'] && !$image['error'])
		{
			$edit_update_image = array(
				'name'		 => $image['update_short']['name'],
				'description'=> $image['update_short']['description'],
				'date'=> $image['update_short']['date']
			);
			$plugins->run_hooks("profilealbums_do_edit_image_upload",$edit_update_image);
			$db->update_query("albumsimages", $edit_update_image,"pid='".intval($mybb->input['pid'])."'");
			redirect("albums.php?image=".intval($mybb->input['pid'])."&album=".intval($mybb->input['aid']),$lang->editimagesuccess);
		}
		if(!$image['error'])
		{
			$update_image_edit = array(
				'uid'			 => $image['uid'],
				'aid'			 => $image['aid'],
				'image'			 => $image['image'],
				'height'		 => $image['height'],
				'width' 		 => $image['width'],
				'name' 			 => $image['name'],
				'description'	 => $image['description'],
				'date'	 => TIME_NOW
			);
			$db->update_query("albumsimages", $update_image_edit,"pid=".intval($mybb->input['pid']));
			redirect("albums.php?image=".intval($mybb->input['pid'])."&album=".intval($mybb->input['aid']),$lang->editimagesuccess);
		}
	}
	$plugins->run_hooks("profilealbums_edit_image_start");
	$pid = intval($mybb->input['image']);
	$aid = intval($mybb->input['album']);
	if(!image_exists($pid))
	{
		error_no_permission();
	}
	if(!ulbum_exists($aid))
	{
		error_no_permission();
	}
	if(!same_user($aid,$mybb->user['uid']))
	{	
		error_no_permission();
	}
	if($errors)
	{
		$errors = inline_error($errors);
	}
	$query_add_breadcrumb = $db->simple_select("albums", "*", "aid='".$aid."'");
	$album_add_breadcrumb = $db->fetch_array($query_add_breadcrumb);
	$query_add = $db->simple_select("users", "*", "uid='".$album_add_breadcrumb['uid']."'");
	$user = $db->fetch_array($query_add);	
	$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
	add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->albums,"member.php?action=albums&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->album." ".$album_add_breadcrumb['name'],"albums.php?aid={$mybb->input['album']}&uid={$album_add_breadcrumb['uid']}");
	$query = $db->simple_select("albumsimages", "*", "pid='{$pid}' AND aid='{$aid}'");
	$image = $db->fetch_array($query);
	add_breadcrumb($image['name'],"albums.php?image={$pid}&album=".$aid);
	add_breadcrumb($lang->editimage);
	$imagename = $image['name'];
	$imagedescription = $image['description'];
	$plugins->run_hooks("profilealbums_edit_image_end");
	if($mybb->settings['profilealbums_show_bbcode_editor'] == 1)
	{
		$codebuttons = build_mycode_inserter();
	}
	eval("\$profileimage_editalbum = \"".$templates->get("profilealbums_image_edit")."\";");
	output_page($profileimage_editalbum);
	return FALSE;
}
if($mybb->input['action'] == "deleteimage")
{
	$pid = intval($mybb->input['image']);
	$aid = intval($mybb->input['album']);
	$plugins->run_hooks("profilealbums_delete_image_start");
	if(!image_exists($pid))
	{
		error_no_permission();
	}
	if(!ulbum_exists($aid))
	{
		error_no_permission();
	}
	if(!same_user($aid,$mybb->user['uid']))
	{	
		error_no_permission();
	}
	delete_image($pid,$aid);
	return false;
}
if($mybb->input['action'] == "delete")
{
	$aid = intval($mybb->input['aid']);
	$uid = intval($mybb->input['uid']);
	$plugins->run_hooks("profilealbums_delete_album_start");
	if(!ulbum_exists($aid))
	{
		error_no_permission();
	}
	if(!same_user($aid,$mybb->user['uid']))
	{	
		error_no_permission();
	}
	delete_album($aid,$uid);
	return FALSE;
}
if($mybb->input['action'] == "editalbum")
{
	if($mybb->request_method == "post")
	{
		$plugins->run_hooks("profilealbums_do_edit_album");
		$aid = intval($mybb->input['aid']);
		$uid = intval($mybb->input['uid']);
		if(!same_user($aid,$uid))
		{
			error($lang->noteditalbumautor,$lang->errorprofilealbums);
		}
		$album = edit_album();
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
		if($album['update_short'] && !$album['error'])
		{
			$update = array(
				'name'		 => $album['update_short']['name'],
				'description'=> $album['update_short']['description']
			);
			$db->update_query("albums", $update,"aid='".intval($aid)."'");
			redirect("albums.php?aid={$aid}&uid={$uid}",$lang->editalbumsuccess);
		}
		if(!$album['error'])
		{
			$update_edit_album = array(
				'uid'			 => $album['uid'],
				'name'			 => $album['name'],
				'image'	 => $album['image'],
				'height'		 => $album['height'],
				'width' 		 => $album['width'],
				'description'	 => $album['description']
			);
			$db->update_query("albums", $update_edit_album,"aid='".$aid."'");
			redirect("albums.php?aid={$aid}&uid={$uid}",$lang->editalbumsuccess);
		}
	}
	$plugins->run_hooks("profilealbums_edit_album_start");
	if($errors)
	{
		$errors = inline_error($errors);
	}
	$aid = intval($mybb->input['aid']);
	$uid = intval($mybb->input['uid']);
	$query_add_breadcrumb = $db->simple_select("albums", "*", "aid='".$aid."'");
	$album_add_breadcrumb = $db->fetch_array($query_add_breadcrumb);
	$query_add = $db->simple_select("users", "*", "uid='".$album_add_breadcrumb['uid']."'");
	$user = $db->fetch_array($query_add);
	$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
	$lang->albumsto = $lang->sprintf($lang->albumsto, $user['username']);
	add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->albumsto,"member.php?action=albums&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->album." ".$album_add_breadcrumb['name'],"albums.php?aid={$aid}&uid={$album_add_breadcrumb['uid']}");
	add_breadcrumb($lang->editalbum);
	if(!ulbum_exists($aid))
	{
		error_no_permission();
	}
	if(!same_user($aid,$mybb->user['uid']))
	{	
		error_no_permission();
	}
	if($mybb->settings['profilealbums_show_bbcode_editor'] == 1)
	{
		$codebuttons = build_mycode_inserter();
	}
	$albumname = $album_add_breadcrumb['name'];
	$albumdescription = $album_add_breadcrumb['description'];
	eval("\$profilealbums_editalbum = \"".$templates->get("profilealbums_album_edit")."\";");
	output_page($profilealbums_editalbum);
	return FALSE;
}
if($mybb->input['action'] == "editcomment")
{
	if($mybb->request_method == "post")
	{
		$plugins->run_hooks("profilealbums_do_edit_comment");
		if(!image_exists($mybb->input['pid']))
		{
			error_no_permission();
		}
		if(!ulbum_exists($mybb->input['aid']))
		{
			error_no_permission();
		}
		if(!same_user($mybb->input['aid'],$mybb->user['uid']))
		{	
			error_no_permission();
		}
		$comment = do_editcomment_send();
		if($comment['error'])
		{
			$errors = $comment['error'];
		}
		if(!$errors)
		{
			$update_edit_comment = array(
				'text'	=> $comment['text']
			);
			$plugins->run_hooks("profilealbums_do_edit_comment_update", $update_edit_comment);
			$db->update_query("albumscomments", $update_edit_comment,"cid='".intval($mybb->input['id_comment'])."'");
			redirect("albums.php?image=".intval($mybb->input['pid'])."&album=".intval($mybb->input['aid']),$lang->editcommentsuccess);
		}
	}
	$plugins->run_hooks("profilealbums_edit_comment_start");
	$pid = intval($mybb->input['image']);
	$aid = intval($mybb->input['album']);
	$cid = intval($mybb->input['comment']);
	if(!in_array($mybb->user['usergroup'],explode(",",$mybb->settings['profilealbums_moderation_comments'])))
	{	
		error_no_permission();
	}
	if($errors)
	{
		$errors = inline_error($errors);
	}
	$query_add_breadcrumb = $db->simple_select("albums", "*", "aid='".$aid."'");
	$album_add_breadcrumb = $db->fetch_array($query_add_breadcrumb);
	$query_add = $db->simple_select("users", "*", "uid='".$album_add_breadcrumb['uid']."'");
	$user = $db->fetch_array($query_add);
	$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
	add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->albums,"member.php?action=albums&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->album." ".$album_add_breadcrumb['name'],"albums.php?aid={$aid}&uid={$album_add_breadcrumb['uid']}");
	add_breadcrumb($lang->image,"albums.php?image={$pid}&album={$aid}");
	add_breadcrumb($lang->editcomment);
	$codebuttons = build_mycode_inserter();
	$query_comment = $db->simple_select("albumscomments", "*", "cid='".$cid."'");
	$comment = $db->fetch_array($query_comment);
	$comment_edit = $comment['text'];
	$plugins->run_hooks("profilealbums_edit_comment_end");
	eval("\$profilealbums_editcomment = \"".$templates->get("profilealbums_form_edit")."\";");
	output_page($profilealbums_editcomment);
	return false;
}
if($mybb->input['action'] == "deletecomment")
{
	if(!image_exists($mybb->input['image']))
	{
		error_no_permission();
	}
	if(!ulbum_exists($mybb->input['album']))
	{
		error_no_permission();
	}
	if(!in_array($mybb->user['usergroup'],explode(",",$mybb->settings['profilealbums_moderation_comments'])))
	{	
		error_no_permission();
	}
	$plugins->run_hooks("profilealbums_delete_comment_start");
	delete_comment($mybb->input['comment'],$mybb->input['album'],$mybb->input['image']);
	return false;
}
if($mybb->input['action'] == "upload")
{
	if($mybb->request_method == "post")
	{
		$plugins->run_hooks("profilealbums_do_upload_image");
		verify_post_check($mybb->input['my_post_key']);
		$image = add_image();
		if($image['error'])
		{
			$errors = $image['error'];
		}
		if(!$errors && $image['imagebyurl'])
		{
			$image = add_url_image();
		}
		if($image['error'])
		{
			$errors = $image['error'];
		}
		if(!$errors)
		{
			$update_image = array(
				'uid'			 => $image['uid'],
				'aid'			 => $image['aid'],
				'image'	 => $image['image'],
				'height'		 => $image['height'],
				'width' 		 => $image['width'],
				'name' 			 => $image['name'],
				'description'	 => $image['description'],
				'date'	 => $image['date']
			);
			$pid = $db->insert_query("albumsimages", $update_image);
			redirect("albums.php?aid={$image['aid']}&uid={$image['uid']}", $lang->imaguploadsuccess);
		}
	}
	$plugins->run_hooks("profilealbums_upload_image_start");
	if($errors)
	{
		$errors = inline_error($errors);
	}
	$aid = intval($mybb->input['aid']);
	$query_add_breadcrumb = $db->simple_select("albums", "*", "aid='".$aid."'");
	$album_add_breadcrumb = $db->fetch_array($query_add_breadcrumb);
	$user = get_user($album_add_breadcrumb['uid']);
	$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
	add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->albums,"member.php?action=albums&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->album." ".$album_add_breadcrumb['name'],"albums.php?aid={$aid}&uid={$album_add_breadcrumb['uid']}");
	$lang->uploadimageto = $lang->sprintf($lang->uploadimageto, $album_add_breadcrumb['name']);
	add_breadcrumb($lang->uploadimageto);
	if($album_add_breadcrumb['uid'] != intval($mybb->input['uid']))
	{
		error_no_permission();
	}
	if(!same_user($aid,$mybb->user['uid']))
	{	
		error_no_permission();
	}
	if($mybb->settings['profilealbums_show_bbcode_editor'] == 1)
	{
		$codebuttons = build_mycode_inserter();
	}
	eval("\$profilealbums_upload_image = \"".$templates->get("profilealbums_upload_image")."\";");
	output_page($profilealbums_upload_image);
	return FALSE;
}
if($mybb->input['image'])
{
	if($mybb->request_method == "post")
	{
		verify_post_check($mybb->input['my_post_key']);
		if(!user_exists($mybb->user['uid']))
		{
			error($lang->notsendcomment,$lang->errorcreatecomment);
		}
		if(!image_exists($mybb->input['image']))
		{
			error($lang->notcommentnexiimg,$lang->errorcreatecomment);
		}
		if($mybb->user['uid'] == 0)
		{
			error($lang->notsendcomment,$lang->errorcreatecomment);
		}
		$plugins->run_hooks("profilealbums_do_send_comment");
		$comment = update_comment();
		if($comment['error'])
		{
			$errors = $comment['error'];
		}
		if(!$errors)
		{
			$update_new_comment = array(
				'pid'		 => $comment['comment']['pid'],
				'aid'		 => $comment['comment']['aid'],
				'uid'	 	 => $comment['comment']['uid'],
				'text'		 => $comment['comment']['text'],
				'date' 		 => $comment['comment']['date']
			);
			$plugins->run_hooks("profilealbums_do_update_comment", $update_new_comment);
			$update_alert = array(
				'aid'		=> $comment['alert']['aid'],
				'pid'		=> $comment['alert']['pid'],
				'uid'		=> $comment['alert']['uid'],
				'commid'	=> $comment['alert']['commid'],
				'username'	=> $comment['alert']['username'],
				'image'		=> $comment['alert']['image'],
				'url'		=> $comment['alert']['url']
			);
			$plugins->run_hooks("profilealbums_do_updatealert_comment", $update_alert);
			if($comment['img_uid'] != $mybb->user['uid'])
			{
				$update_user = array(
					'imagecomment'	=> $comment['imgcomm']['imagecomment']
				);
				$plugins->run_hooks("profilealbums_do_updateuser_comment", $update_user);
				$update_table = $db->update_query('users', $update_user,'uid ='.$comment['img_uid']);
			}
			$cid = $db->insert_query("albumscomments", $update_new_comment);
			$cid1 = $db->insert_query("albumsalert", $update_alert);
			redirect("albums.php?image={$mybb->input['image']}&album={$mybb->input['album']}",$lang->commentuploadsuccess);
		}
	}
	$plugins->run_hooks("profilealbums_showimage_start");
	$pid = intval($mybb->input['image']);
	$aid = intval($mybb->input['album']);
	if($errors)
	{
		$errors = inline_error($errors);
	}
	if(!image_exists($pid))
	{
		error($lang->imagenotexisterror,$lang->errorprofilealbums);
	}
	if(!ulbum_exists($aid))
	{
		error($lang->albumnotexisterror,$lang->errorprofilealbums);
	}
	$query_add_breadcrumb = $db->simple_select("albums", "*", "aid='".$aid."'");
	$album_add_breadcrumb = $db->fetch_array($query_add_breadcrumb);
	$query_add = $db->simple_select("users", "*", "uid='".$album_add_breadcrumb['uid']."'");
	$user = $db->fetch_array($query_add);	
	$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
	add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->albums,"member.php?action=albums&uid=".$album_add_breadcrumb['uid']);
	add_breadcrumb($lang->album." ".$album_add_breadcrumb['name'],"albums.php?aid={$aid}&uid={$album_add_breadcrumb['uid']}");
	$query = $db->simple_select("albumsimages", "*", "pid='{$pid}' AND aid='{$aid}'");
	$image = $db->fetch_array($query);
	add_breadcrumb($image['name']);
	if($mybb->settings['profilealbums_show_bbcode_editor_comments'] == 1)
	{
		$codebuttons = build_mycode_inserter();
	}
	$query = $db->simple_select('albumscomments', 'COUNT(cid) AS comments', 'aid='.$aid.' AND pid='.$pid, array('limit' => 1));
	$quantity = $db->fetch_field($query, "comments");
    $page = intval($mybb->input['page']);
    $perpage = $mybb->settings['profilealbums_pagination_comments'];
	
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
	$profile_page = "albums.php?image={$pid}&album={$aid}";
	$query_comments = $db->query('SELECT * FROM ' . TABLE_PREFIX . 'albumscomments WHERE aid='.$aid.' AND pid='.$pid.' ORDER BY date DESC LIMIT ' . $start . ', ' . $perpage);
	$pagination = multipage($quantity, (int)$perpage, (int)$page, $profile_page);
	while($comments_image = $db->fetch_array($query_comments))
	{
		$query_user = $db->simple_select("users", "*", "uid=".$comments_image['uid']);
		while($user = $db->fetch_array($query_user))
		{
			global $theme,$mybb;
			$style = "trow1";
			$style = alt_trow();
			$username_format = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
			$username = build_profile_link($username_format, $user['uid'],"_blank");
			$text = style_message($comments_image[text]);
			if(in_array($mybb->user['usergroup'],explode(",",$mybb->settings['profilealbums_moderation_comments'])))
			{
				eval("\$formedit = \"".$templates->get("profilealbums_form_manage")."\";");
			}else{
				$formedit = "";
			}
			$date = my_date($mybb->settings['dateformat'], $comments_image['date']);
			$time = my_date($mybb->settings['timeformat'], $comments_image['date']);
            $user['avatar'] = (!empty($user['avatar'])) ? $user['avatar'] : "images/avatars/invalid_url.gif";
			eval("\$imagecomments .= \"".$templates->get("profilealbums_list_commets")."\";");
		}
	}
	$query_sameuser = $db->simple_select("albums", "*", "aid='".intval($aid)."'", array('limit' => 1));
	$sameuser_user = $db->fetch_field($query_sameuser, 'uid');
	if($sameuser_user != $mybb->user[uid])
	{
		$image_manage = "";
	}elseif($mybb->user['uid'] == 0){
		$image_manage = "";
	}else{
		eval("\$image_manage = \"".$templates->get("profilealbums_image_manage")."\";");
	}
	if($mybb->user['uid'] != 0)
	{
		eval("\$newcomment = \"".$templates->get("profilealbums_image_newcomment")."\";");
	}else{
		eval("\$newcomment = \"".$templates->get("profilealbums_image_newcomment_guest")."\";");
	}
	$imagename = $image['name'];
	$lang->commentsto = $lang->sprintf($lang->commentsto, $imagename);
	$picture = image_picture($pid,$image[image],$mybb->settings['profilealbums_resize_image']);
	$description = style_message($image['description']);
	$date = my_date($mybb->settings['dateformat'], $image['date']);
	$time = my_date($mybb->settings['timeformat'], $image['date']);
	$plugins->run_hooks("profilealbums_showimage_end");
	eval("\$comments = \"".$templates->get("profilealbums_image_comments")."\";");
	eval("\$profilealbums_show_image = \"".$templates->get("profilealbums_show_image")."\";");
	output_page($profilealbums_show_image);
	return FALSE;
}
if($mybb->input['aid'])
{
	$aid = intval($mybb->input['aid']);
	$uid = intval($mybb->input['uid']);
	if(!user_exists($uid))
	{
		error($lang->usernotexisteview,$lang->errorprofilealbums);
	}
	if(!ulbum_exists($aid))
	{
		error($lang->albumnotexisterror,$lang->errorprofilealbums);
	}
	$plugins->run_hooks("profilealbums_show_albums_start");
	count_images($aid,$uid);
	$query_add_breadcrumb = $db->simple_select("albums", "*", "aid='".$aid."'");
	$album_add_breadcrumb = $db->fetch_array($query_add_breadcrumb);
	$query_add = $db->simple_select("users", "*", "uid='".$uid."'");
	$user = $db->fetch_array($query_add);
	$lang->profbreadcrumb = $lang->sprintf($lang->profbreadcrumb, $user['username']);
	add_breadcrumb($lang->profbreadcrumb,"member.php?action=profile&uid=".$uid);
	add_breadcrumb($lang->albums,"member.php?action=albums&uid=".$uid);
	add_breadcrumb($lang->album." ".$album_add_breadcrumb['name']);

	$query = $db->simple_select('albumsimages', 'COUNT(aid) AS albums', 'uid='.$uid.' AND aid='.$aid, array('limit' => 1));
	$quantity = $db->fetch_field($query, "albums");
	$page = intval($mybb->input['page']);
	$perpage = $mybb->settings['profilealbums_pagination_images'];
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
	$profile_page = "albums.php?aid={$aid}&uid=".$uid;
	$query = $db->query('SELECT * FROM ' . TABLE_PREFIX . 'albumsimages WHERE aid='.$aid.' AND uid='.$uid.' ORDER BY pid DESC LIMIT ' . $start . ', ' . $perpage);
	while($albumsimages = $db->fetch_array($query))
	{
		$image_album .= image_show($albumsimages['image'],$albumsimages['pid'],$albumsimages['aid'],$mybb->settings['profilealbums_resize_images']);
		eval("\$albums_list = \"".$templates->get("profilealbums_album_list")."\";");
	}
	if(!$albums_list)
	{
		$albums_list = "<tr><td class='trow1' align='center'> Empty Album</td></tr>";
	}
	if($uid == $mybb->user['uid'])
	{
		eval("\$manage_albums = \"".$templates->get("profilealbums_album_manage")."\";");
	}
	$pagination = multipage($quantity, (int)$perpage, (int)$page, $profile_page);
	$plugins->run_hooks("profilealbums_show_albums_end");
	eval("\$page = \"".$templates->get("profilealbums_album")."\";");
	output_page($page);
	return false;
}
error_no_permission();
?>	