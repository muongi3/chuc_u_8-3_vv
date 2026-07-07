<?php
$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$count = 0;
foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    
    if (!in_array($ext, ['php', 'css', 'js', 'html'])) continue;
    // Skip this script
    if (basename($path) === 'clean_comments.php') continue;

    $content = file_get_contents($path);
    $original = $content;

    // 1. Remove // =========...
    // Usually it's an empty line of =====
    $content = preg_replace('/^\s*\/\/\s*[=═]{5,}\s*$/m', '', $content);
    
    // 2. Simplify multiline CSS/JS block comments: 
    // /* ==================
    //    Title
    //    ================== */
    // Change to /* Title */
    $content = preg_replace('/\/\*\s*[=═]{5,}\s*(.*?)\s*[=═]{5,}\s*\*\//s', '/* $1 */', $content);

    // 3. Simplify HTML block comments:
    // <!-- ==================
    //      Title
    //      ================== -->
    $content = preg_replace('/<!--\s*[=═]{5,}\s*(.*?)\s*[=═]{5,}\s*-->/s', '<!-- $1 -->', $content);

    // Clean up multiple blank lines that might result from deleting // ======= lines
    $content = preg_replace("/(\r?\n){3,}/", "\n\n", $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        $count++;
        echo "Cleaned: $path\n";
    }
}
echo "Total files cleaned: $count\n";
