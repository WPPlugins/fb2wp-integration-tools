<?php
//有整理過些惹...
//TODO:優化
if (!empty($_POST)) {
	$id = $_POST['mxp_fb_app_id'];
	$secret = $_POST['mxp_fb_secret'];
	$token = $_POST['mxp_fb_app_access_token'];
	$vtoken = $_POST['mxp_fb_webhooks_verify_token'];
	$enable_log = $_POST['mxp_enable_debug'];
	$default_reply = $_POST['mxp_messenger_default_reply'];
	$post_enable = $_POST['mxp_fb2wp_post_enable'];
	$post_author = $_POST['mxp_fb2wp_post_author'];
	$post_category = $_POST['mxp_fb2wp_post_category'];
	$post_status = $_POST['mxp_fb2wp_post_status'];
	$comment_status = $_POST['mxp_fb2wp_post_comment_status'];
	$ping_status = $_POST['mxp_fb2wp_post_ping_status'];
	$post_type = $_POST['mxp_fb2wp_post_type'];
	$jssdk = $_POST['mxp_fb_enable_jssdk'];
	$sdk_local = $_POST['mxp_fb_jssdk_local'];
	$fb_api_version = $_POST['mxp_fb_api_version'];
	$auth_users = $_POST['mxp_fb2wp_auth_users'];
	$post_tags = $_POST['mxp_fb2wp_post_tags'];
	$post_title = $_POST['mxp_fb2wp_default_title'];
	$display_attachment = $_POST['mxp_fb2wp_default_display_attachment'];
	$display_embed = $_POST['mxp_fb2wp_default_display_embed'];
	$display_img_caption = $_POST['mxp_fb2wp_default_display_img_caption'];
	$display_img_width = $_POST['mxp_fb2wp_image_width'];
	$display_img_height = $_POST['mxp_fb2wp_image_height'];
	$display_vid_width = $_POST['mxp_fb2wp_video_width'];
	$display_vid_height = $_POST['mxp_fb2wp_video_height'];
	$post_footer = stripslashes($_POST['mxp_fb2wp_post_footer']);
	$no_post_tag = $_POST['mxp_fb2wp_no_post_tag'];
	$enable_fbquote = $_POST['mxp_fb_quote_enable'];
	$enable_fbsave = $_POST['mxp_fb_save_enable'];
	$enable_fbsend = $_POST['mxp_fb_send_enable'];
	$enable_fbcomments = $_POST['mxp_fb_comments_enable'];
	$complete_remove = $_POST['mxp_complete_remove'];
	$page_id = $_POST['mxp_fb_page_id'];
	$section_title = stripslashes($_POST['mxp_fb_functions_section_title']);
	if (has_shortcode($post_footer, 'mxp_fb2wp_display_embed')) {
		echo "<script>alert('「轉發文章Footer內容」中 請勿包含「mxp_fb2wp_display_embed」shortcode');</script>";
		unset($post_footer);
	}
}
if (isset($id) && isset($secret) && isset($token) && isset($vtoken) && isset($enable_log) && isset($default_reply)
	&& isset($post_enable) && isset($post_author) && isset($post_category) && isset($post_status) && isset($comment_status)
	&& isset($ping_status) && isset($post_type) && isset($jssdk) && isset($sdk_local) && isset($fb_api_version)
	&& isset($auth_users) && isset($post_tags) && isset($post_title) && isset($display_attachment) && isset($display_embed)
	&& isset($display_img_caption) && isset($display_img_width) && isset($display_img_height) && isset($display_vid_width)
	&& isset($display_vid_height) && isset($post_footer) && isset($no_post_tag) && isset($enable_fbquote)
	&& isset($enable_fbsave) && isset($enable_fbsend) && isset($enable_fbcomments) && isset($complete_remove) && isset($page_id)
	&& isset($section_title)) {
	update_option("mxp_fb_app_id", $id);
	update_option("mxp_fb_secret", $secret);
	update_option("mxp_fb_app_access_token", $token);
	update_option("mxp_fb_webhooks_verify_token", $vtoken);
	update_option("mxp_enable_debug", $enable_log);
	update_option("mxp_messenger_default_reply", $default_reply);
	update_option("mxp_fb2wp_post_enable", $post_enable);
	update_option("mxp_fb2wp_post_author", $post_author);
	update_option("mxp_fb2wp_post_category", $post_category);
	update_option("mxp_fb2wp_post_status", $post_status);
	update_option("mxp_fb2wp_post_comment_status", $comment_status);
	update_option("mxp_fb2wp_post_ping_status", $ping_status);
	update_option("mxp_fb2wp_post_type", $post_type);
	update_option("mxp_fb_enable_jssdk", $jssdk);
	update_option("mxp_fb_jssdk_local", $sdk_local);
	update_option("mxp_fb_api_version", $fb_api_version);
	update_option("mxp_fb2wp_auth_users", $auth_users);
	update_option("mxp_fb2wp_post_tags", $post_tags);
	update_option("mxp_fb2wp_default_title", $post_title);
	update_option("mxp_fb2wp_default_display_attachment", $display_attachment);
	update_option("mxp_fb2wp_default_display_embed", $display_embed);
	update_option("mxp_fb2wp_default_display_img_caption", $display_img_caption);
	update_option("mxp_fb2wp_image_width", $display_img_width);
	update_option("mxp_fb2wp_image_height", $display_img_height);
	update_option("mxp_fb2wp_video_width", $display_vid_width);
	update_option("mxp_fb2wp_video_height", $display_vid_height);
	update_option("mxp_fb2wp_post_footer", $post_footer);
	update_option("mxp_fb2wp_no_post_tag", $no_post_tag);
	update_option("mxp_fb_quote_enable", $enable_fbquote);
	update_option("mxp_fb_save_enable", $enable_fbsave);
	update_option("mxp_fb_send_enable", $enable_fbsend);
	update_option("mxp_fb_comments_enable", $enable_fbcomments);
	update_option("mxp_complete_remove", $complete_remove);
	update_option("mxp_fb_page_id", $page_id);
	update_option("mxp_fb_functions_section_title", $section_title);
	echo "更新成功！";
}
$rest_url = '';

if (get_option("mxp_fb2wp_callback_url") == "ERROR") {
	global $wp_version;
	$rest_url = 'WordPress 版本過低( v' . $wp_version . ' )，不支援 REST API 方法，請更新( v4.7 以後版本 )後再使用！';
} else {
	if (!is_ssl()) {
		//ref:https://developers.facebook.com/docs/graph-api/webhooks#setup
		$rest_url = 'Facebook API 自 v2.5 版後，不支援非安全連線的回呼 URL，請將網站升級安全連線 HTTPS 後再使用！';
	} else {
		$rest_url = get_rest_url(null, get_option("mxp_fb2wp_callback_url"));
	}
}

?>
<h2><a href="https://developers.facebook.com" target="_blank">FB APP</a> 設定</h2>
<form action="" method="POST">
<p>應用程式編號：
<input type="text" value="<?php echo get_option("mxp_fb_app_id"); ?>" name="mxp_fb_app_id" size="20" id="fb_app_id" />
</p>
<p>應用程式密鑰：
<input type="text" value="<?php echo get_option("mxp_fb_secret"); ?>" name="mxp_fb_secret" size="36" id="fb_app_secret" />
</p>
<p>粉絲頁編號：
<input type="text" value="<?php echo get_option("mxp_fb_page_id"); ?>" name="mxp_fb_page_id" size="20" id="mxp_fb_page_id" />
</p>
<p>粉絲頁應用程式授權碼：
<input type="text" value="<?php echo get_option("mxp_fb_app_access_token"); ?>" name="mxp_fb_app_access_token" size="60" id="fb_app_access_token" />(<a href="https://tw.wordpress.org/plugins/fb2wp-integration-tools/faq/" target="_blank" >Q&A</a>)
</p>
<p>啟用JS SDK：
<input type="radio" name="mxp_fb_enable_jssdk" value="yes" <?php checked('yes', get_option("mxp_fb_enable_jssdk", "yes"));?>>是 </input>
<input type="radio" name="mxp_fb_enable_jssdk" value="no" <?php checked('no', get_option("mxp_fb_enable_jssdk"));?>>否</input>(建議啟用)
</p>
<p>選擇SDK語言：
<?php
$fb2wp = Mxp_FB2WP::get_instance();
$fblocals = $fb2wp['Mxp_FB2WP']->get_fb_locals()['locale'];
if ($fblocals != "error") {
	echo '<select name="mxp_fb_jssdk_local">';
	for ($i = 0; $i < count($fblocals); ++$i) {
		$val = $fblocals[$i]['codes']['code']['standard']['representation'];
		$key = $fblocals[$i]['englishName'];
		echo '<option value="' . $val . '"' . selected(get_option("mxp_fb_jssdk_local", "zh_TW"), $val) . '>' . $key . '</option>';
	}
	echo '</select>';
} else {
	echo '<input type="hidden" name="mxp_fb_jssdk_local" value="zh_TW"/>';
	echo "Facebook 語系檔案發生解析錯誤，請將下面訊息回報開發者： " . $fb2wp['Mxp_FB2WP']->get_fb_locals()['msg'];
}
?>
</p>
<p>設定SDK版本：
<input type="text" size="7" value="<?php echo get_option("mxp_fb_api_version", "v2.8"); ?>" name="mxp_fb_api_version">
</p>
<h2>FB APP 設定 Webhooks</h2>
<p>請將下列連結填入 App Messenger, Webhooks 之回呼網址</p>
<p>
<input type="text" size="100" value="<?php echo $rest_url; ?>" id="fb_app_webhooks_callback_url" disabled />
</p>
<p>並訂閱：conversations, feed, messages, messaging_optins, messaging_postbacks 事件</p>
<p>回呼驗證權杖：
<input type="text" value="<?php echo get_option("mxp_fb_webhooks_verify_token"); ?>" name="mxp_fb_webhooks_verify_token">
</p>
<h2>訊息自動回覆設定</h2>
<p>若無比對到訊息時的預設回覆：
<textarea name="mxp_messenger_default_reply" rows="3" cols="30"><?php echo get_option("mxp_messenger_default_reply"); ?></textarea></br>（可使用 [mxp_input_msg] 關鍵字帶入原使用者輸入句，例如：「您好，無法識別【[mxp_input_msg]】這項指令，請重新輸入，謝謝！ 」）
</p>
<h2>文章同步回 WordPress 設定</h2>
<p>是否啟用：
<input type="radio" name="mxp_fb2wp_post_enable" value="open" <?php checked('open', get_option("mxp_fb2wp_post_enable", "open"));?>>是 </input>
<input type="radio" name="mxp_fb2wp_post_enable" value="closed" <?php checked('closed', get_option("mxp_fb2wp_post_enable"));?>>否</input>
</p>
</p>
<p>發文使用者：
<?php wp_dropdown_users(array('name' => 'mxp_fb2wp_post_author', 'selected' => get_option("mxp_fb2wp_post_author", "1")));?>
</p>
<p>發文分類：
<?php wp_dropdown_categories(array('name' => 'mxp_fb2wp_post_category', 'hide_empty' => 0, 'selected' => get_option("mxp_fb2wp_post_category", "1")));?>
</p>
<p>發文能見度狀態：
<select name="mxp_fb2wp_post_status">
<option value="publish" <?php selected(get_option("mxp_fb2wp_post_status"), "publish");?>>已發表</option>
<option value="pending" <?php selected(get_option("mxp_fb2wp_post_status"), "pending");?>>待審中</option>
<option value="draft" <?php selected(get_option("mxp_fb2wp_post_status", "draft"), "draft");?>>草稿</option>
<option value="private" <?php selected(get_option("mxp_fb2wp_post_status"), "private");?>>私密</option>
</select>
</p>
<p>允許迴響：
<input type="radio" name="mxp_fb2wp_post_comment_status" value="open" <?php checked('open', get_option("mxp_fb2wp_post_comment_status", "open"));?>>是 </input>
<input type="radio" name="mxp_fb2wp_post_comment_status" value="closed" <?php checked('closed', get_option("mxp_fb2wp_post_comment_status"));?>>否</input>
</p>
<p>允許通告：
<input type="radio" name="mxp_fb2wp_post_ping_status" value="open" <?php checked('open', get_option("mxp_fb2wp_post_ping_status", "open"));?>>是 </input>
<input type="radio" name="mxp_fb2wp_post_ping_status" value="closed" <?php checked('closed', get_option("mxp_fb2wp_post_ping_status"));?>>否</input>
</p>
<p>文章類型：
<?php
$ps = get_post_types(array('public' => true));
echo '<select name="mxp_fb2wp_post_type">';
foreach ($ps as $key => $value) {
	echo '<option value="' . $value . '"' . selected(get_option("mxp_fb2wp_post_type", "post"), $value) . '>' . $value . '</option>';
}
echo '</select>';
?>
</p>
<p>限定FB使用者投稿：
<input type="text" name="mxp_fb2wp_auth_users" size="30" value="<?php echo get_option("mxp_fb2wp_auth_users", ""); ?>" />（逗點（,）分隔FB使用者ID，不填入則表示所有人皆授權許可）
</p>
<p>發文標籤：
<input type="text" name="mxp_fb2wp_post_tags" size="30" value="<?php echo get_option("mxp_fb2wp_post_tags", ""); ?>" />（逗點（,）分隔）
</p>
<p>停止該篇轉發文章標籤：#
<input type="text" name="mxp_fb2wp_no_post_tag" size="10" value="<?php echo get_option("mxp_fb2wp_no_post_tag", ""); ?>" />（輸入不需加#號）
</p>
<p>替代標題：
<input type="text" name="mxp_fb2wp_default_title" size="30" value="<?php echo get_option("mxp_fb2wp_default_title", "-FB轉發文章"); ?>" />（文章抓取時使用FB發文的第一行視為標題，若第一行或內文為空，則帶入替代標題）
</p>
<p>預設顯示附件：
<input type="radio" name="mxp_fb2wp_default_display_attachment" value="yes" <?php checked('yes', get_option("mxp_fb2wp_default_display_attachment", "yes"));?>>是 </input>
<input type="radio" name="mxp_fb2wp_default_display_attachment" value="no" <?php checked('no', get_option("mxp_fb2wp_default_display_attachment"));?>>否</input>
</p>
<p>預設顯示圖片描述：
<input type="radio" name="mxp_fb2wp_default_display_img_caption" value="yes" <?php checked('yes', get_option("mxp_fb2wp_default_display_img_caption", "yes"));?>>是 </input>
<input type="radio" name="mxp_fb2wp_default_display_img_caption" value="no" <?php checked('no', get_option("mxp_fb2wp_default_display_img_caption"));?>>否</input>
</p>
<p>預設圖片寬度：
<input type="text" name="mxp_fb2wp_image_width" size="7" value="<?php echo get_option("mxp_fb2wp_image_width", ""); ?>" />
</p>
<p>預設圖片高度：
<input type="text" name="mxp_fb2wp_image_height" size="7" value="<?php echo get_option("mxp_fb2wp_image_height", ""); ?>" />
</p>
<p>預設影片寬度：
<input type="text" name="mxp_fb2wp_video_width" size="7" value="<?php echo get_option("mxp_fb2wp_video_width", "320"); ?>" />
</p>
<p>預設影片高度：<input type="text" name="mxp_fb2wp_video_height" size="7" value="<?php echo get_option("mxp_fb2wp_video_height", "240"); ?>" /></p>
<p>附件短碼影片使用部分，還有其他預設參數：video_controls, video_preload, video_loop, video_autoplay （列為TODO之後參數化）</p>
<p>預設顯示嵌入文章：
<input type="radio" name="mxp_fb2wp_default_display_embed" value="yes" <?php checked('yes', get_option("mxp_fb2wp_default_display_embed", "yes"));?>>是 </input>
<input type="radio" name="mxp_fb2wp_default_display_embed" value="no" <?php checked('no', get_option("mxp_fb2wp_default_display_embed"));?>>否</input>
</p>
<p>轉發文章Footer內容：
<textarea name="mxp_fb2wp_post_footer" rows="3" cols="40"><?php echo get_option("mxp_fb2wp_post_footer", ""); ?></textarea>（支援 HTML, JavaScript, CSS and shortcode）
</p>
<h2>Facebook 外掛功能</h2>
<p>區塊標題：
<input type="text" name="mxp_fb_functions_section_title" value="<?php echo get_option("mxp_fb_functions_section_title", "<h3>Facebook 功能：</h3>"); ?>">（支援 HTML, JavaScript and CSS)
</p>
<p>啟用文章引言分享：
<input type="radio" name="mxp_fb_quote_enable" value="yes" <?php checked('yes', get_option("mxp_fb_quote_enable", "yes"));?>>是 </input>
<input type="radio" name="mxp_fb_quote_enable" value="no" <?php checked('no', get_option("mxp_fb_quote_enable"));?>>否</input>
</p>
<p>啟用文章儲存：
<input type="radio" name="mxp_fb_save_enable" value="yes" <?php checked('yes', get_option("mxp_fb_save_enable", "yes"));?>>是(大按鈕) </input>
<input type="radio" name="mxp_fb_save_enable" value="yes1" <?php checked('yes1', get_option("mxp_fb_save_enable", "yes"));?>>是(小按鈕) </input>
<input type="radio" name="mxp_fb_save_enable" value="no" <?php checked('no', get_option("mxp_fb_save_enable"));?>>否</input>
</p>
<p>啟用文章傳送：
<input type="radio" name="mxp_fb_send_enable" value="yes" <?php checked('yes', get_option("mxp_fb_send_enable", "yes"));?>>是 </input>
<input type="radio" name="mxp_fb_send_enable" value="no" <?php checked('no', get_option("mxp_fb_send_enable"));?>>否</input>
</p>
<p>啟用文章留言：
<input type="radio" name="mxp_fb_comments_enable" value="yes" <?php checked('yes', get_option("mxp_fb_comments_enable", "yes"));?>>是(共存模式) </input>
<input type="radio" name="mxp_fb_comments_enable" value="yes1" <?php checked('yes1', get_option("mxp_fb_comments_enable", "yes"));?>>是(單一模式) </input>
<input type="radio" name="mxp_fb_comments_enable" value="no" <?php checked('no', get_option("mxp_fb_comments_enable"));?>>否</input>
</p>
<h2>開發者功能</h2>
<p>刪除外掛時連帶全部設定資料：
<input type="radio" name="mxp_complete_remove" value="yes" <?php checked('yes', get_option("mxp_complete_remove", "no"));?>>是 </input>
<input type="radio" name="mxp_complete_remove" value="no" <?php checked('no', get_option("mxp_complete_remove", "no"));?>>否</input>
</p>
<p>Log文件記錄：
<input type="radio" name="mxp_enable_debug" value="yes" <?php checked('yes', get_option("mxp_enable_debug", "yes"));?>>是 </input>
<input type="radio" name="mxp_enable_debug" value="no" <?php checked('no', get_option("mxp_enable_debug"));?>>否</input>
</p>
<p>目前記錄檔：
<?php
$logs = $fb2wp['Mxp_FB2WP']->get_plugin_logs();
if (count($logs) == 0) {
	echo '無';
}
echo '<ul>';
for ($i = 0; $i < count($logs); ++$i) {
	echo '<li><a target="_blank" href="' . $logs[$i] . '">' . $logs[$i] . '</a></li>';
}
echo '</ul>';
?>
</p>
<p><input type="submit" id="save" value="儲存" class="button action" /></p>
</form>
<p>當前版本：<?php echo Mxp_FB2WP::$version; ?></p>
