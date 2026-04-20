let fileToken = null;

async function loadShare(){

    const params = new URLSearchParams(window.location.search);
    fileToken = params.get("token");

    if(!fileToken){
        document.getElementById("fileName").innerText = "Invalid link";
        return;
    }

    try{

        const res = await fetch(
            "/FileSheild/backend-php/share_info.php?token=" + fileToken
        );

        const data = await res.json();

        if(data.error){
            document.getElementById("fileName").innerText = "File not available";
            return;
        }

        /* set info */
        document.getElementById("fileName").innerText = data.display_name;

        document.getElementById("fileMeta").innerText =
            formatSize(data.file_size) + " • " + data.mime_type;

        /* render preview */
        renderPreview(data.mime_type);

    }
    catch(err){
        console.error(err);
        document.getElementById("fileName").innerText = "Error loading file";
    }
}


/* PREVIEW */

function renderPreview(type){

    const preview = document.getElementById("previewArea");

    const url =
        "/FileSheild/backend-php/share_download.php?token=" + fileToken + "&preview=1";

    /* IMAGE */
    if(type.startsWith("image/")){
        preview.innerHTML = `
            <img src="${url}" style="max-width:100%; border-radius:10px;">
        `;
    }

    /* PDF */
    else if(type === "application/pdf"){
        preview.innerHTML = `
            <iframe src="${url}" style="width:100%; height:500px; border:none;"></iframe>
        `;
    }

    /* VIDEO */
    else if(type.startsWith("video/")){
        preview.innerHTML = `
            <video controls style="max-width:100%; border-radius:10px;">
                <source src="${url}" type="${type}">
                Your browser does not support video.
            </video>
        `;
    }

    /* AUDIO NEW */
    else if(type.startsWith("audio/")){
        preview.innerHTML = `
            <audio controls style="width:100%;">
                <source src="${url}" type="${type}">
                Your browser does not support audio.
            </audio>
        `;
    }

    /* OTHER FILES */
    else{
        preview.innerHTML = `
            <p style="color:gray;">Preview not available for this file type</p>
        `;
    }
}


/* DOWNLOAD */

function downloadFile(){
    window.location.href =
        "/FileSheild/backend-php/share_download.php?token=" + fileToken;
}


/* FORMAT SIZE */

function formatSize(bytes){
    if(bytes < 1024) return bytes + " B";
    if(bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + " KB";
    return (bytes / (1024 * 1024)).toFixed(2) + " MB";
}


loadShare();