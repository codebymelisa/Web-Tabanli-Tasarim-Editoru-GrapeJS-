<?php
$host = "db";
$dbname = "grapes_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

if (isset($_GET["action"]) && $_GET["action"] === "save") {
    header("Content-Type: application/json; charset=utf-8");

    if (!$pdo) {
        echo json_encode([
            "status" => "error",
            "message" => "Veritabanı bağlantısı kurulamadı."
        ]);
        exit;
    }

    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!$input || !isset($input["html"]) || !isset($input["css"])) {
        echo json_encode([
            "status" => "error",
            "message" => "HTML ve CSS verisi alınamadı."
        ]);
        exit;
    }

    $data = json_encode([
        "html" => $input["html"],
        "css" => $input["css"]
    ], JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("INSERT INTO page_data (data) VALUES (:data)");
        $stmt->execute(["data" => $data]);

        echo json_encode([
            "status" => "success",
            "message" => "Tasarım başarıyla MySQL'e kaydedildi."
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Kayıt hatası: " . $e->getMessage()
        ]);
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>CloudBuilder AI - GrapesJS Studio</title>

    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Arial, Helvetica, sans-serif;
            background: #1f2937;
        }

        .topbar {
            height: 70px;
            background: #111827;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid #374151;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
        }

        .save-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 22px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .save-btn:hover {
            background: #059669;
        }

        .main {
            display: flex;
            width: 100%;
            height: calc(100vh - 70px);
        }

        .editor-area {
            flex: 1;
            min-width: 0;
            height: 100%;
            background: #1f2937;
            padding: 24px;
        }

        #gjs {
            height: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .right-panel {
            width: 300px;
            height: 100%;
            background: #111827;
            color: white;
            border-left: 1px solid #374151;
            overflow-y: auto;
        }

        .right-panel h2 {
            margin: 0;
            padding: 22px;
            font-size: 20px;
            border-bottom: 1px solid #374151;
        }

        #blocks {
            padding: 16px;
        }

        .gjs-block {
            width: 100% !important;
            min-height: 74px !important;
            margin: 0 0 14px 0 !important;
            padding: 14px !important;
            border-radius: 10px !important;
            background: #1f2937 !important;
            color: white !important;
            border: 1px solid #4b5563 !important;
            box-shadow: none !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            cursor: grab !important;
        }

        .gjs-block:hover {
            background: #2563eb !important;
            border-color: #60a5fa !important;
        }

        .gjs-block-label {
            font-size: 15px !important;
        }

        .gjs-editor {
            height: 100% !important;
        }

        .gjs-cv-canvas {
            width: 100% !important;
            height: 100% !important;
            top: 0 !important;
            left: 0 !important;
        }

        .gjs-frame-wrapper {
            background: white !important;
        }

        .gjs-pn-panels {
            display: none !important;
        }
    </style>
</head>

<body>

<div class="topbar">
    <div class="brand">☁️ CloudBuilder AI - GrapesJS Studio</div>
    <button class="save-btn" onclick="saveDesign()">☁️ Tasarımı MySQL'e Kaydet</button>
</div>

<div class="main">
    <div class="editor-area">
        <div id="gjs">
            <section style="padding: 70px 40px; text-align: center;">
                <h1 style="font-size: 34px; margin-bottom: 14px;">Sürükle Bırak Tasarım Alanı</h1>
                <p style="font-size: 18px; color: #64748b;">Sağ taraftaki panelden blokları buraya sürükleyip bırakabilirsiniz.</p>
            </section>
        </div>
    </div>

    <aside class="right-panel">
        <h2>Tasarım Araçları</h2>
        <div id="blocks"></div>
    </aside>
</div>

<script src="https://unpkg.com/grapesjs"></script>

<script>
    const editor = grapesjs.init({
        container: '#gjs',
        height: '100%',
        width: '100%',
        fromElement: true,
        storageManager: false,
        noticeOnUnload: false,

        panels: {
            defaults: []
        },

        blockManager: {
            appendTo: '#blocks',
            blocks: [
                {
                    id: 'section',
                    label: '📦 Bölüm',
                    content: `
                        <section style="padding:50px; background:#f3f4f6; text-align:center; border-radius:12px; margin:20px;">
                            <h2 style="font-size:30px; margin-bottom:12px;">Yeni Bölüm</h2>
                            <p style="font-size:18px; color:#4b5563;">Bu bölümün içeriğini düzenleyebilirsiniz.</p>
                        </section>
                    `
                },
                {
                    id: 'text',
                    label: '✍️ Metin',
                    content: `
                        <p style="font-size:20px; line-height:1.7; color:#111827; padding:20px;">
                            Buraya kendi metninizi yazabilirsiniz.
                        </p>
                    `
                },
                {
                    id: 'button',
                    label: '🔘 Buton',
                    content: `
                        <div style="padding:20px; text-align:center;">
                            <a href="#" style="display:inline-block; padding:14px 28px; background:#2563eb; color:white; text-decoration:none; border-radius:8px; font-weight:bold;">
                                Buton
                            </a>
                        </div>
                    `
                },
                {
                    id: 'image',
                    label: '🖼️ Resim',
                    content: `
                        <div style="padding:20px; text-align:center;">
                            <img src="https://picsum.photos/700/320" style="max-width:100%; border-radius:12px;">
                        </div>
                    `
                }
            ]
        },

        canvas: {
            styles: [
                'https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css'
            ]
        }
    });

    editor.on('load', function () {
        const body = editor.Canvas.getBody();

        body.style.minHeight = '100vh';
        body.style.background = '#ffffff';
        body.style.padding = '0';
        body.style.margin = '0';

        editor.refresh();
    });

    function saveDesign() {
        const htmlCode = editor.getHtml();
        const cssCode = editor.getCss();

        fetch('index.php?action=save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                html: htmlCode,
                css: cssCode
            })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.status === 'success') {
                alert('🎉 ' + data.message);
            } else {
                alert('❌ HATA: ' + data.message);
            }
        })
        .catch(function (error) {
            console.error(error);
            alert('MySQL senkronizasyon hatası!');
        });
    }
</script>

</body>
</html>
EOFcat << 'EOF' > index.php
<?php
$host = "db";
$dbname = "grapes_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

if (isset($_GET["action"]) && $_GET["action"] === "save") {
    header("Content-Type: application/json; charset=utf-8");

    if (!$pdo) {
        echo json_encode([
            "status" => "error",
            "message" => "Veritabanı bağlantısı kurulamadı."
        ]);
        exit;
    }

    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!$input || !isset($input["html"]) || !isset($input["css"])) {
        echo json_encode([
            "status" => "error",
            "message" => "HTML ve CSS verisi alınamadı."
        ]);
        exit;
    }

    $data = json_encode([
        "html" => $input["html"],
        "css" => $input["css"]
    ], JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("INSERT INTO page_data (data) VALUES (:data)");
        $stmt->execute(["data" => $data]);

        echo json_encode([
            "status" => "success",
            "message" => "Tasarım başarıyla MySQL'e kaydedildi."
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Kayıt hatası: " . $e->getMessage()
        ]);
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>CloudBuilder AI - GrapesJS Studio</title>

    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Arial, Helvetica, sans-serif;
            background: #1f2937;
        }

        .topbar {
            height: 70px;
            background: #111827;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid #374151;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
        }

        .save-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 22px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .save-btn:hover {
            background: #059669;
        }

        .main {
            display: flex;
            width: 100%;
            height: calc(100vh - 70px);
        }

        .editor-area {
            flex: 1;
            min-width: 0;
            height: 100%;
            background: #1f2937;
            padding: 24px;
        }

        #gjs {
            height: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .right-panel {
            width: 300px;
            height: 100%;
            background: #111827;
            color: white;
            border-left: 1px solid #374151;
            overflow-y: auto;
        }

        .right-panel h2 {
            margin: 0;
            padding: 22px;
            font-size: 20px;
            border-bottom: 1px solid #374151;
        }

        #blocks {
            padding: 16px;
        }

        .gjs-block {
            width: 100% !important;
            min-height: 74px !important;
            margin: 0 0 14px 0 !important;
            padding: 14px !important;
            border-radius: 10px !important;
            background: #1f2937 !important;
            color: white !important;
            border: 1px solid #4b5563 !important;
            box-shadow: none !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            cursor: grab !important;
        }

        .gjs-block:hover {
            background: #2563eb !important;
            border-color: #60a5fa !important;
        }

        .gjs-block-label {
            font-size: 15px !important;
        }

        .gjs-editor {
            height: 100% !important;
        }

        .gjs-cv-canvas {
            width: 100% !important;
            height: 100% !important;
            top: 0 !important;
            left: 0 !important;
        }

        .gjs-frame-wrapper {
            background: white !important;
        }

        .gjs-pn-panels {
            display: none !important;
        }
    </style>
</head>

<body>

<div class="topbar">
    <div class="brand">☁️ CloudBuilder AI - GrapesJS Studio</div>
    <button class="save-btn" onclick="saveDesign()">☁️ Tasarımı MySQL'e Kaydet</button>
</div>

<div class="main">
    <div class="editor-area">
        <div id="gjs">
            <section style="padding: 70px 40px; text-align: center;">
                <h1 style="font-size: 34px; margin-bottom: 14px;">Sürükle Bırak Tasarım Alanı</h1>
                <p style="font-size: 18px; color: #64748b;">Sağ taraftaki panelden blokları buraya sürükleyip bırakabilirsiniz.</p>
            </section>
        </div>
    </div>

    <aside class="right-panel">
        <h2>Tasarım Araçları</h2>
        <div id="blocks"></div>
    </aside>
</div>

<script src="https://unpkg.com/grapesjs"></script>

<script>
    const editor = grapesjs.init({
        container: '#gjs',
        height: '100%',
        width: '100%',
        fromElement: true,
        storageManager: false,
        noticeOnUnload: false,

        panels: {
            defaults: []
        },

        blockManager: {
            appendTo: '#blocks',
            blocks: [
                {
                    id: 'section',
                    label: '📦 Bölüm',
                    content: `
                        <section style="padding:50px; background:#f3f4f6; text-align:center; border-radius:12px; margin:20px;">
                            <h2 style="font-size:30px; margin-bottom:12px;">Yeni Bölüm</h2>
                            <p style="font-size:18px; color:#4b5563;">Bu bölümün içeriğini düzenleyebilirsiniz.</p>
                        </section>
                    `
                },
                {
                    id: 'text',
                    label: '✍️ Metin',
                    content: `
                        <p style="font-size:20px; line-height:1.7; color:#111827; padding:20px;">
                            Buraya kendi metninizi yazabilirsiniz.
                        </p>
                    `
                },
                {
                    id: 'button',
                    label: '🔘 Buton',
                    content: `
                        <div style="padding:20px; text-align:center;">
                            <a href="#" style="display:inline-block; padding:14px 28px; background:#2563eb; color:white; text-decoration:none; border-radius:8px; font-weight:bold;">
                                Buton
                            </a>
                        </div>
                    `
                },
                {
                    id: 'image',
                    label: '🖼️ Resim',
                    content: `
                        <div style="padding:20px; text-align:center;">
                            <img src="https://picsum.photos/700/320" style="max-width:100%; border-radius:12px;">
                        </div>
                    `
                }
            ]
        },

        canvas: {
            styles: [
                'https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css'
            ]
        }
    });

    editor.on('load', function () {
        const body = editor.Canvas.getBody();

        body.style.minHeight = '100vh';
        body.style.background = '#ffffff';
        body.style.padding = '0';
        body.style.margin = '0';

        editor.refresh();
    });

    function saveDesign() {
        const htmlCode = editor.getHtml();
        const cssCode = editor.getCss();

        fetch('index.php?action=save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                html: htmlCode,
                css: cssCode
            })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.status === 'success') {
                alert('🎉 ' + data.message);
            } else {
                alert('❌ HATA: ' + data.message);
            }
        })
        .catch(function (error) {
            console.error(error);
            alert('MySQL senkronizasyon hatası!');
        });
    }
</script>

</body>
</html>
