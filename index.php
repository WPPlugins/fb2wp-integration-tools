<?php
/**
 * Plugin Name: FB2WP integration tools - Mxp.TW
 * Plugin URI:
 * Description: 使用此外掛接收 Facebook API 資訊來達到粉絲頁發布貼文同步更新 WordPress 與整合 Messenger API 應用，可設定無限關鍵字回覆。
 * Version: 1.4.8
 * Author: Chun
 * Author URI: https://www.mxp.tw/contact/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('WPINC')) {
	die;
}

if (!class_exists('Mxp_FB2WP_API')) {
	include_once plugin_dir_path(__FILE__) . "rest_api.php";
}

class Mxp_FB2WP {
	static $version = '1.4.8';
	protected static $instance = null;
	protected static $rest_api = null;
	public $slug = 'mxp-fb2wp';

	/*
		Core Functions
	*/
	private function __construct() {
		//check if install or not
		$ver = get_option("mxp_fb2wp_db_version");
		if (!isset($ver) || $ver == "") {
			$this->install();
		} else if (version_compare(self::$version, $ver, '>')) {
			$this->update($ver);
		}
		$this->init();
	}

	public static function get_instance() {
		global $wp_version;
		// REST API (WP_REST_Controller) was included starting WordPress 4.7.
		if (!isset(self::$rest_api) && version_compare($wp_version, '4.7', '>=')) {
			self::$rest_api = new Mxp_FB2WP_API();
			add_action('rest_api_init', array(__CLASS__, 'register_facebook_webhooks'));
			update_option("mxp_fb2wp_callback_url", '/' . self::$rest_api->get_namespace_var() . '/' . self::$rest_api->get_rest_base_var());
		} else {
			update_option("mxp_fb2wp_callback_url", 'ERROR');
		}

		if (!isset(self::$instance) && is_super_admin()) {
			self::$instance = new self;
		}
		self::register_public_action();
		return array('Mxp_FB2WP' => self::$instance, 'Mxp_FB2WP_API' => self::$rest_api);
	}

	private function init() {
		add_action('admin_enqueue_scripts', array($this, 'load_assets'));
		add_action('admin_menu', array($this, 'create_plugin_menu'));
		add_action('wp_ajax_mxp_messenger_settings_save', array($this, 'mxp_messenger_settings_save'));
		add_action('wp_ajax_mxp_debug_record_action', array($this, 'mxp_debug_record_action'));
		$this->get_fb_locals();
	}

	public static function register_public_action() {
		if (get_option("mxp_fb_enable_jssdk", "yes") == "yes") {
			add_action('wp_head', array(__CLASS__, 'setting_fb_sdk'));
		}
		add_action('wp_head', array(__CLASS__, 'add_generator'));
		add_shortcode('mxp_fb2wp_display_attachment', array(__CLASS__, 'register_attachment_shortcode'));
		add_shortcode('mxp_fb2wp_display_embed', array(__CLASS__, 'register_embed_shortcode'));
		add_action('wp_footer', array(__CLASS__, 'register_facebook_quote'));
		add_filter('the_content', array(__CLASS__, 'register_facebook_save'));
		add_action('comments_template', array(__CLASS__, 'register_facebook_comment'), 1);
		add_filter('comments_template', array(__CLASS__, 'overwrite_default_comment'));
	}

	private function install() {
		global $wpdb;
		$collate = '';

		if ($wpdb->has_cap('collation')) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
		CREATE TABLE {$wpdb->prefix}fb2wp_debug (
		  sid bigint(20) NOT NULL AUTO_INCREMENT,
		  created_time bigint(32) NOT NULL,
		  sender bigint(32) NOT NULL,
		  sender_name varchar(255) NULL,
		  action varchar(20) NOT NULL,
		  item varchar(20) NOT NULL,
		  post_id varchar(255) NOT NULL,
		  message longtext NULL,
		  source_json longtext NOT NULL,
		  PRIMARY KEY  (sid)
		) $collate;";
		if (!function_exists('dbDelta')) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		dbDelta($tables);
		add_option("mxp_fb2wp_db_version", self::$version);
	}

	private function update($ver) {
		include plugin_dir_path(__FILE__) . "update.php";
		$res = Mxp_Update::apply_update($ver);
		if ($res == true) {
			update_option("mxp_fb2wp_db_version", self::$version);
		} else {
			deactivate_plugins(plugin_basename(__FILE__));
			//更新失敗的TODO:聯絡我～回報錯誤
			wp_die('更新失敗惹...Q_Q||| 請來信至: im@mxp.tw 告訴我您是從哪個版本升級發生意外的？可以使用 Chrome Dev tools 的 console 分頁查看是否有錯誤提示！', 'Q_Q|||');
		}

	}

	/*
		public methods
	*/
	public function create_plugin_menu() {
		add_menu_page('Mxp.TW FB工具箱', 'FB工具箱設定', 'administrator', $this->slug, array($this, 'main_page_cb'), 'dashicons-admin-generic');
		add_submenu_page($this->slug, '訊息回覆設定', '訊息回覆設定', 'administrator', $this->slug . '-message', array($this, 'message_page_cb'));
		add_submenu_page($this->slug, 'FB發文紀錄', 'FB發文紀錄', 'administrator', $this->slug . '-post', array($this, 'post_page_cb'));
	}

	public function page_wraper($title, $cb) {
		echo '<div class="wrap" id="mxp"><h1>' . $title . '</h1>';
		call_user_func($cb);
		echo '</div>';
	}

	public function main_page_cb() {
		$this->page_wraper('FB工具箱設定', function () {
			include plugin_dir_path(__FILE__) . "views/main.php";
		});
		wp_localize_script($this->slug . '-main-page', 'MXP_FB2WP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('mxp-ajax-nonce'),
		));
		wp_enqueue_script($this->slug . '-main-page');
	}

	public function message_page_cb() {
		$this->page_wraper('訊息回覆設定', function () {
			include plugin_dir_path(__FILE__) . "views/message.php";
		});
		wp_localize_script($this->slug . '-message-page', 'MXP_FB2WP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('mxp-ajax-nonce'),
		));
		wp_enqueue_script($this->slug . '-message-page');
		wp_enqueue_script($this->slug . '-loading-script');
		wp_enqueue_style($this->slug . '-loading-style');
	}

	public function post_page_cb() {
		$this->page_wraper('發文紀錄', function () {
			include plugin_dir_path(__FILE__) . "views/post.php";
		});
		wp_localize_script($this->slug . '-post-page', 'MXP_FB2WP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('mxp-ajax-nonce'),
		));
		wp_enqueue_script($this->slug . '-post-page');
		wp_enqueue_script($this->slug . '-loading-script');
		wp_enqueue_style($this->slug . '-loading-style');
	}

	public function load_assets() {
		wp_register_script($this->slug . '-main-page', plugin_dir_url(__FILE__) . 'views/js/main.js', array('jquery'), false, false);
		wp_register_script($this->slug . '-message-page', plugin_dir_url(__FILE__) . 'views/js/message.js', array('jquery'), false, false);
		wp_register_script($this->slug . '-post-page', plugin_dir_url(__FILE__) . 'views/js/post.js', array('jquery'), false, false);
		wp_register_script($this->slug . '-loading-script', plugin_dir_url(__FILE__) . 'views/js/waitMe.min.js', array('jquery'), false, false);
		wp_register_style($this->slug . '-loading-style', plugin_dir_url(__FILE__) . 'views/css/waitMe.min.css', array(), false);
	}

	public static function register_attachment_shortcode($atts) {
		extract(shortcode_atts(array(
			'id' => '',
			'class' => '',
			'src' => '',
			'mime_type' => '',
			'title' => '',
			'body' => '',
			'video_height' => '',
			'video_width' => '',
			'video_autoplay' => 'no',
			'video_loop' => 'no',
			'video_preload' => 'auto', //auto|metadata|none
			'video_controls' => 'yes',
			'display' => 'yes',
			'image_display_caption' => 'yes',
			'image_width' => '',
			'image_height' => '',
		), $atts, 'mxp_fb2wp_display_attachment'));
		if ($src == "" || $mime_type == "" || $display != "yes") {
			return '';
		}
		$title = base64_decode($title);
		$body = base64_decode($body);
		$type = explode("/", $mime_type);
		switch ($type[0]) {
		case 'image':
			$id = $id != '' ? 'id="' . esc_attr($id) . '"' : '';
			$image_width = $image_width != '' ? 'width="' . esc_attr($image_width) . '"' : 'width="' . esc_attr(get_option("mxp_fb2wp_image_width", "")) . '"';
			$image_height = $image_height != '' ? 'width="' . esc_attr($image_height) . '"' : 'width="' . esc_attr(get_option("mxp_fb2wp_image_height", "")) . '"';
			$html5 = "<figure {$id} class='mxp-fb2wp facebook-image " . esc_attr($class) . "'><img src='" . esc_url($src) . "' alt='" . esc_attr($title) . "' {$image_width} {$image_height} />";
			if ($body != "" && $image_display_caption == "yes") {
				$html5 .= "<figcaption>" . esc_html($body) . "</figcaption>";
			}
			$html5 .= "</figure>";
			return $html5;
			break;
		case 'video':
			$video_width = $video_width != '' ? esc_attr($video_width) : esc_attr(get_option("mxp_fb2wp_video_width", "320"));
			$video_height = $video_height != '' ? esc_attr($video_height) : esc_attr(get_option("mxp_fb2wp_video_height", "240"));
			$video_controls = $video_controls == 'yes' ? 'controls' : '';
			$video_autoplay = $video_autoplay == 'yes' ? 'autoplay' : '';
			$video_loop = $video_loop == 'yes' ? 'loop' : '';
			$video_preload = $video_preload != '' ? 'preload="' . esc_attr($video_preload) . '"' : '';
			$html5 = "<video width='{$video_width}' height='{$video_height}' alt='" . esc_attr($body) . "' title='" . esc_attr($title) . "' {$video_preload} {$video_controls} {$video_autoplay} {$video_loop}><source src='" . esc_url($src) . "' type='" . esc_attr($mime_type) . "'>您的瀏覽器不支援使用HTML5 video 標籤播放影片</video>";
			return $html5;
			break;
		default:
			return '';
			break;
		}

	}

	public static function register_embed_shortcode($atts) {
		extract(shortcode_atts(array(
			'sender' => '',
			'item' => '',
			'post_id' => '',
			'title' => '',
			'body' => '',
			'pid' => '',
			'display' => 'yes',
		), $atts, 'mxp_fb2wp_display_embed'));
		$meta = '';
		if ($post_id == "" || $display != "yes") {
			return $meta;
		}
		$posts = explode("_", $post_id);
		$footer = get_option("mxp_fb2wp_post_footer", "");
		if (has_shortcode($footer, 'mxp_fb2wp_display_embed')) {
			$footer = strip_shortcodes($footer);
		}
		return "<div class='fb-post' data-href='https://www.facebook.com/{$posts[0]}/posts/{$posts[1]}'></div>" . $meta . '<p></p>' . do_shortcode($footer);
	}
	// v1.4.3 新增FB引言功能
	public static function register_facebook_quote() {
		if (get_option("mxp_fb_quote_enable", "yes") == "yes") {
			echo '<div class="fb-quote"></div>';
		}
	}
	// v1.4.3 新增FB儲存文章,傳送,留言功能
	public static function register_facebook_save($content) {
		global $wp_current_filter;
		if (get_post_type(get_the_ID()) != 'post' || is_page() || is_feed() || is_archive() || is_home() || in_array('get_the_excerpt', (array) $wp_current_filter) || 'the_excerpt' == current_filter()) {
			return $content;
		}
		$func = '';
		if (get_option("mxp_fb_save_enable", "yes") == "yes" || get_option("mxp_fb_save_enable", "yes") == "yes1") {
			if (get_option("mxp_fb_save_enable", "yes") == "yes") {
				$size = 'large';
			} else {
				$size = 'small';
			}
			$func .= '<p><div class="fb-save" data-size="' . $size . '"></div></p>';
		}
		if (get_option("mxp_fb_send_enable", "yes") == "yes") {
			$func .= '<p><div class="fb-send" data-layout="button_count"></div></p>';
		}
		return $content . "<div id='mxp_fb_functions_section'>" . get_option("mxp_fb_functions_section_title", "</h3>Facebook 功能：</h3>") . $func . "</div>";
	}
	// v1.4.4.1 修正FB留言模組跟隨在任意有實作留言模板區塊文後
	public static function register_facebook_comment() {
		global $post;
		if (!(is_singular() && (have_comments() || 'open' == $post->comment_status))) {
			return;
		}
		if (get_option("mxp_fb_comments_enable", "yes") == "yes" || get_option("mxp_fb_comments_enable", "yes") == "yes1") {
			echo '<div id="mxp-fb2wp-comments"><p><div class="fb-comments"  data-numposts="5"></div></p></div>';
		}
	}
	// v1.4.5 新增FB留言模組覆蓋方法
	public static function overwrite_default_comment($comment_template) {
		global $post;
		if (!(is_singular() && (have_comments() || 'open' == $post->comment_status))) {
			return;
		}
		if (get_option("mxp_fb_comments_enable", "yes") == "yes1") {
			return dirname(__FILE__) . '/views/fb-comments.php';
		}
		return $comment_template;
	}

	public static function register_facebook_webhooks() {
		self::$rest_api->register_routes();
	}

	public static function add_generator() {
		echo '<meta name="generator" content="FB2WP - ' . self::$version . ' Powered by Mxp.TW" />' . "\n";
	}

	public static function setting_fb_sdk() {

		?>
		<?php if (get_option("mxp_fb_app_id") != ""): ?>
			<meta property="fb:app_id" content="<?php echo get_option("mxp_fb_app_id"); ?>" />
		<?php endif;?>
		<?php if (get_option("mxp_fb_page_id") != ""): ?>
			<meta property="fb:pages" content="<?php echo get_option("mxp_fb_page_id"); ?>" />
		<?php endif;?>
			<script>
	  window.fbAsyncInit = function() {
	    FB.init({
	      appId      : '<?php echo get_option("mxp_fb_app_id"); ?>',
	      xfbml      : true,
	      version    : '<?php echo get_option("mxp_fb_api_version", "v2.8"); ?>'
	    });
	  };

	  (function(d, s, id){
	     var js, fjs = d.getElementsByTagName(s)[0];
	     if (d.getElementById(id)) {return;}
	     js = d.createElement(s); js.id = id;
	     js.src = "//connect.facebook.net/<?php echo get_option("mxp_fb_jssdk_local", "zh_TW"); ?>/sdk.js";
	     fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));
	</script>
	<?php

	}
	//取得語言標籤
	public function get_fb_locals() {
		$fb_locals = get_transient($this->slug . '-cache-fb-locals');
		if (false === $fb_locals || $fb_locals == "") {
			$fb_locals = wp_remote_get('https://www.facebook.com/translations/FacebookLocales.xml');
			$response_code = wp_remote_retrieve_response_code($fb_locals);
			if ($response_code != 200) {
				$error_message = wp_remote_retrieve_body($fb_locals);
				return array('locale' => 'error', 'msg' => $error_message);
			}
			$local_arr = json_decode(json_encode(simplexml_load_string($fb_locals['body'])), true);
			set_transient($this->slug . '-cache-fb-locals', $local_arr, 4 * WEEK_IN_SECONDS);
		}
		return $fb_locals;
	}

	public function get_plugin_logs() {
		$list = scandir(plugin_dir_path(__FILE__) . 'logs/');
		if ($list == false) {
			return array();
		}
		$logs = array();
		for ($i = 0; $i < count($list); ++$i) {
			$end = explode('.', $list[$i]);
			if ('txt' == end($end)) {
				$logs[] = plugin_dir_url(__FILE__) . 'logs/' . $list[$i];
			}
		}
		return $logs;
	}
	public function mxp_messenger_settings_save() {
		$data = $_POST['data'];
		$nonce = $_POST['nonce'];
		$method = $_POST['method'];
		if (!wp_verify_nonce($nonce, 'mxp-ajax-nonce')) {
			wp_send_json_error(array('data' => array('msg' => '錯誤的請求')));
		}
		if (!isset($data) || $data == "") {
			update_option("mxp_messenger_msglist", serialize(array('match' => array(), 'fuzzy' => array())));
			wp_send_json_success(array('data' => $data));
		}
		if (isset($method) && $method == "get") {
			wp_send_json_success(unserialize(get_option("mxp_messenger_msglist")));
		}
		if (update_option("mxp_messenger_msglist", serialize($data))) {
			wp_send_json_success(array('data' => $data));
		} else {
			wp_send_json_error(array('data' => array('msg' => '無效更新')));
		}

	}

	public function mxp_debug_record_action() {
		$method = $_POST['method'];
		$nonce = $_POST['nonce'];

		if (!wp_verify_nonce($nonce, 'mxp-ajax-nonce') || !isset($method)) {
			wp_send_json_error(array('data' => array('msg' => '錯誤的請求')));
		}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 0;
		$sid = isset($_POST['sid']) ? explode(",", $_POST['sid']) : array();
		global $wpdb;
		switch ($method) {
		case 'get':
			$offset = 25;
			$now = $page * $offset;
			$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fb2wp_debug");
			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fb2wp_debug ORDER BY sid DESC LIMIT {$now},{$offset}", ARRAY_A);
			$pages = ceil($count / $offset);
			wp_send_json_success(array('data' => $data, 'total_pages' => $pages, 'page' => $page));
			break;
		case 'post':
			$data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}fb2wp_debug WHERE sid = {$sid[0]}", ARRAY_A);
			$fb2wp = Mxp_FB2WP::get_instance();
			$res = $fb2wp['Mxp_FB2WP_API']->sorry_i_am_late_post(json_decode($data['source_json'], true));
			if ($res) {
				wp_send_json_success(array('msg' => 'done'));
			} else {
				wp_send_json_error(array('msg' => '無效的請求'));
			}
			break;
		case 'delete':
			if (count($sid) != 0) {
				for ($i = 0; $i < count($sid); ++$i) {
					$wpdb->delete("{$wpdb->prefix}fb2wp_debug", array('sid' => intval($sid[$i])));
				}
				wp_send_json_success();
			} else {
				wp_send_json_error(array('msg' => '無效的請求'));
			}
			break;
		default:
			wp_send_json_error(array('msg' => '無效的請求'));
			break;
		}

	}
}

add_action('plugins_loaded', array('Mxp_FB2WP', 'get_instance'));
