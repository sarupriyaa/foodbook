let debounceTimer = null;
let searchOpen = false;
let activeIndex = -1;
let currentResults = [];

function normalize(str) {
    return (str || "").toLowerCase().trim();
}

function highlight(text, q) {
    let lower = text.toLowerCase();
    let index = lower.indexOf(q);
    if (index === -1) return text;

    return (
        text.slice(0, index) +
        `<span class="highlight">${text.slice(index, index + q.length)}</span>` +
        text.slice(index + q.length)
    );
}

function runSearch(query) {
    const q = normalize(query);

    if (!q) {
        document.getElementById("search-results").innerHTML = "";
        searchOpen = false;
        return;
    }

    fetch("search.php?q=" + q)
        .then(res => res.json())
        .then(results => {
            currentResults = results;
            activeIndex = results.length ? 0 : -1;
            searchOpen = results.length > 0;

            let html = "";
            results.forEach((it, idx) => {
                html += `
                <div class="result-item ${idx === activeIndex ? 'active' : ''}" 
                    onclick="selectResult(${it.id})"
                    onmouseenter="activeIndex=${idx}; renderResults()">
                    <div class="title">${highlight(it.title, q)}</div>
                    <div class="desc">${highlight(it.description || '', q)}</div>
                    <div class="cat">${it.category}</div>
                </div>`;
            });

            document.getElementById("search-results").innerHTML = html;
        });
}

function renderResults() {
    let items = document.querySelectorAll(".result-item");
    items.forEach((item, idx) => {
        item.classList.toggle("active", idx === activeIndex);
    });
}

function onSearchInput(value) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => runSearch(value), 250);
}

function handleKey(e) {
    if (!searchOpen) return;

    if (e.key === "ArrowDown") {
        activeIndex = Math.min(currentResults.length - 1, activeIndex + 1);
        renderResults();
    } else if (e.key === "ArrowUp") {
        activeIndex = Math.max(0, activeIndex - 1);
        renderResults();
    } else if (e.key === "Enter") {
        if (currentResults[activeIndex])
            showCard(currentResults[activeIndex]);
    }
}

function selectResult(id) {
    const item = currentResults.find(r => r.id == id);
    if (item) showCard(item);
}

function showCard(recipe) {
    alert("Selected: " + recipe.title);
}
