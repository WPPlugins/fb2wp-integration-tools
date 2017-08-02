<?php
if (!defined('WPINC')) {
	die;
}

//更新方法都寫這，方法必須要回傳 true 才算更新完成。
class Mxp_Update {
	public static $version_list = array('1.3.4', '1.3.5', '1.3.6', '1.3.7', '1.3.8', '1.3.9', '1.4.0', '1.4.1', '1.4.2', '1.4.3', '1.4.3.1', '1.4.4', '1.4.5', '1.4.6', '1.4.7', '1.4.7.1', '1.4.7.2', '1.4.7.3', '1.4.7.4', '1.4.8');

	public static function apply_update($ver) {
		$index = array_search($ver, self::$version_list);
		if ($index === false) {
			echo "<script>console.log('update version: {$ver}, in index: {$index}');</script>";
			return false;
		}
		for ($i = $index + 1; $i < count(self::$version_list); ++$i) {
			$new_v = str_replace(".", "_", self::$version_list[$i]);
			if (defined('WP_DEBUG') && WP_DEBUG === true) {
				echo "<script>console.log('mxp_update_to_v{$new_v}');</script>";
			}
			if (call_user_func(array(__CLASS__, "mxp_update_to_v{$new_v}")) === false) {
				echo "<script>console.log('current version: {$ver}, new version: {$new_v}');</script>";
				return false;
			}
		}
		return true;
	}
	/**
	 * 經過前面一段混亂的緊湊開發，從 2016-12-21 v0.0.1 版到 2017-01-05 歷經 133 次的 commit
	 * 更新線上測試版本 v1.1.6 到最當前現在最新版 v1.3.4，來準備提交！
	 */
	public function mxp_update_to_v1_3_4() {
		// global $wpdb;
		// $collate = '';

		// if ($wpdb->has_cap('collation')) {
		// 	$collate = $wpdb->get_charset_collate();
		// }
		// $wpdb->query("blahblah~");
		// $tables = "";
		// if (!function_exists('dbDelta')) {
		// 	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// }
		// dbDelta($tables);
		return true;
	}
	public function mxp_update_to_v1_3_5() {
		//更新外掛描述頁面資訊
		return true;
	}
	public function mxp_update_to_v1_3_6() {
		//更新簡寫陣列（[]）的寫法，向下相容PHP版本
		//新增功能：tag 中包含完整分類字眼就將發文加入該分類
		return true;
	}
	public function mxp_update_to_v1_3_7() {
		//新增功能：FB發文使用自訂標籤（#tag）停止該篇同步發文
		return true;
	}
	public function mxp_update_to_v1_3_8() {
		//優化一些寫法
		return true;
	}
	public function mxp_update_to_v1_3_9() {
		//解決 PHP Deprecated:  Non-static method Mxp_FB2WP::get_instance() should not be called statically 警示
		return true;
	}
	public function mxp_update_to_v1_4_0() {
		//改良更新方法，確保各版本升級時不會有問題。
		return true;
	}
	public function mxp_update_to_v1_4_1() {
		//移除 Microdata JSON-LD 支援，避免造成 Google Search Console 結構化資料判斷錯誤
		return true;
	}
	public function mxp_update_to_v1_4_2() {
		//新增上傳的圖片自動設定為該發文的特色圖片，相容 schemapress 所產生的 JSON-LD 資料
		//修正 Messenger Webhook 傳來資料的判斷式
		return true;
	}
	public function mxp_update_to_v1_4_3() {
		//修正後台輸出因html標籤，導致顯示錯誤，避免被自己XSS
		//新增FB引言功能
		//新增FB儲存文章,傳送,留言功能
		//修正前台文章附件內容輸出，提高安全性
		//新增移除外掛是否刪除設定選項
		return true;
	}
	public function mxp_update_to_v1_4_3_1() {
		//修正版本比對函式中參數要為字串
		//修正後台設定選項失靈
		return true;
	}
	public function mxp_update_to_v1_4_4() {
		//新增問與答，關於設定同步功能部份
		//新增設定「粉絲頁編號」
		//新增 fb:pages, fb:app_id 的 head meta 值
		return true;
	}
	public function mxp_update_to_v1_4_5() {
		//修正FB留言模組跟隨在任意有實作留言模板區塊文後，透過後台內建管理開通留言與否設定
		//新增Facebook小工具上方描述設定
		//新增Facebook儲存外掛大小按鈕設定
		return true;
	}
	public function mxp_update_to_v1_4_6() {
		//終於把待辦事項中第一項「將使用者所輸入的訊息參數化」給完成
		//修正一些錯字小問題
		return true;
	}
	public function mxp_update_to_v1_4_7() {
		//延伸 Messenger 自動回覆功能彈性，可程式化設定 `fb2wp_match_respond_call`, `fb2wp_fuzzy_respond_call` 兩組事件，強化回覆內容彈性
		//補強說明文件
		//更新快照圖片
		return true;
	}
	public function mxp_update_to_v1_4_7_1() {
		//修正FB訊息 hook 事件處理方法
		return true;
	}
	public function mxp_update_to_v1_4_7_2() {
		//寫新功能發現舊功能的BUG，更新了錯字問題
		//強化 fb2wp_match_respond_call 與 fb2wp_fuzzy_respond_call 兩個事件的完整性
		return true;
	}
	public function mxp_update_to_v1_4_7_3() {
		//向下支援4.7以下版本部分功能，避免無 WP_REST_Controller 類別產生致命錯誤
		//整理 main.php 程式碼
		return true;
	}
	public function mxp_update_to_v1_4_7_4() {
		//為了避免「限定FB使用者投稿」功能誤會，預將粉絲頁編號設定為開放
		//修正問與答內容
		return true;
	}
	public function mxp_update_to_v1_4_8() {
		//修正 DEBUG 模式下顯示的錯誤資訊
		//因應 Facebook Webhooks 這次[故障](https://developers.facebook.com/bugs/463793280620151/)與[語系文件遺失](https://developers.facebook.com/bugs/1836827343245862/)補上追蹤原始請求紀錄和錯誤判斷
		return true;
	}
}