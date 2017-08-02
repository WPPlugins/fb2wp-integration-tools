<?php
if (!defined('WPINC')) {
	die;
}

//v1.4.7.3 向下支援4.7以下版本部分功能，避免無類別產生致命錯誤
if (class_exists('WP_REST_Controller')) {
	class Mxp_FB2WP_API extends WP_REST_Controller {
		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected $namespace = 'mxp_fb2wp/v1';

		/**
		 * Route base.
		 *
		 * @var string
		 */
		protected $rest_base = 'webhook';

		public $fb_messenger_api = 'https://graph.facebook.com/v2.8/me/messages';

		public $fb_graph_api = 'https://graph.facebook.com/v2.8';

		public function get_namespace_var() {
			return $this->namespace;
		}

		public function get_rest_base_var() {
			return $this->rest_base;
		}

		/**
		 * Register the routes for the objects of the controller.
		 */
		public function register_routes() {
			$namespace = $this->namespace;
			$base = $this->rest_base;
			register_rest_route($namespace, '/' . $base, array(
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				),
				array(
					'methods' => WP_REST_Server::CREATABLE,
					'callback' => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				),
			));
		}

		/**
		 * 處理驗證要求：https://developers.facebook.com/docs/graph-api/webhooks#setupget
		 */
		public function get_items($request) {
			$challenge = $request->get_param("hub_challenge");
			$verify_token = $request->get_param("hub_verify_token");
			//驗證訂閱
			if ($verify_token === get_option("mxp_fb_webhooks_verify_token")) {
				echo $challenge;
				exit;
			}
			return array('msg' => '我猜你在測試，這次就原諒你惹！');
			//WP REST API 在 callback 這邊如果使用 return  就會被包裝成 JSON 格式
		}

		/**
		 * 接收webhook 請求
		 */
		public function create_item($request) {
			$json = $request->get_json_params();
			$body = $request->get_body();
			$events = $json['entry'];
			$is_page = $json['object'] == 'page' ? true : false;
			if ($is_page) {
				//Messenger 部分，至少都會有個傳送者
				$sender = isset($json['entry'][0]['messaging'][0]['sender']['id']) ? $json['entry'][0]['messaging'][0]['sender']['id'] : "";
				//最常見是訊息回覆，但也有可能出現貼圖或是傳送檔案
				$message = isset($json['entry'][0]['messaging'][0]['message']['text']) ? $json['entry'][0]['messaging'][0]['message']['text'] : "";
				//TODO:高負載情況可能一次很多訊息(現在只會抓第一筆訊息回)
				//ref: https://developers.facebook.com/docs/messenger-platform/webhook-reference#batching
				if ($sender != "") {
					$this->messenger_request($sender, $message);
					return array('msg' => 'Done!');
				}
				//Webhooks 訂閱事件處理部分
				for ($i = 0; $i < count($events); ++$i) {
					$is_feed = $events[$i]['changes'][0]['field'] == 'feed' ? true : false;
					if ($is_feed) {
						$event = $events[$i]['changes'][0]['value'];
						$wrap = $this->parsing_event($event);
						if (count($wrap) != 0) {
							$this->fb2wp_log($wrap);
						}
					} else {
						$this->logger('debug-isnotfeed', $body);
					}

				}
			} else {
				$this->logger('debug-isnotpage', $body);
			}
			return array('msg' => '一般人應該是看不到這串文字der～');
		}

		/**
		 * Check if a given request has access to get items
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function get_items_permissions_check($request) {
			//return true; <--use to make readable by all
			return true;
		}

		/**
		 * 驗證請求
		 */
		public function create_item_permissions_check($request) {
			$body = $request->get_body();
			$verify = 'sha1=' . hash_hmac('sha1', $body, get_option("mxp_fb_secret"));
			$signature = $request->get_header('X_HUB_SIGNATURE');
			$this->logger('request_source', 'Body:' . PHP_EOL . print_r($request->get_body(), true) . PHP_EOL . 'Headers:' . PHP_EOL . print_r($request->get_headers(), true));
			if ($verify != $signature) {
				$this->logger('request_verify', $verify . '|' . $signature);
				return false;
			}
			return true;
		}

		/**
		 * Prepare the item for create or update operation
		 *
		 * @param WP_REST_Request $request Request object
		 * @return WP_Error|object $prepared_item
		 */
		protected function prepare_item_for_database($request) {
			return array();
		}

		/**
		 * Prepare the item for the REST response
		 *
		 * @param mixed $item WordPress representation of the item.
		 * @param WP_REST_Request $request Request object.
		 * @return mixed
		 */
		public function prepare_item_for_response($item, $request) {
			return array();
		}

		/**
		 * Get the query params for collections
		 *
		 * @return array
		 */
		public function get_collection_params() {
			return array();
		}

		private function parsing_message($msg) {
			$obj = unserialize(get_option("mxp_messenger_msglist"));
			if (isset($obj['match'])) {
				$m = $obj['match'];
				if (is_array($m)) {
					for ($i = 0; $i < count($m); ++$i) {
						$key = $m[$i]['key'];
						$value = urldecode($m[$i]['value']);
						if ($key == $msg) {
							//1.4.7 新增 hook
							return apply_filters('fb2wp_match_respond_call', $value, $key, $msg);
						}
					}
				}

			}
			if (isset($obj['fuzzy'])) {
				$f = $obj['fuzzy'];
				if (is_array($f)) {
					for ($i = 0; $i < count($f); ++$i) {
						$key = $f[$i]['key'];
						$value = urldecode($f[$i]['value']);
						if (preg_match("/{$key}/i", $msg)) {
							//1.4.7 新增 hook
							return apply_filters('fb2wp_fuzzy_respond_call', $value, $key, $msg);
						}
					}
				}

			}
			return $this->message_nomatch($msg);
		}

		public function message_nomatch($msg) {
			$nomatch = get_option("mxp_messenger_default_reply", "「{$msg}」無法識別指令");
			$detect = false;
			if (strpos($nomatch, '[mxp_input_msg]') !== false) {
				$detect = true;
			}
			if ($detect) {
				return str_replace('[mxp_input_msg]', $msg, $nomatch);
			} else {
				return $nomatch;
			}
		}
		/**
		 * 完整的回覆參考文件：https://developers.facebook.com/docs/messenger-platform/webhook-reference/message
		 * 要做完實在有點雜，先以訊息為主
		 */
		private function messenger_request($sender, $message) {
			$api = $this->fb_messenger_api . '?access_token=' . get_option("mxp_fb_app_access_token");
			$resp = $this->parsing_message($message);
			$resp_data = array('recipient' => array('id' => $sender));
			if ($resp == "") {
				//沒預設資料就回傳讀寫中的狀態，考慮是否要加TODO
				//ref: https://developers.facebook.com/docs/messenger-platform/send-api-reference/sender-actions
				$resp_data['sender_action'] = 'typing_on';
			} else {
				$resp_data['message'] = array('text' => $resp);
			}
			$response = wp_remote_post($api, array(
				'method' => 'POST',
				'timeout' => 5,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
				'body' => json_encode($resp_data),
				'cookies' => array(),
			)
			);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$this->logger('request_error', $error_message);
			} else {
				//都正確就不要再囉唆惹！
			}
		}

		private function parsing_event($event) {
			$item = $event['item'];
			$action = $event['verb'];
			$sender = isset($event['sender_id']) ? $event['sender_id'] : 0000000000;
			$sender_name = $event['sender_name'];
			$post_id = $event['post_id'];
			$published = isset($event['published']) ? $event['published'] : -1;
			$message = isset($event['message']) ? $event['message'] : "";
			$created_time = isset($event['created_time']) ? $event['created_time'] : time();
			$this->logger('debug' . '-' . $item, json_encode($event));
			switch ($item) {
			case 'status':
				//粉絲頁主專屬功能
				//粉絲頁狀態更新(僅粉絲頁主有權限)
				//$event['published'] -> 1=>發佈,0=>排程(未發佈),發佈時會同時送出兩筆，一筆無此欄位資訊，一筆改為已發布
				//可能會突然來個帶圖的狀態文就會有 $event['photos'] 的陣列放圖片連結
				return array(
					'created_time' => $created_time,
					'sender' => $sender,
					'sender_name' => $sender_name,
					'item' => $item,
					'action' => $action,
					'post_id' => $post_id,
					'message' => $message,
					'source_json' => json_encode($event),
				);
				break;
			case 'share':
				//訪客或粉絲頁主分享連結都會到這(分享FB內部文章、連結不一定會有內容，API v2.4 以後還無法取得單篇內容)
				//$event['link'];
				//$event['share_id'];
				//$event['published']; //1=>發佈,0=>排程(未發佈),發佈時會同時送出兩筆，一筆無此欄位資訊，一筆改為已發布
				return array(
					'created_time' => $created_time,
					'sender' => $sender,
					'sender_name' => $sender_name,
					'item' => $item,
					'action' => $action,
					'post_id' => $post_id,
					'message' => $message,
					'source_json' => json_encode($event),
				);
				break;
			case 'photo': //訪客或粉絲頁主PO圖片都會到這
				//$event['link']; //單圖檔連結
				//$event['published']; //粉絲頁主發佈才有的狀態
				return array(
					'created_time' => $created_time,
					'sender' => $sender,
					'sender_name' => $sender_name,
					'item' => $item,
					'action' => $action,
					'post_id' => $post_id,
					'message' => $message,
					'source_json' => json_encode($event),
				);
				break;
			case 'album':
				//粉絲頁主專屬功能
				//ref: https://graph.facebook.com/v2.8/album_id 撈相簿描述
				$api = $this->fb_graph_api . '/' . $event['album_id'] . '?fields=name,description,link&access_token=' . get_option("mxp_fb_app_access_token");
				$response = wp_remote_post($api, array(
					'method' => 'GET',
					'timeout' => 5,
					'redirection' => 5,
					'httpversion' => '1.1',
					'blocking' => true,
					'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
					'cookies' => array(),
				)
				);

				if (is_wp_error($response)) {
					$error_message = $response->get_error_message();
					$this->logger('request_error', $error_message);
					return array();
				}
				$res = json_decode($response['body'], true);
				if (!isset($res['name'])) {
					return array();
				}
				$post_id = $event['sender_id'] . '_' . $event['album_id'];
				$name = $res['name'];
				$des = $res['description'];
				$message = "{$name}\n{$des}";
				$event['message'] = $message; //存回去
				$event['post_id'] = $post_id; //存回去
				//ref: https://developers.facebook.com/docs/graph-api/reference/v2.8/album/photos 撈相簿的所有（？）圖塞文章
				//撈回100張單一頁我想差不多是極限（很懶得寫翻頁跟不超時的部分啊）
				$api = $this->fb_graph_api . '/' . $event['album_id'] . '/photos?limit=100&fields=name,source&access_token=' . get_option("mxp_fb_app_access_token");
				$response = wp_remote_post($api, array(
					'method' => 'GET',
					'timeout' => 5,
					'redirection' => 5,
					'httpversion' => '1.1',
					'blocking' => true,
					'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
					'cookies' => array(),
				)
				);

				if (is_wp_error($response)) {
					$error_message = $response->get_error_message();
					$this->logger('request_error', $error_message);
					return array();
				}
				$res = json_decode($response['body'], true);
				if (!isset($res['data'])) {
					return array();
				}
				$event['link'] = $res['data'];
				return array(
					'created_time' => $created_time,
					'sender' => $sender,
					'sender_name' => $sender_name,
					'item' => $item,
					'action' => $action,
					'post_id' => $post_id,
					'message' => $message,
					'source_json' => json_encode($event),
				);
				break;
			case 'video':
				//可能放影片又不留言的，message 會無回傳值
				$video_id = $event['video_id'];
				$link = $event['link']; //影片連結
				return array(
					'created_time' => $created_time,
					'sender' => $sender,
					'sender_name' => $sender_name,
					'item' => $item,
					'action' => $action,
					'post_id' => $post_id,
					'message' => $message,
					'source_json' => json_encode($event),
				);
				break;
			case 'post':
				//訪客一般發文或針對發文的操作
				return array(
					'created_time' => $created_time,
					'sender' => $sender,
					'sender_name' => $sender_name,
					'item' => $item,
					'action' => $action,
					'post_id' => $post_id,
					'message' => $message,
					'source_json' => json_encode($event),
				);
				break;
			case 'like':
			case 'reaction':
				//打心情
				$post_id = $event['post_id'];
				$reaction_type = $event['reaction_type'];
				//後續有想到怎麼連結WP再說，可能是在迴響下面做自動推文（！？）或是變成簡單的評分機制（！？）
				return array();
				break;
			case 'comment':
				return array();
				break;
			default:
				//DO NOTHING! JUST LOG
				$this->logger('debug' . '-unknow-type-' . $item, json_encode($event));
				return array();
				break;
			}
		}

		private function fb2wp_log($event) {
			global $wpdb;
			if (!is_array($event) || count($event) == 0) {
				return false;
			}
			//FB來的 post_id 參數會被 insert 裡的方法自動轉為數字儲存，會有問題，需要特別設定型別
			$res = $wpdb->insert($wpdb->prefix . "fb2wp_debug", $event, array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'));
			//記錄錯誤
			if (!$res) {
				$this->logger('debug' . '-db', json_encode($res) . PHP_EOL . $wpdb->last_query);
			}
			//判斷是否允許發文以及發文身份是否有授權
			if (get_option("mxp_fb2wp_post_enable", "open") == "open" && $event['action'] == "add") {
				$sender = $event['sender'];
				$sender_name = isset($event['sender_name']) ? $event['sender_name'] : "";
				$item = $event['item'];
				$post_id = $event['post_id'];
				$message = isset($event['message']) ? $event['message'] : "";
				$obj = json_decode($event['source_json'], true);
				$link = isset($obj['link']) ? $obj['link'] : "";
				if ($item == "status" && isset($obj['photos'])) {
					$link = array();
					for ($i = 0; $i < count($obj['photos']); ++$i) {
						$link[] = array('source' => $obj['photos'][$i], 'name' => $message);
					}
				}
				$published = isset($obj['published']) ? $obj['published'] : -1;
				if ($item == "post" || $item == "photo") {
					$published = 1;
				}
				$auth_users = get_option("mxp_fb2wp_auth_users", "");
				$auth_users_arr = array();
				if ($auth_users != "") {
					$auth_users_arr = explode(',', $auth_users);
				}
				//v1.4.7.4 為了避免「限定FB使用者投稿」功能誤會，預將粉絲頁編號設定為開放
				if (get_option("mxp_fb_page_id") != "") {
					$auth_users_arr[] = get_option("mxp_fb_page_id");
				}
				if ($auth_users == "" || in_array($sender, $auth_users_arr)) {
					if ($published == 1) {
						$this->save_to_post($sender, $sender_name, $item, $post_id, $message, $link, false);
					}

				}
			}

		}
		/**
		 * ref: https://developers.facebook.com/docs/messenger-platform/webhook-reference#response
		 * 判斷伺服器跟主機系統是哪家來決定用來射後不理的方法
		 */
		private function make_quick_response_to_facebook() {
			$detect = "NONE";
			$server = $_SERVER['SERVER_SOFTWARE'];
			if (preg_match('/nginx/i', $server)) {
				$detect = "METHOD_A";
			} else if (preg_match('/apache/i', $server)) {
				$detect = "METHOD_B";
			} else {
				$detect = "WTF";
			}
			//WINDOWS不支援多執行緒的做法
			// if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			// 	$detect = "WIN";
			// } else {
			// 	$detect = "NOTWIN";
			// }
			//中斷請求連線自己搞，還是會佔線，先測試看是否要開到多執行緒，但一般人不會ＰＯ這麼頻繁影片吧！！
			switch ($detect) {
			case 'METHOD_A':
				fastcgi_finish_request();
				break;
			case 'METHOD_B':
			case 'WTF':
				ob_start();
				header("Connection: close");
				header("Content-Encoding: none");
				echo "got it";
				$size = ob_get_length();
				header("Content-Length: {$size}");
				ob_end_flush();
				flush();
			default:
				break;
			}
		}

		private function save_to_post($sender, $sender_name, $item, $post_id, $message, $link, $late) {
			if (!$late) {
				$this->make_quick_response_to_facebook();
			}
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$message_arr = explode("\n", $message);
			//處理文章標題，預設就是發文第一行，若無則呼叫設定的參數
			$title = $message_arr[0] == "" ? current_time("Y-m-d H:i:s") . get_option("mxp_fb2wp_default_title", "-FB轉發文章") : wp_strip_all_tags($message_arr[0]);
			//處理文章內文
			$body = "";
			for ($i = 1; $i < count($message_arr); ++$i) {
				$body .= "<p>" . $message_arr[$i] . "</p>";
			}
			//處理文章標籤
			preg_match_all('/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u', $message, $tags);
			if (count($tags[1]) == 0 && get_option("mxp_fb2wp_post_tags", "") != "") {
				$tags[1] = explode(',', get_option("mxp_fb2wp_post_tags"));
			}
			$new_tags = $tags[1];
			//判斷標籤是否有提示需要略過發文
			$pass_tag = get_option("mxp_fb2wp_no_post_tag", "");
			if ($late == false && in_array($pass_tag, $new_tags)) {
				return false;
			}
			//處理標籤同步分類
			$cats = array();
			$cats[] = get_option("mxp_fb2wp_post_category", "1");
			$exist_cats = get_categories(array('taxonomy' => 'category'));
			foreach ($exist_cats as $exist_cat) {
				for ($i = 0; $i < count($new_tags); ++$i) {
					if ($exist_cat->name == $new_tags[$i]) {
						$cats[] = $exist_cat->term_id;
					}
				}
			}
			//建立一篇文章
			$pid = wp_insert_post(array(
				'post_title' => $title,
				'post_content' => $body,
				'post_status' => get_option("mxp_fb2wp_post_status", "draft"),
				'post_author' => get_option("mxp_fb2wp_post_author", "1"),
				'post_category' => $cats,
				'tags_input' => $new_tags,
				'comment_status' => get_option("mxp_fb2wp_post_comment_status", "open"),
				'ping_status' => get_option("mxp_fb2wp_post_ping_status", "open"),
				'post_type' => get_option("mxp_fb2wp_post_type", "post"),
			));
			if (is_wp_error($pid)) {
				$this->logger('insert_post_error', print_r($pid, true));
				return false;
			}
			//處理嵌入文章短碼
			$embed_shortcode = '[mxp_fb2wp_display_embed sender="' . $sender . '" item="' . $item . '" post_id="' . $post_id . '" display="' . get_option("mxp_fb2wp_default_display_embed", "yes") . '" title="' . base64_encode(str_replace(array('\'', '"'), '', wp_strip_all_tags($title))) . '" body="' . base64_encode(str_replace(array('\'', '"'), '', wp_strip_all_tags($body))) . '" pid="' . $pid . '"]';
			//加入 post metadata
			add_post_meta($pid, 'mxp_fb2wp_post_id', $post_id);
			add_post_meta($pid, 'mxp_fb2wp_item', $item);
			add_post_meta($pid, 'mxp_fb2wp_sender', $sender);
			//判斷是否有附加檔案，並上傳
			$filename = array();
			$upload_file = array();
			if ($link != "" && $item != "share") {
				if (!is_array($link)) {
					$filename[] = basename(parse_url($link, PHP_URL_PATH));
					$upload_file[] = wp_upload_bits($filename[0], null, file_get_contents($link));
				} else {
					for ($i = 0; $i < count($link); ++$i) {
						$filename[$i] = basename(parse_url($link[$i]['source'], PHP_URL_PATH));
						$upload_file[] = wp_upload_bits($filename[$i], null, file_get_contents($link[$i]['source']));
					}
				}
				//如果上傳沒失敗，就附加到剛剛那篇文章
				$origin_body = $body;
				$origin_title = $title;
				$set_feature_image = true;
				for ($i = 0; $i < count($upload_file); ++$i) {
					if (!$upload_file[$i]['error']) {
						$wp_filetype = wp_check_filetype($filename[$i], null);
						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_parent' => $pid,
							'post_title' => preg_replace('/\.[^.]+$/', '', $filename[$i]),
							'post_content' => is_array($link) && isset($link[$i]['name']) ? $link[$i]['name'] : $origin_title . $origin_body,
							'post_status' => 'inherit',
						);
						$attachment_id = wp_insert_attachment($attachment, $upload_file[$i]['file'], $pid);
						if (!is_wp_error($attachment_id)) {
							//產生附加檔案中繼資料
							$attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file[$i]['file']);
							wp_update_attachment_metadata($attachment_id, $attachment_data);
							//將圖像的附加檔案設為特色圖片
							$type = explode("/", $wp_filetype['type']);
							if ($set_feature_image == true && $type[0] == 'image') {
								set_post_thumbnail($pid, $attachment_id);
								$set_feature_image = false;
							}
							//更新剛剛那篇文章內容，加載附件更新文章
							$body .= '<p>[mxp_fb2wp_display_attachment src="' . $upload_file[$i]['url'] . '" mime_type="' . $wp_filetype['type'] . '" title="' . base64_encode(str_replace(array('\'', '"'), '', wp_strip_all_tags(is_array($link) && isset($link[$i]['name']) ? $link[$i]['name'] : $origin_title))) . '" body="' . base64_encode(str_replace(array('\'', '"'), '', wp_strip_all_tags(is_array($link) && isset($link[$i]['name']) ? $link[$i]['name'] : $origin_body))) . '" display="' . get_option("mxp_fb2wp_default_display_attachment", "yes") . '" image_display_caption="' . get_option("mxp_fb2wp_default_display_img_caption", "yes") . '"]</p>';
						}
					}
				}
			}
			$body .= '<p>' . $embed_shortcode . '</p>';
			$update_attachment_post = array(
				'ID' => $pid,
				'post_content' => $body,
			);
			$upid = wp_update_post($update_attachment_post);
			if (is_wp_error($upid)) {
				$this->logger('update_post_error', print_r($upid, true));
				return false;
			}
			return true;
		}

		public function sorry_i_am_late_post($event) {
			$wrap = $this->parsing_event($event);
			if (count($wrap) != 0 && $wrap['action'] == "add") {
				$sender = $wrap['sender'];
				$sender_name = isset($wrap['sender_name']) ? $wrap['sender_name'] : "";
				$item = $wrap['item'];
				$post_id = $wrap['post_id'];
				$message = isset($wrap['message']) ? $wrap['message'] : "";
				$obj = json_decode($wrap['source_json'], true);
				$link = isset($obj['link']) ? $obj['link'] : "";
				if ($item == "status" && isset($obj['photos'])) {
					$link = array();
					for ($i = 0; $i < count($obj['photos']); ++$i) {
						$link[] = array('source' => $obj['photos'][$i], 'name' => $message);
					}
				}
				$published = isset($obj['published']) ? $obj['published'] : -1;
				if ($item == "post" || $item == "photo") {
					$published = 1;
				}
				$auth_users = get_option("mxp_fb2wp_auth_users", "");
				$auth_users_arr = array();
				if ($auth_users != "") {
					$auth_users_arr = explode(',', $auth_users);
				}
				if ($auth_users == "" || in_array($sender, $auth_users_arr)) {
					if ($published == 1) {
						return $this->save_to_post($sender, $sender_name, $item, $post_id, $message, $link, true);
					}

				}
			}
			return false;
		}

		public function logger($file, $data) {
			if (get_option("mxp_enable_debug", "yes") == "yes") {
				file_put_contents(
					plugin_dir_path(__FILE__) . 'logs/' . md5(get_option("mxp_fb_secret")) . "-{$file}.txt",
					'===' . time() . '===' . PHP_EOL . $data . PHP_EOL,
					FILE_APPEND
				);
			}
		}
	}
}