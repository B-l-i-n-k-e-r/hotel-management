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
// --- Handle actions (mark read/unread, delete) ---
$action_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"], $_POST["feedback_id"])) {
    $fid    = (int)$_POST["feedback_id"];
    $action = $_POST["action"];

    try {
        if ($action === "mark_read") {
            $pdo->prepare("UPDATE feedback SET status='read' WHERE feedback_id=?")->execute([$fid]);
            $action_msg = "Marked as read.";
        } elseif ($action === "mark_unread") {
            $pdo->prepare("UPDATE feedback SET status='unread' WHERE feedback_id=?")->execute([$fid]);
            $action_msg = "Marked as unread.";
        } elseif ($action === "delete") {
            $pdo->prepare("DELETE FROM feedback WHERE feedback_id=?")->execute([$fid]);
            $action_msg = "Feedback deleted.";
        }
    } catch (Throwable $e) {
        $action_msg = "Action failed: " . $e->getMessage();
    }
}

// --- Fetch all feedback ---
try {
    $rows = $pdo->query("
        SELECT f.*,
               u.user_fname, u.user_lname, u.user_email
        FROM feedback f
        LEFT JOIN users u ON u.user_id = f.user_id
        ORDER BY f.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
    $action_msg = "Could not load feedback: " . $e->getMessage();
}

$unread_count = 0;
foreach ($rows as $r) if (($r["status"] ?? "unread") === "unread") $unread_count++;

// --- Header ---
$admin_page  = "feedback";
$admin_title = "Feedback";
$admin_sub   = $unread_count . " unread · " . count($rows) . " total";
include(__DIR__ . "/includes/header.php");
?>

<style>
  .fb-toolbar { display:flex; gap:10px; align-items:center; margin: 12px 0 20px; flex-wrap: wrap; }
  .fb-toolbar input[type="search"] {
    flex:1; min-width:220px; padding:10px 14px; border:1px solid #e2e8f0;
    border-radius:8px; font-size:14px;
  }
  .fb-chip { padding:6px 12px; border-radius:999px; background:#eef2f7; font-size:12px; color:#475569; }
  .fb-chip.unread { background:#fee2e2; color:#b91c1c; font-weight:600; }

  .fb-list { display:grid; gap:14px; }
  .fb-card {
    background:#fff; border:1px solid #e5e7eb; border-radius:12px;
    padding:18px 20px; box-shadow:0 1px 2px rgba(15,23,42,.04);
  }
  .fb-card.unread { border-left:4px solid #ef4444; }
  .fb-card-head {
    display:flex; justify-content:space-between; align-items:flex-start;
    gap:12px; margin-bottom:8px; flex-wrap:wrap;
  }
  .fb-who { font-weight:600; color:#0f172a; }
  .fb-meta { font-size:12px; color:#64748b; margin-top:2px; }
  .fb-date { font-size:12px; color:#94a3b8; white-space:nowrap; }
  .fb-qa { margin-top:14px; display:grid; gap:10px; }
  .fb-q  { font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; margin-bottom:2px; }
  .fb-a  { font-size:14px; color:#0f172a; white-space:pre-wrap; word-break:break-word; }
  .fb-a.empty { color:#cbd5e1; font-style:italic; }
  .fb-actions { margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; }
  .fb-actions form { display:inline; }
  .fb-btn {
    padding:7px 14px; border-radius:8px; border:1px solid #e2e8f0; background:#f8fafc;
    font-size:13px; cursor:pointer; color:#334155;
  }
  .fb-btn:hover { background:#eef2f7; }
  .fb-btn.danger { border-color:#fecaca; color:#b91c1c; background:#fef2f2; }
  .fb-btn.danger:hover { background:#fee2e2; }
  .fb-empty { text-align:center; padding:60px 20px; color:#94a3b8; }

  .fb-flash {
    margin: 8px 0 16px; padding: 10px 14px; border-radius: 8px;
    background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px;
  }
</style>

<?php if ($action_msg): ?>
  <div class="fb-flash"><?= htmlspecialchars($action_msg) ?></div>
<?php endif; ?>

<div class="fb-toolbar">
  <input type="search" id="fb-search" placeholder="Search by name, email, or any answer…" />
  <span class="fb-chip <?= $unread_count ? 'unread' : '' ?>">
    <?= $unread_count ?> unread
  </span>
  <button type="button" class="fb-btn" id="fb-filter-unread">Show unread only</button>
  <button type="button" class="fb-btn" id="fb-filter-all" hidden>Show all</button>
</div>

<?php if (!$rows): ?>
  <div class="fb-empty">No feedback submitted yet.</div>
<?php else: ?>
  <div class="fb-list" id="fb-list">
    <?php foreach ($rows as $r):
      $is_unread = ($r["status"] ?? "unread") === "unread";
      $name  = $r["user_id"] ? trim(($r["user_fname"] ?? "") . " " . ($r["user_lname"] ?? "")) : "Guest";
      if ($name === "") $name = "Guest";
      $email = $r["user_email"] ?? "";
      $hay   = strtolower($name . " " . $email . " " . ($r["impression"] ?? "") . " " . ($r["first_hear"] ?? "") . " " . ($r["missing_features"] ?? "") . " " . ($r["useful_features"] ?? "") . " " . ($r["ease_of_use"] ?? ""));
    ?>
    <article class="fb-card <?= $is_unread ? 'unread' : '' ?>"
             data-unread="<?= $is_unread ? '1' : '0' ?>"
             data-search="<?= htmlspecialchars($hay, ENT_QUOTES) ?>">
      <div class="fb-card-head">
        <div>
          <div class="fb-who"><?= htmlspecialchars($name) ?>
            <?php if (!$r["user_id"]): ?><span class="fb-chip">guest</span><?php endif; ?>
          </div>
          <div class="fb-meta">
            <?= $email ? htmlspecialchars($email) . " · " : "" ?>
            ID #<?= (int)$r["feedback_id"] ?>
          </div>
        </div>
        <div class="fb-date"><?= htmlspecialchars($r["created_at"]) ?></div>
      </div>

      <div class="fb-qa">
        <div>
          <div class="fb-q">First impression</div>
          <div class="fb-a <?= $r["impression"] === "" ? "empty" : "" ?>"><?= htmlspecialchars($r["impression"] ?: "—") ?></div>
        </div>
        <div>
          <div class="fb-q">How they heard about us</div>
          <div class="fb-a <?= $r["first_hear"] === "" ? "empty" : "" ?>"><?= htmlspecialchars($r["first_hear"] ?: "—") ?></div>
        </div>
        <div>
          <div class="fb-q">Missing on page</div>
          <div class="fb-a <?= $r["missing_features"] === "" ? "empty" : "" ?>"><?= htmlspecialchars($r["missing_features"] ?: "—") ?></div>
        </div>
        <div>
          <div class="fb-q">Recommend score</div>
          <div class="fb-a"><?= (int)$r["recommend"] ?> / 5</div>
        </div>
        <div>
          <div class="fb-q">Most useful feature</div>
          <div class="fb-a <?= $r["useful_features"] === "" ? "empty" : "" ?>"><?= htmlspecialchars($r["useful_features"] ?: "—") ?></div>
        </div>
        <div>
          <div class="fb-q">Ease of use</div>
          <div class="fb-a <?= $r["ease_of_use"] === "" ? "empty" : "" ?>"><?= htmlspecialchars($r["ease_of_use"] ?: "—") ?></div>
        </div>
      </div>

      <div class="fb-actions">
        <form method="post">
          <input type="hidden" name="feedback_id" value="<?= (int)$r["feedback_id"] ?>">
          <?php if ($is_unread): ?>
            <input type="hidden" name="action" value="mark_read">
            <button type="submit" class="fb-btn">Mark as read</button>
          <?php else: ?>
            <input type="hidden" name="action" value="mark_unread">
            <button type="submit" class="fb-btn">Mark as unread</button>
          <?php endif; ?>
        </form>
        <form method="post" onsubmit="return confirm('Delete this feedback permanently?');">
          <input type="hidden" name="feedback_id" value="<?= (int)$r["feedback_id"] ?>">
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="fb-btn danger">Delete</button>
        </form>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
  (function () {
    var $search  = document.getElementById("fb-search");
    var $list    = document.getElementById("fb-list");
    if (!$list) return;
    var cards    = Array.prototype.slice.call($list.querySelectorAll(".fb-card"));
    var $unread  = document.getElementById("fb-filter-unread");
    var $all     = document.getElementById("fb-filter-all");
    var unreadOnly = false;

    function apply() {
      var q = ($search.value || "").trim().toLowerCase();
      cards.forEach(function (c) {
        var match = !q || (c.dataset.search || "").indexOf(q) !== -1;
        var isUnread = c.dataset.unread === "1";
        var show = match && (!unreadOnly || isUnread);
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