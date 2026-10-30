async function loadJSON(path) {
    const r = await fetch(path);

    if (!r.ok) {
        throw new Error(path);
    }

    return r.json();
}

function getStored(k, f) {
    try {
        return JSON.parse(localStorage.getItem(k)) || f;
    } catch (e) {
        return f;
    }
}

function save(k, v) {
    localStorage.setItem(k, JSON.stringify(v));
}

function esc(s) {
    return String(s ?? "").replace(/[&<>"']/g, c => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    }[c]));
}

(async () => {
    await CodeMateSession.getCurrentUser();

    const base = await loadJSON("data/mock-moderation.json");

    // Admin decisions are saved locally so they survive page changes.
    let queue = getStored("codemate_moderation", base);

    const itemsEl = document.getElementById("items");
    const filterEl = document.getElementById("moderation-filter");

    const STATUS_LABEL = {
        pending: "Pending",
        kept: "Kept",
        hidden: "Hidden"
    };

    const STATUS_PILL = {
        pending: "pending",
        kept: "",
        hidden: "danger"
    };

    function render() {
        const filter = filterEl ? filterEl.value : "pending";

        const list = filter === "all"
            ? queue
            : queue.filter(x => x.status === filter);

        if (!list.length) {
            itemsEl.innerHTML =
                '<div class="role-card">Nothing to show here.</div>';

            return;
        }

        itemsEl.innerHTML = list.map(x => {
            // Resources are approved rather than "kept".
            const keepLabel = x.type === "Resource"
                ? "Approve"
                : "Keep";

            const actions = x.status === "pending"
                ? `<button class="role-btn primary"
                    data-action="kept"
                    data-id="${esc(x.id)}">${keepLabel}</button>` +
                  `<button class="role-btn danger"
                    data-action="hidden"
                    data-id="${esc(x.id)}">Hide</button>`
                : `<button class="role-btn secondary"
                    data-action="pending"
                    data-id="${esc(x.id)}">Undo</button>`;

            return `
                <div class="role-card" style="margin-bottom: 10px">
                    <span class="role-pill">
                        ${esc(x.type)}
                    </span>

                    <span class="role-pill ${STATUS_PILL[x.status]}">
                        ${STATUS_LABEL[x.status]}
                    </span>

                    <h2 style="margin-top: 8px">
                        ${esc(x.title)}
                    </h2>

                    <p>${esc(x.content)}</p>

                    <p style="margin-top: 8px">
                        <small>
                            ${esc(x.course)} · by ${esc(x.author)}
                        </small>
                    </p>

                    <p style="margin-top: 4px">
                        <small>
                            <strong>Flagged:</strong>
                            ${esc(x.reason)} ·
                            ${esc(x.flaggedBy)} ·
                            ${esc(x.flaggedAt)}
                        </small>
                    </p>

                    <div class="role-actions" style="margin-top: 10px">
                        ${actions}
                    </div>
                </div>
            `;
        }).join("");
    }

    itemsEl.addEventListener("click", e => {
        const btn = e.target.closest("[data-action]");

        if (!btn) {
            return;
        }

        const item = queue.find(
            x => x.id === btn.dataset.id
        );

        if (!item) {
            return;
        }

        item.status = btn.dataset.action;

        save("codemate_moderation", queue);
        render();
    });

    if (filterEl) {
        filterEl.addEventListener("change", render);
    }

    render();
})();
