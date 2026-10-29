-- ============================================================================
--  GOP CAC MA THEO DOI GOOGLE VE MOT LAN NAP THU VIEN
--
--  CHAY TREN: CSDL sql_karaokedep_com_vn
--  SAO LUU TRUOC: bang `systems` (script nay da tu luu ban cu, xem buoc 1)
-- ============================================================================
--
--  VAN DE
--  ------
--  PageSpeed do khoi "Google Tag Manager" nang 806,7 KiB - nang hon toan bo
--  noi dung cua chinh website (675 KiB). Ly do: thu vien gtag.js bi nap BON
--  LAN, moi lan khoang 160-185 KiB:
--
--    systems id=88  script_header : G-2XESVP24XC  va  AW-578170159
--    systems id=89  script_body   : AW-11407116052
--    systems id=131 script_1      : G-2XESVP24XC   <- trung voi id=88
--    systems id=132 script_2      : G-2XESVP24XC   <- trung lan thu ba
--
--  Moi khoi con dinh nghia lai `window.dataLayer` va ham `gtag()`.
--
--  CACH SUA
--  --------
--  Google quy dinh: nap gtag.js MOT LAN roi goi `gtag('config', ...)` cho
--  tung ma. Lam dung vay thi ca ba ma van hoat dong y nguyen, chi khac la
--  thu vien khong con bi tai lap. Tiet kiem khoang 500 KiB.
--
--  KHONG BO MA NAO. Ca ba ma deu duoc giu:
--    G-2XESVP24XC     Google Analytics 4
--    AW-578170159     Google Ads
--    AW-11407116052   Google Ads
--  Su kien page_view gui ve AW-578170159 cung duoc giu.
--
--  MOT LOI RIENG DA SUA LUON
--  -------------------------
--  Doan cu gui kem  'user_id': 'replace with value'  - dung nguyen chuoi mau
--  ma nguoi dat tag chua thay. Nghia la moi luot xem deu bao cho Google rang
--  user_id la chuoi "replace with value". Da BO han tham so user_id thay vi
--  doan mot gia tri. Neu ben marketing thuc su can user_id, ho dat lai bang
--  gia tri that.
--
--  LUI LAI
--  -------
--  Buoc 1 luu ban cu vao bang systems_tracking_backup. Muon tra lai:
--    UPDATE systems s JOIN systems_tracking_backup b ON b.id = s.id
--       SET s.content = b.content;
-- ============================================================================

SET NAMES utf8mb4;
START TRANSACTION;

-- 1. Luu ban cu
CREATE TABLE IF NOT EXISTS `systems_tracking_backup` (
    `id` INT PRIMARY KEY,
    `keyword` VARCHAR(191),
    `content` LONGTEXT,
    `saved_at` DATETIME
);

INSERT INTO `systems_tracking_backup` (`id`, `keyword`, `content`, `saved_at`)
SELECT `id`, `keyword`, `content`, NOW() FROM `systems` WHERE `id` IN (88, 89, 131, 132)
ON DUPLICATE KEY UPDATE `content` = VALUES(`content`), `saved_at` = VALUES(`saved_at`);

-- 2. script_header: nap gtag.js mot lan, config ca ba ma
UPDATE `systems` SET `content` =
'<!-- Google tag (gtag.js) - nap MOT LAN cho ca ba ma -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-2XESVP24XC"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(\'js\', new Date());
  gtag(\'config\', \'G-2XESVP24XC\');
  gtag(\'config\', \'AW-578170159\');
  gtag(\'config\', \'AW-11407116052\');
  gtag(\'event\', \'page_view\', { \'send_to\': \'AW-578170159\' });
</script>
<script async src="https://s.zzcdn.me/ztr/ztracker.js?id=7118134106671710208"></script>'
WHERE `id` = 88;

-- 3. script_body: ma AW-11407116052 da duoc config o tren, bo khoi trung
UPDATE `systems` SET `content` = '' WHERE `id` = 89;

-- 4. script_1 va script_2: ca hai deu la ban sao cua G-2XESVP24XC o id=88
UPDATE `systems` SET `content` = '' WHERE `id` IN (131, 132);

COMMIT;

-- Kiem tra sau khi chay: tren trang chu chi con DUNG MOT the
--   <script async src="https://www.googletagmanager.com/gtag/js?id=...">
-- va ba dong gtag('config', ...).
