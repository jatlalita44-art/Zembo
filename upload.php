<?php
// File Upload Handler
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['file']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            $downloadLink = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $targetFile;
            echo json_encode(["status"=>"success","link"=>$downloadLink,"name"=>basename($fileName)]);
        } else {
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Upload Premium UI</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(120deg,#1f1c2c,#928dab);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 50px 20px;
    min-height: 100vh;
}

/* Header */
h2 {
    font-size: 32px;
    margin-bottom: 30px;
    color: #ffda79;
    text-shadow: 0 0 10px #ffda79,0 0 20px #ffda79;
    animation: glow 2s infinite alternate;
}
@keyframes glow {0%{text-shadow:0 0 5px #ffda79;}100%{text-shadow:0 0 25px #ffda79,0 0 50px #ffda79;}}

/* File List Preview */
#fileNames {margin-bottom:20px; width:450px;}
.file-name {background:rgba(0,0,0,0.4);padding:10px 15px;border-radius:10px;margin-bottom:8px; text-align:center; animation: fadeIn 0.5s ease;}
@keyframes fadeIn {0%{opacity:0; transform:translateY(20px);}100%{opacity:1; transform:translateY(0);}}

/* Upload Box */
.upload-box {
    border: 3px dashed #00f2fe;
    border-radius: 20px;
    width: 450px;
    padding: 40px 20px;
    text-align: center;
    background: rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    position: relative;
}
.upload-box.dragover {
    border-color: #ff512f;
    background: rgba(255,81,47,0.1);
    box-shadow:0 0 30px #ff512f,0 0 60px #dd2476;
    animation: pulse 0.8s infinite alternate;
}
@keyframes pulse {0%{transform:scale(1);}50%{transform:scale(1.03);}100%{transform:scale(1);}}

input[type="file"]{display:none;}
.choose-btn, .upload-btn {
    display: inline-block;
    padding: 14px 28px;
    border-radius: 30px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    margin: 10px;
}
.choose-btn {background: linear-gradient(45deg,#4facfe,#00f2fe); color:#000;}
.upload-btn {background: linear-gradient(45deg,#ff512f,#dd2476); color:#fff; border:none;}
.choose-btn:hover, .upload-btn:hover {transform: scale(1.1); box-shadow:0 0 20px #fff;}
.choose-btn:active, .upload-btn:active {transform: scale(0.95);}

/* Progress bar */
.file-item {background: rgba(0,0,0,0.5); padding: 15px 20px; border-radius:12px; margin:15px 0; text-align:center; animation: fadeIn 0.6s;}
.progress-container {margin-top:10px; width:100%; height:20px; background:rgba(255,255,255,0.1); border-radius:10px; overflow:hidden;}
.progress-bar {height:100%; width:0%; background:linear-gradient(90deg,#ff512f,#dd2476); transition:width 0.4s ease;}

/* File link */
.file-link {margin-top:10px; display:flex; justify-content:center; gap:15px; align-items:center;}
.file-link a {color:#00f2fe; font-weight:bold; text-decoration:none; transition:0.3s;}
.file-link a:hover {text-shadow:0 0 10px #00f2fe;}
.copy-btn {background:#00f2fe;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:14px;transition:all 0.3s;}
.copy-btn:hover {background:#ffda79;box-shadow:0 0 15px #ffda79;transform:scale(1.1);}

/* Success popup */
#successPopup {position:fixed;top:30px;left:50%;transform:translateX(-50%);background:rgba(0,255,0,0.9);color:#000;padding:15px 25px;border-radius:20px;font-weight:bold;display:none;text-align:center;box-shadow:0 0 20px #00ff00;animation: popupFade 0.5s;}
@keyframes popupFade{0%{opacity:0; transform:translate(-50%,-20px) scale(0.8);}100%{opacity:1;transform:translate(-50%,0) scale(1);}}
</style>
</head>
<body>
<h2>🚀 Premium File Upload</h2>

<div id="successPopup">✅ Upload Successful!</div>



<form id="uploadForm" class="upload-box">
    <p>फाइलें Drag & Drop करें या क्लिक करके चुनें</p>
    <input type="file" id="fileInput" multiple>
    <label for="fileInput" class="choose-btn">📂 फाइल चुनें</label><br>
    <button type="submit" class="upload-btn">⬆ अपलोड करें</button>
</form>
<div id="fileNames"></div>

<div id="fileList"></div>

<script>
const fileInput = document.getElementById("fileInput");
const fileList = document.getElementById("fileList");
const uploadForm = document.getElementById("uploadForm");
const fileNames = document.getElementById("fileNames");
const successPopup = document.getElementById("successPopup");

// Show file names on select
function showFileNames(){
    const files = fileInput.files;
    fileNames.innerHTML = "";
    Array.from(files).forEach(file=>{
        let div = document.createElement("div");
        div.className = "file-name";
        div.textContent = file.name;
        fileNames.appendChild(div);
    });
}

// Drag & Drop
uploadForm.addEventListener("dragover", e => {
    e.preventDefault(); 
    uploadForm.classList.add("dragover");
});
uploadForm.addEventListener("dragleave", () => {
    uploadForm.classList.remove("dragover");
});
uploadForm.addEventListener("drop", e => {
    e.preventDefault(); 
    uploadForm.classList.remove("dragover"); 
    fileInput.files = e.dataTransfer.files; 
    showFileNames();
});

// On file select
fileInput.addEventListener("change", showFileNames);

// Upload
uploadForm.addEventListener("submit", e => {
    e.preventDefault();
    if(fileInput.files.length === 0){
        alert("कृपया फाइल चुनें!"); 
        return;
    }

    // Animate fileNames disappear
    fileNames.style.transition = "all 0.5s ease";
    fileNames.style.opacity = 0;
    setTimeout(() => { fileNames.style.display = "none"; }, 500);

    // Show fileList container
    fileList.style.display = "block";
    fileList.style.opacity = 0;
    fileList.style.transition = "all 0.5s ease";

    Array.from(fileInput.files).forEach(file => {
        const fileDiv = document.createElement("div");
        fileDiv.className = "file-item";
        fileDiv.style.opacity = 0; // start hidden for animation
        fileDiv.innerHTML = `<p>📄 ${file.name}</p>
            <div class="progress-container"><div class="progress-bar"></div></div>
            <div class="file-link"></div>`;
        fileList.appendChild(fileDiv);

        // Animate each fileDiv
        setTimeout(() => { fileDiv.style.opacity = 1; }, 100);

        uploadFile(file, fileDiv);
    });

    // Animate fileList fade-in
    setTimeout(() => { fileList.style.opacity = 1; }, 500);
});

function uploadFile(file, fileDiv){
    const formData = new FormData();
    formData.append("file", file);
    const xhr = new XMLHttpRequest();
    const progressBar = fileDiv.querySelector(".progress-bar");
    const fileLinkDiv = fileDiv.querySelector(".file-link");

    xhr.open("POST", "", true);

    xhr.upload.onprogress = e => {
        if(e.lengthComputable){
            progressBar.style.width = Math.round((e.loaded / e.total) * 100) + "%";
        }
    };

    xhr.onload = function(){
        if(xhr.status === 200){
            const res = JSON.parse(xhr.responseText);
            if(res.status === "success"){
                fileLinkDiv.innerHTML = `<a href="${res.link}" target="_blank">${res.name}</a>
                <button class="copy-btn" onclick="copyLink('${res.link}')">लिंक कॉपी करें</button>`;
                showSuccessPopup();
            } else {
                fileLinkDiv.innerHTML = "<span style='color:red;'>❌ अपलोड विफल</span>";
            }
        }
    };

    xhr.send(formData);
}

function copyLink(link){
    navigator.clipboard.writeText(link).then(() => {
        alert("✅ लिंक कॉपी हो गया!");
    });
}

function showSuccessPopup(){
    successPopup.style.display = "block"; 
    setTimeout(() => { successPopup.style.display = "none"; }, 2000);
}
</script>

</body>
</html>
