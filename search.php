<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Connection Failed");

// API MODE (AJAX) 
if (isset($_GET['ajax'])) {

    $q = isset($_GET['q']) ? trim($_GET['q']) : "";
    $safe = $conn->real_escape_string($q);

    $sql = "SELECT id, title, description, category 
            FROM recipes
            WHERE status='approved'
            AND (title LIKE '%$safe%' 
            OR description LIKE '%$safe%' 
            OR category LIKE '%$safe%')
            LIMIT 12";

    $result = $conn->query($sql);

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }

    header("Content-Type: application/json");
    echo json_encode($rows);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Search Recipes</title>

<link rel="stylesheet" href="search.css">

</head>
<body>

<div class="search-container">
<div class="search-box">
  <span class="icon-left">🔍</span>
  <input id="search" placeholder="Search recipes...">
  <span class="icon-clear" id="clearBtn">✕</span>
</div>
<div id="results" class="panel" style="display:none;"></div>
  <p id="count" class="count"></p>
</div>

<script>
const input   = document.getElementById("search");
const panel   = document.getElementById("results");
const countEl = document.getElementById("count");
const clearBtn = document.getElementById("clearBtn");

let debounce = null;
let items = [];

function highlight(text, q){
  q = q.toLowerCase();
  const low = (text || "").toLowerCase();
  const idx = low.indexOf(q);
  if (idx === -1) return text;
  return text.slice(0, idx) +
         `<span class="match">${text.slice(idx, idx+q.length)}</span>` +
         text.slice(idx+q.length);
}

input.addEventListener("input", () => {
  clearTimeout(debounce);
  const q = input.value.trim();

  clearBtn.style.display = q ? "block" : "none";

  if (!q){
    panel.style.display = "none";
    countEl.textContent = "";
    return;
  }

  debounce = setTimeout(() => runSearch(q), 250);
});

async function runSearch(q){
  const res = await fetch(`search.php?ajax=1&q=` + encodeURIComponent(q));
  items = await res.json();

  if (!items.length){
    panel.style.display = "none";
    countEl.textContent = "No results";
    return;
  }

  panel.innerHTML = items.map((it)=>`
    <div class="item" data-id="${it.id}">
      <strong>${highlight(it.title, q)}</strong><br>
      <small>${highlight(it.description ?? "", q)}</small><br>
      <span style="color:#777">${it.category ?? ""}</span>
    </div>
  `).join("");

  panel.style.display = "block";
  countEl.textContent = `Showing ${items.length} result(s) for "${q}"`;
}

// click select -> go to recipe page
panel.addEventListener("mousedown", e => {
  const box = e.target.closest(".item");
  if (!box) return;
  const id = box.dataset.id;
  window.location = "recipe.php?id=" + id;
});

// clear button
clearBtn.addEventListener("click", () => {
  input.value = "";
  clearBtn.style.display = "none";
  panel.style.display = "none";
  countEl.textContent = "";
  input.focus();
});
</script>
</body>
</html>
    