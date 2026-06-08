<?php
/**
 * CLI: Convert .doc to HTML via COM
 * Usage: php convert-word-cli.php <input.doc> <output_dir>
 */

if ($argc < 3) {
    echo json_encode(['error' => 'Usage: php convert-word-cli.php <input> <outdir>']);
    exit(1);
}

$inputFile = $argv[1];
$outputDir = $argv[2];

if (!file_exists($inputFile)) {
    echo json_encode(['error' => 'File not found']);
    exit(1);
}

$safeInput = dirname($inputFile) . '\\safe.doc';
@unlink($safeInput);
if (!copy($inputFile, $safeInput)) {
    echo json_encode(['error' => 'Cannot copy file']);
    exit(1);
}

$word = null;
$doc = null;

try {
    $word = new COM("Word.Application");
    $word->Visible = false;
    $word->DisplayAlerts = 0;

    $doc = $word->Documents->Open($safeInput, false, false, false);

    $htmlPath = $outputDir . '\\out.html';
    $doc->SaveAs2($htmlPath, 10);

    try { $doc->Close(0); } catch (\Throwable $e) {}
    $doc = null;

    $html = @file_get_contents($htmlPath);
    if (!$html) {
        echo json_encode(['error' => 'Cannot read output']);
        exit(1);
    }

    @unlink($htmlPath);
    @unlink($safeInput);

    echo json_encode(['success' => true, 'html' => $html]);
    exit(0);

} catch (\Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit(1);
} finally {
    if ($word) { try { $word->Quit(); } catch (\Throwable $e) {} }
    $word = null;
}


$inputFile = $argv[1];

if (!file_exists($inputFile)) {
    echo json_encode(['error' => 'File not found: ' . $inputFile]);
    exit(1);
}

$absInput = str_replace('/', '\\', realpath($inputFile));
$tmpDir = dirname($absInput);

$safeInput = $tmpDir . '\\doc_input.doc';
@unlink($safeInput);
if (!copy($absInput, $safeInput)) {
    echo json_encode(['error' => 'Cannot copy file to temp location']);
    exit(1);
}
chmod($safeInput, 0666);

$htmlOut = $tmpDir . '\\output.html';
@unlink($htmlOut);

$word = null;
$doc = null;
$result = null;

try {
    $word = new COM("Word.Application");
    $word->Visible = false;
    $word->DisplayAlerts = 0;

    $doc = $word->Documents->Open($safeInput, false, false, false);

    $doc->SaveAs2($htmlOut, 10);

    $html = @file_get_contents($htmlOut);
    if ($html === false || empty($html)) {
        echo json_encode(['error' => 'Cannot read converted HTML']);
        exit(1);
    }

    $cssDir = $tmpDir . '\\output.files';
    $cssFile = $cssDir . '\\output.css';
    if (@file_exists($cssFile)) {
        $css = @file_get_contents($cssFile);
        if ($css !== false && !empty($css)) {
            $html = '<style>' . $css . '</style>' . $html;
        }
    }

    try { $doc->Close(0); } catch (\Throwable $e) {}
    $doc = null;

    @unlink($htmlOut);
    @unlink($safeInput);
    if (is_dir($cssDir)) {
        $files = @glob("$cssDir\\*");
        if ($files) foreach ($files as $f) @unlink($f);
        @rmdir($cssDir);
    }

    echo json_encode(['success' => true, 'html' => $html]);
    exit(0);

} catch (\Throwable $e) {
    try { if ($doc) $doc->Close(0); } catch (\Throwable $e2) {}
    @unlink($safeInput);
    @unlink($htmlOut);
    echo json_encode(['error' => 'COM Error: ' . $e->getMessage()]);
    exit(1);

} finally {
    if ($word) {
        try { $word->Quit(); } catch (\Throwable $e) {}
    }
    $word = null;
    $doc = null;
}
