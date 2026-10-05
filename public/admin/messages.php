<?php
require_once(__DIR__ . "/../config.php");

// --- Admin gate ---
if (!isLoggedIn()) {
    header("Location: " . SROOT . "/login.php");
    exit;
}
if (!isAdmin($pdo)) {
    header("Location: " . SROOT . "/index.php");
    exit;
}

// --- Actions ---
$action_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"], $_POST["message_id"])) {
    $mid    = (int)$_POST["message_id"];
    $action = $_POST["action"];
    try {
        if ($action === "mark_read") {
            $pdo->prepare("UPDATE contact_messages SET status='read' WHERE message_id=?")->execute([$mid]);
            $action_msg = "Marked as read.";
        } elseif ($action === "mark_unread") {
            $pdo->prepare("UPDATE contact_messages SET status='unread' WHERE message_id=?")->execute([$mid]);
            $action_msg = "Marked as unread.";
        } elseif ($action === "delete") {
            $pdo->prepare("DELETE FROM contact_messages WHERE message_id=?")->execute([$mid]);
            $action_msg = "Message deleted.";
        }
    } catch (Throwable $e) {
        $action_msg = "Action failed: " . $e->getMessage();
    }
}

// --- Fetch ---
try {
    $rows = $pdo->query("
        SELECT m.*,
               u.user_fname, u.user_lname
        FROM contact_messages m
        LEFT JOIN users u ON u.user_id = m.user_id
        ORDER BY m.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
    $action_msg = "Could not load messages: " . $e->getMessage();
}

$unread = 0;
foreach ($rows as $r) if ($r["status"] === "unread") $unread++;

$admin_page  = "messages";
$admin_title = "Customer Care";
$admin_sub   = $unread . " unread · " . count($rows) . " total";
include(__DIR__ . "/includes/header.php");
?>

<style>
  .msg-toolbar { display:flex; gap:10px; align-items:center; margin: 12px 0 20px; flex-wrap: wrap; }
  .msg-toolbar input[type="search"] {
    flex:1; min-width:220px; padding:10px 14px; border:1px solid #e2e8f0;
    border-radius:8px; font-size:14px;
  }
  .msg-chip { padding:6px 12px; border-radius:999px; background:#eef2f7; font-size:12px; color:#475569; }
  .msg-chip.unread { background:#fee2e2; color:#b91c1c; font-weight:600; }

  .msg-list { display:grid; gap:14px; }
  .msg-card {
    background:#fff; border:1px solid #e5e7eb; border-radius:12px;
    padding:18px 20px; box-shadow:0 1px 2px rgba(15,23,42,.04);
  }
  .msg-card.unread { border-left:4px solid #ef4444; }
  .msg-head {
    display:flex; justify-content:space-between; align-items:flex-start;
    gap:12px; margin-bottom:8px; flex-wrap:wrap;
  }
  .msg-who  { font-weight:600; color:#0f172a; }
  .msg-meta { font-size:12px; color:#64748b; margin-top:2px; }
  .msg-date { font-size:12px; color:#94a3b8; white-space:nowrap; }
  .msg-subject { margin-top:12px; font-weight:600; color:#0f172a; }
  .msg-body { margin-top:6px; font-size:14px; color:#334155; white-space:pre-wrap; word-break:break-word; }
  .msg-actions { margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; }
  .msg-actions form { display:inline; }
  .msg-btn {
    padding:7px 14px; border-radius:8px; border:1px solid #e2e8f0; background:#f8fafc;
    font-size:13px; cursor:pointer; color:#334155; text-decoration:none; display:inline-block;
  }
  .msg-btn:hover { background:#eef2f7; color:#334155; text-decoration:none; }
  .msg-btn.danger { border-color:#fecaca; color:#b91c1c; background:#fef2f2; }
  .msg-btn.danger:hover { background:#fee2e2; }
  .msg-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
  .msg-flash {
    margin: 8px 0 16px; padding: 10px 14px; border-radius: 8px;
    background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px;
  }
</style>

<?php if ($action_msg): ?>
  <div class="msg-flash"><?= htmlspecialchars($action_msg) ?></div>
<?php endif; ?>

<div class="msg-toolbar">
  <input type="search" id="msg-search" placeholder="Search by name, email, subject, or content…" />
  <span class="msg-chip <?= $unread ? 'unread' : '' ?>"><?= $unread ?> unread</span>
  <button type="button" class="msg-btn" id="msg-filter-unread">Show unread only</button>
  <button type="button" class="msg-btn" id="msg-filter-all" hidden>Show all</button>
</div>

<?php if (!$rows): ?>
  <div class="msg-empty">No messages yet.</div>
<?php else: ?>
  <div class="msg-list" id="msg-list">
    <?php foreach ($rows as $r):
      $is_unread = $r["status"] === "unread";
      $hay = strtolower(($r["name"] ?? "") . " " . ($r["email"] ?? "") . " " . ($r["subject"] ?? "") . " " . ($r["message"] ?? ""));
    ?>
    <article class="msg-card <?= $is_unread ? 'unread' : '' ?>"
             data-unread="<?= $is_unread ? '1' : '0' ?>"
             data-search="<?= htmlspecialchars($hay, ENT_QUOTES) ?>">
      <div class="msg-head">
        <div>
          <div class="msg-who"><?= htmlspecialchars($r["name"]) ?>
            <?php if (!$r["user_id"]): ?><span class="msg-chip">guest</span><?php endif; ?>
          </div>
          <div class="msg-meta">
            <?= htmlspecialchars($r["email"]) ?>
            <?= $r["phone"] ? " · " . htmlspecialchars($r["phone"]) : "" ?>
            · ID #<?= (int)$r["message_id"] ?>
          </div>
        </div>
        <div class="msg-date"><?= htmlspecialchars($r["created_at"]) ?></div>
      </div>

      <div class="msg-subject"><?= htmlspecialchars($r["subject"]) ?></div>
      <div class="msg-body"><?= htmlspecialchars($r["message"]) ?></div>

      <div class="msg-actions">
        <a class="msg-btn" href="mailto:<?= htmlspecialchars($r['email']) ?>?subject=Re: <?= rawurlencode($r['subject']) ?>">
          Reply by email
        </a>
        <form method="post">
          <input type="hidden" name="message_id" value="<?= (int)$r["message_id"] ?>">
          <?php if ($is_unread): ?>
            <input type="hidden" name="action" value="mark_read">
            <button type="submit" class="msg-btn">Mark as read</button>
          <?php else: ?>
            <input type="hidden" name="action" value="mark_unread">
            <button type="submit" class="msg-btn">Mark as unread</button>
          <?php endif; ?>
        </form>
        <form method="post" onsubmit="return confirm('Delete this message permanently?');">
          <input type="hidden" name="message_id" value="<?= (int)$r["message_id"] ?>">
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="msg-btn danger">Delete</button>
        </form>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
  (function () {
    var $search = document.getElementById("msg-search");
    var $list   = document.getElementById("msg-list");
    if (!$list) return;
    var cards = Array.prototype.slice.call($list.querySelectorAll(".msg-card"));
    var $unread = document.getElementById("msg-filter-unread");
    var $all    = document.getElementById("msg-filter-all");
    var unreadOnly = false;

    function apply() {
      var q = ($search.value || "").trim().toLowerCase();
      cards.forEach(function (c) {
        var match    = !q || (c.dataset.search || "").indexOf(q) !== -1;
        var isUnread = c.dataset.unread === "1";
        var show     = match && (!unreadOnly || isUnread);
        c.style.display = show ? "" : "none";
      });
    }

    $search.addEventListener("input", apply);
    $unread.addEventListener("click", function () {
      unreadOnly = true; apply();
      $unread.hidden = true; $all.hidden = false;
    });
    $all.addEventListener("click", function () {
      unreadOnly = false; apply();
      $all.hidden = true; $unread.hidden = false;
    });
  })();
</script>

<?php include(__DIR__ . "/includes/footer.php"); ?>