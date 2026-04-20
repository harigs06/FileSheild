let files = [];
let folders = [];

let allFiles = [];
let allFolders = [];

let currentFolder = null;


/* ================= LOAD DASHBOARD ================= */

async function loadDashboard() {
    const token = localStorage.getItem("auth_token");

    if (!token) {
        alert("Please login");
        window.location.href = "/FileShield/frontend/signin.html";
        return;
    }

    const params = new URLSearchParams(window.location.search);
    currentFolder = params.get("folder");

    try {
        const response = await fetch(
            "/FileSheild/backend-php/dashboard_data.php?folder=" + (currentFolder || ""),
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ token })
            }
        );

        const data = await response.json();

        files = data.files || [];
        folders = data.folders || [];

        allFiles = [...files];
        allFolders = [...folders];

        renderBreadcrumb(data.path || []);
        renderFiles(files);
        renderFolders(folders);

    } catch (err) {
        console.error("Dashboard error:", err);
    }
}

/* ================= BREADCRUMB ================= */

function renderBreadcrumb(path) {
    const container = document.getElementById("breadcrumb");

    let html = `<span style="cursor:pointer" onclick="goHome()">Home</span>`;

    path.forEach(p => {
        html += ` > <span style="cursor:pointer" onclick="openFolder(${p.id})">${p.name}</span>`;
    });

    container.innerHTML = html;
}

function goHome() {
    window.location.href = "your_uploads.html";
}

/* ================= FILES ================= */

function renderFiles(list) {
    const container = document.getElementById("filesList");
    container.innerHTML = "";

    if (list.length === 0) {
        container.innerHTML = "<p>No files found</p>";
        return;
    }

    list.forEach(file => {
        const card = document.createElement("div");
        card.className = "card";

        card.innerHTML = `
            <div class="icon">📄</div>
            <div class="name">${file.display_name}</div>

            <div class="buttons">
                <button class="open" onclick="openFile('${file.share_token}')">Open</button>
                <button class="download" onclick="downloadFile(${file.id})">Download</button>
                <button class="share" onclick="shareFile('${file.share_token}')">Share</button>
                <button class="delete" onclick="deleteFile(${file.id})">Delete</button>
            </div>
        `;

        container.appendChild(card);
    });
}

/* ================= FOLDERS ================= */

function renderFolders(list) {
    const container = document.getElementById("foldersList");
    container.innerHTML = "";

    if (list.length === 0) {
        container.innerHTML = "<p>No folders found</p>";
        return;
    }

    list.forEach(folder => {
        const card = document.createElement("div");
        card.className = "card";

        card.innerHTML = `
            <div class="icon">📁</div>
            <div class="name">${folder.folder_name}</div>

            <div class="buttons">
                <button class="open" onclick="openFolder(${folder.id})">Open</button>
                <button class="delete" onclick="deleteFolder(${folder.id})">Delete</button>
                <button class="share" onclick="shareFolder('${folder.share_token}')">Share</button>
                
            </div>
        `;

        container.appendChild(card);
    });
}

/* ================= NAVIGATION ================= */

function openFolder(id) {
    window.location.href = "your_uploads.html?folder=" + id;
}

/* ================= DOWNLOAD ================= */

function downloadFile(id) {
    const token = localStorage.getItem("auth_token");

    if (!token) {
        alert("Login required");
        return;
    }

    window.location.href =
        "/FileSheild/backend-php/download.php?id=" + id + "&token=" + encodeURIComponent(token);
}

/* ================= OPEN FILE ================= */

function openFile(token) {
    if (!token) {
        alert("Invalid token");
        return;
    }

    const url = "share.html?token=" + encodeURIComponent(token);
    window.open(url, "_parent");
}

/* ================= SHARE ================= */

function shareFile(token) {
    if (!token) {
        alert("Invalid token");
        return;
    }

    const link =
        "https://colin-flossy-anissa.ngrok-free.dev/FileSheild/frontend/share.html?token=" + encodeURIComponent(token);

    navigator.clipboard.writeText(link)
        .then(() => {
            alert("Share link copied!\n" + link);
        })
        .catch(() => {
            alert("Failed to copy link");
        });
}

function shareFolder(token){
    const link =
        "https://colin-flossy-anissa.ngrok-free.dev/FileSheild/frontend/folder_share.html?token=" 
        + encodeURIComponent(token);

    navigator.clipboard.writeText(link)
        .then(() => alert("Folder link copied!\n" + link))
        .catch(() => alert("Failed to copy link"));
}

/* ================= DELETE FILE ================= */

async function deleteFile(id) {
    const token = localStorage.getItem("auth_token");

    if (!confirm("Delete this file?")) return;

    try {
        const res = await fetch("/FileSheild/backend-php/delete_file.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id, token })
        });

        const data = await res.json();

        if (data.success) {
            loadDashboard();
        } else {
            alert(data.error);
        }

    } catch (err) {
        console.error("Delete file error:", err);
    }
}

/* ================= DELETE FOLDER ================= */

async function deleteFolder(id) {
    const token = localStorage.getItem("auth_token");

    if (!confirm("Delete this folder and its contents?")) return;

    try {
        const res = await fetch("/FileSheild/backend-php/delete_folder.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id, token })
        });

        const data = await res.json();

        if (data.success) {
            loadDashboard();
        } else {
            alert(data.error);
        }

    } catch (err) {
        console.error("Delete folder error:", err);
    }
}

/* ================= SEARCH ================= */

document.addEventListener("DOMContentLoaded", () => {
    const fileSearch = document.getElementById("searchFile");
    const folderSearch = document.getElementById("searchFolder");

    if (fileSearch) {
        fileSearch.addEventListener("keyup", () => {
            const value = fileSearch.value.toLowerCase();
            renderFiles(
                allFiles.filter(f =>
                    f.display_name.toLowerCase().includes(value)
                )
            );
        });
    }

    if (folderSearch) {
        folderSearch.addEventListener("keyup", () => {
            const value = folderSearch.value.toLowerCase();
            renderFolders(
                allFolders.filter(f =>
                    f.folder_name.toLowerCase().includes(value)
                )
            );
        });
    }

    loadDashboard();
});