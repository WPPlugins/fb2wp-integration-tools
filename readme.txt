=== FB2WP integration tools ===
Contributors: mxp
Donate link: https://www.mxp.tw/contact/
Tags: Mxp.TW, FB2WP, chinese, 中文, FB, 同步, 發佈, 轉發, 機器人, 自動回覆訊息, API, sync, synchronize, 粉絲頁, Facebook, Page, Messenger, webhook, generate, auto, bot
Requires at least: 4.7
Tested up to: 4.8
Stable tag: 1.4.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

透過此外掛來當您粉絲頁的小管家，包含自動回覆訊息、同步圖文回網站！


== Description ==

> 注意: WordPress 版本低於 4.7 與 網站無加密 HTTPS 連線將無法使用此外掛 `粉絲頁發文同步` 功能

> 安裝環境: PHP 5.4.0 以上（建議 PHP 7）

有鑒於現在使用習慣與閱讀來源常常是 Facebook，所以自然希望將整理的資料能夠同步回網站，自行記錄建檔。

此外掛透過 WordPress 4.7 REST API 當作 Facebook Webhook 的端點接口來接收粉絲頁動態，包含訪客 `Messenger訊息`、 `粉絲頁發文`、`訪客發文`、`粉絲頁（訪客）發圖`、`發相簿` 等動態都可以透過此外掛同步回您的網站。

感謝一群棒子所寫的教學介紹文「[如何利用 FB2WP integration tools 讓粉絲團發文同步至我們的網站上](https://goo.gl/BDxOfx)」，設定上有問題可以參考這篇文章哦～

特色(Features):

1. 無限組數設定 Messenger 自動回覆訊息
2. 同步粉絲頁上：粉絲頁主發文、發圖 與 訪客發文發圖
3. Facebook 外掛工具功能（網站留言模組、文章儲存、傳送與引言功能）
4. 同步權限設定，可限制對象同步
5. 同步設定多元，包含選擇同步發文分類與標籤
6. 延伸 Messenger 自動回覆功能彈性，可程式化設定 `fb2wp_match_respond_call`, `fb2wp_fuzzy_respond_call` 兩組事件，強化回覆內容彈性

需求(Requirements):

1. WordPress 4.7 以上
2. HTTPS 加密連線

待辦清單(TODO):

1. 當 Facebook 負載高時可能會集合多筆訊息傳入，目前僅針對第一筆回覆
2. 多國語言化（i18n）
3. 寫文件


== Installation ==

* 一般

> 進入網站後台，「外掛」->「安裝外掛」搜尋此外掛名稱

* 進階

1. 上傳外掛至外掛目錄 `wp-content/plugins/` 下。 Upload the plugin files to the `/wp-content/plugins/` directory
2. 於後台外掛功能處啟動外掛。 Activate the plugin through the 'Plugins' screen in WordPress
3. 啟用後在後台選單可找到「FB工具箱設定」進行參數調整。 Use the 「FB工具箱設定」 screen to configure the plugin
4. 完成。 Done


== Screenshots ==

1. **FB工具箱設定** - 第一部分為設定 Facebook App 資訊，本外掛將會使用該 App 資訊向 Facebook 回覆必要資料。第二部分為將此外掛提供的 REST API 端點向 Facebook Webhook 提交，如外掛中描述。第三部分為開通 Messenger API 後，如果沒有符合參照比對句的預設回覆。

2. **FB工具箱設定** - 第四部分為文章同步回 WordPress 的設定，功能如設定描述。

3. **FB工具箱設定** - 續第四部分同步回 WordPress 的設定中，包含shortcode的參數描述與每篇同步文章footer顯示資訊，第五部分為Facebook外掛小工具功能，個別選擇啟用即可，而第六部分為開發除錯，可在此區域查詢系統記錄。

4. **訊息回覆設定** - 設定 Messenger API 的比對句與其回應句，此為提供 Messenger 自動回覆訊息功能。

5. **FB發文紀錄** - 此部分為顯示同步歷史紀錄，可供原本關閉同步的轉發文章紀錄，可以再此手動轉發回 WordPress 內容。

== Frequently Asked Questions ==

= 設定中的 「粉絲頁應用程式授權碼」、 「請將下列連結填入 App Messenger, Webhooks 之回呼網址」、 「回呼驗證權杖」 這三個欄位要如何填寫？ =

粉絲頁應用程式授權碼 這個是需要搭配使用 FB APP 產生

參考之前[寫過的一篇文章](https://goo.gl/dVaa5i)

1. 申請一個 Facebook App，輸入名稱信箱跟用途

2. 進控制面板後左側 新增產品 ，把 Webhooks 與 Messenger 加入

首先在 webhooks 那個設定裡可以看到一個 新訂閱內容 的按鈕在右邊欄，點下去，選擇 Page （粉絲頁）

填入外掛產生的 回呼網址 （callback url）、 與你自己設定的 驗證權杖 （verify token），最後是勾選如外掛說的 訂閱欄位 <conversations, feed, messages, messaging_optins, messaging_postbacks>

都確定後就驗證儲存！

回到 粉絲頁應用程式授權碼

需在 Messenger 這功能設定頁裡選擇 權杖產生 跟設定 Webhooks 訂閱綁定 （這邊都是選擇要訂閱哪個粉絲頁就可以了），在權杖產生那邊會生成一組很長的授權碼，把它貼回到外掛設定頁中，存檔，搞定！

= 怎麼粉絲頁上修改不會跟著修改？ =

此外掛只會抓取「新增」事件來加入網站內容，其餘操作僅是記錄

= 粉絲頁上同步到網站後，行間距變很大？ =

外掛會根據你的發文空行，去轉換成 `p` 標籤！使用上不建議在粉絲頁上使用連續空行排版，會造成網站顯示行距過大。這部分建議文字可以打多一點後再使用連續空行，閱讀上較不會造成問題。可以參考作者的筆記粉絲頁：[一介資男](https://www.facebook.com/a.tech.guy/) 搭配網站練習！

= 一次新增相簿照片超過一百張會怎樣？ =

運氣好是會抓完一百張，發文。運氣不好（主機連線速度太慢）會導致超時被終止，沒反應！

運氣好定義： PHP 執行時間設的夠長

= Facebook 外掛功能區塊介紹 =

在 `1.4.3` 版本中加入的新功能「Facebook 外掛」，其中包含：

- 文章引言 ：啟動全站內容將可以被瀏覽者重點文字選取後分享於 Facebook 中 
- 文章儲存 ：儲存該篇文章於 Facebook ，可在[我的珍藏](https://www.facebook.com/saved/)裡找到
- 文章傳送 ：傳送該篇文章給指定對象
- 文章留言 ：網站文章或其他類型內容下方的留言功能，有分兩種模式，共存模式即為與原本留言功能共存，單一模式為僅保留 Facebook 留言功能。

= 粉絲頁過去的文章怎麼辦，能同步回網站嗎？ =

這部分很遺憾外掛只能做到「從安裝外掛啟用之後」的文章，粉絲頁上舊文章經過測試，可以使用這款「[Facebook Fanpage import](https://tw.wordpress.org/plugins/facebook-fanpage-import/)」外掛進行匯入！

要注意的一點是伺服器上 Cronjob 記得要設定哦～ 如果網站一開始流量太低，可能會有漏抓、跳拍問題（定期抓取功能可能有時候失靈）。

= 碰到問題怎回報？ =

可以透過粉絲頁、網站或是個人臉書找到我。

臉書：[點此](https://www.facebook.com/mxp.tw)

粉絲頁： [點此](https://www.facebook.com/a.tech.guy)

網站：[聯絡我](https://www.mxp.tw/contact/)

== Changelog ==

= 1.4.8 =
* 修正 DEBUG 模式下顯示的錯誤資訊
* 因應 Facebook Webhooks 這次[故障](https://developers.facebook.com/bugs/463793280620151/)與[語系文件遺失](https://developers.facebook.com/bugs/1836827343245862/)補上追蹤原始請求紀錄和錯誤判斷

= 1.4.7.4 =
* 為了避免「限定FB使用者投稿」功能誤會，預將粉絲頁編號設定為開放
* 修正問與答內容

= 1.4.7.3 =
* 向下支援4.7以下版本部分功能，避免無 WP_REST_Controller 類別產生致命錯誤
* 把設定頁面的程式碼整理了一下ＱＱ

= 1.4.7.2 =
* 寫新功能發現舊功能的BUG，更新了錯字問題
* 強化 `fb2wp_match_respond_call` 與 `fb2wp_fuzzy_respond_call` 兩個事件的完整性

= 1.4.7.1 =
* 修正FB訊息 hook 事件處理方法

= 1.4.7 =
* 延伸 Messenger 自動回覆功能彈性，可程式化設定 `fb2wp_match_respond_call`, `fb2wp_fuzzy_respond_call` 兩組事件，強化回覆內容彈性
* 補強說明文件
* 更新快照圖片

= 1.4.6 =
* 終於把待辦事項中第一項「將使用者所輸入的訊息參數化」給完成
* 修正一些錯字小問題

= 1.4.5 =
* 修正FB留言模組跟隨在任意有實作留言模板區塊文後，透過後台內建管理開通留言與否設定
* 新增Facebook小工具上方描述設定
* 新增Facebook儲存外掛大小按鈕設定

= 1.4.4 =
* 新增問與答，關於設定同步功能部份
* 新增設定「粉絲頁編號」
* 新增 fb:pages, fb:app_id 的 head meta 值

= 1.4.3.1 =
* 修正版本比對問題
* 修正後台設定選項失靈

= 1.4.3 =
* 修正後台輸出因html標籤，導致顯示錯誤，避免被自己XSS
* 新增FB引言功能
* 新增FB儲存文章,傳送,留言功能
* 修正前台文章附件內容輸出，提高安全性
* 新增移除外掛是否刪除設定選項

= 1.4.2 =
* 新增上傳的圖片自動設定為該發文的特色圖片，相容 schemapress 所產生的 JSON-LD 資料
* 修正 Messenger Webhook 傳來資料的判斷式

= 1.4.1 =
* 移除 Microdata JSON-LD 支援，避免造成 Google Search Console 結構化資料判斷錯誤

= 1.4.0 =
* 改良更新方法，確保各版本升級時不會有問題。

= 1.3.9 =
* 解決 `PHP Deprecated:  Non-static method Mxp_FB2WP::get_instance() should not be called statically` 警示

= 1.3.8 =
* 優化一些寫法

= 1.3.7 =
* 新增FB發文使用自訂標籤（#tag）停止該篇同步發文

= 1.3.6 =
* 更新簡寫陣列（[]）的寫法，向下相容PHP版本
* 新增功能：tag 中包含完整分類字眼就將發文加入該分類

= 1.3.5 =
* 更新外掛描述頁面資訊

= 1.3.4 =
* 2017.01.05
* 提交

更早版本略- -

== Upgrade Notice ==

無