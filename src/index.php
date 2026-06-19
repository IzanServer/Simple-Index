<?php
$baseDir = realpath(__DIR__);
$currentDir = realpath($baseDir . '/' . ($_GET['path'] ?? ''));
if ($currentDir === false || strpos($currentDir, $baseDir) !== 0) {
    http_response_code(403);
    exit("Access denied");
}

if (is_file($currentDir)) {

    $size = filesize($currentDir);

    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($currentDir) . '"');
    header('Content-Length: ' . $size);
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=3600');

    readfile($currentDir);
    exit;
}


if ($currentDir === false || strpos($currentDir, $baseDir) !== 0) {
    http_response_code(403);
    exit("Access denied");
}

$relativePath = str_replace($baseDir, '', $currentDir);
if (is_file($currentDir)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($currentDir) . '"');
    readfile($currentDir);
    exit;
}

$items = scandir($currentDir);

function formatDate($file) {
    return date("Y-m-d H:i", filemtime($file));
}

function formatSize($bytes) {
    if ($bytes < 0) return "-";
    if ($bytes >= 1073741824) return round($bytes/1073741824,2)." GB";
    if ($bytes >= 1048576) return round($bytes/1048576,2)." MB";
    if ($bytes >= 1024) return round($bytes/1024,2)." KB";
    return $bytes." B";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Index of <?php echo $relativePath ?: '/'; ?></title>

<style>
html { background: #edeff0; }

.content {
    max-width: 1000px;
    margin: 35px auto;
    font-family: Lato, system-ui, Arial;
    padding: 0 1em;
}

h1 { margin-bottom: 32px; word-break: break-all; }

#table-list {
    overflow-x: auto;
    background: white;
    margin: 32px 0;
    box-shadow: 0 0.55rem 1.25rem rgb(0 65 98 / 4%);
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 0.88em;
}

tr:hover:not(:first-child) {
    background-color: rgb(220 220 220);
}

th, td {
    padding: 0.65em;
    text-align: left;
}

td:nth-child(3) {
    text-align: right;
    color: #666;
}

tbody tr:nth-child(odd) {
    background-color: #f8fafb;
}

a {
    color: #0047AB;
    text-decoration: none;
}

a:hover {
    color: #0096FF;
    text-decoration: underline;
}

.icon {
    vertical-align: middle;
    padding-right: 8px;
    height: 18px;
}

address {
    color: rgb(100,100,100);
    font-size: 0.8em;
}
  
@media (prefers-color-scheme: dark) {

    html {
        background: #0f172a;
    }

    .content {
        color: #e2e8f0;
    }

    #table-list {
        background: #020617;
        box-shadow: none;
    }

    tbody tr:nth-child(odd) {
        background-color: #020617;
    }

    tr:hover:not(:first-child) {
        background-color: #1e293b;
    }

    th, td {
        color: #cbd5f5;
    }

    td:nth-child(3) {
        color: #94a3b8;
    }

    a {
        color: #38bdf8;
    }

    a:hover {
        color: #7dd3fc;
    }

    h1 {
        color: #e2e8f0 !important;
    }

    address {
        color: #64748b;
    }

    .icon {
        filter: brightness(2) grayscale(1);
    }
}

#theme-toggle {
    position: fixed;
    top: 15px;
    right: 20px;
    background: #ffffff;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 16px;
    z-index: 999;
}
  
</style>

</head>
<body>
  
<div class="content">
<h1>Index of <?php echo $relativePath ?: '/'; ?></h1>

<div id="table-list">
<table>
<thead>
<tr>
<th>Name</th>
<th>Last Modified</th>
<th>Size</th>
</tr>
</thead>
<tbody>

<?php
// Back button
if ($relativePath) {
    $parent = dirname(trim($relativePath, '/'));
    echo "<tr>
        <td><a href='?path=$parent'>
        <img class='icon' src='./_autoindex/corner-left-up.svg'> Parent Directory</a></td>
        <td></td><td></td>
    </tr>";
}

foreach (($items ?: []) as $item) {

    if ($item === '.' || $item === '..') continue;

    // hide _autoindex folder, php files and files that start with .
    if (str_starts_with($item, '.')) continue;
  
  	if ($item === '_autoindex') continue;
  
    if (pathinfo($item, PATHINFO_EXTENSION) === 'php') continue;

    $fullPath = $currentDir . '/' . $item;
    $url = ($relativePath ? trim($relativePath, '/') . '/' : '') . $item;
	$url = trim($url, '/');
	// hide LICENSE only in root
	if ($currentDir === $baseDir && $item === 'LICENSE') continue;
    if (is_dir($fullPath)) {
        echo "<tr>
            <td>
                <a href='?path=$url'>
                <img class='icon' src='./_autoindex/folder-fill.svg'>
                $item
                </a>
            </td>
            <td>".formatDate($fullPath)."</td>
            <td>-</td>
        </tr>";
    } else {
		echo "<tr>
    		<td>
        		<a href='?path=" . rawurlencode($url) . "'>
        		<img class='icon' src='./_autoindex/file.svg'>
        		$item
        		</a>
    		</td>
    	<td>" . formatDate($fullPath) . "</td>
    	<td>" . formatSize(filesize($fullPath)) . "</td>
	</tr>";
    }
}
?>

</tbody>
</table>
</div>

  <address>Made by <a href="https://izanserver.com">izanserver.com</a></address>
</div>
  
</body>
</html>
