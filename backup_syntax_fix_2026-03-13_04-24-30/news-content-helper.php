<?php

function ensureDirectoryExists($directory)
{
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

function convertIniSizeToBytes($size)
{
    $size = trim((string) $size);
    if ($size === '') {
        return 0;
    }

    $unit = strtolower(substr($size, -1));
    $value = (float) $size;

    switch ($unit) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
            break;
    }

    return (int) $value;
}

function getNewsImageExtensionFromMime($mime)
{
    $map = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp'
    ];

    return $map[strtolower($mime)] ?? 'png';
}

function saveNewsInlineImage($mime, $base64Data)
{
    $binary = base64_decode($base64Data, true);

    if ($binary === false) {
        return null;
    }

    $relativeDirectory = 'images/news-content';
    ensureDirectoryExists($relativeDirectory);

    $extension = getNewsImageExtensionFromMime($mime);
    $filename = 'news-content-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = $relativeDirectory . '/' . $filename;

    if (file_put_contents($relativePath, $binary) === false) {
        return null;
    }

    return $relativePath;
}

function getNodeInnerHtml(DOMNode $node)
{
    $html = '';

    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }

    return $html;
}

function normalizeNewsContentHtml($html)
{
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    if (!class_exists('DOMDocument')) {
        return trim($html);
    }

    $internalErrors = libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $wrappedHtml = '<?xml encoding="utf-8" ?><!DOCTYPE html><html><body><div id="news-content-root">' . $html . '</div></body></html>';
    $dom->loadHTML($wrappedHtml, LIBXML_HTML_NODEFDTD);

    $xpath = new DOMXPath($dom);
    $rootNode = $xpath->query('//*[@id="news-content-root"]')->item(0);

    if (!$rootNode) {
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        return trim($html);
    }

    foreach ($xpath->query('.//script | .//object | .//embed', $rootNode) as $unsafeNode) {
        $unsafeNode->parentNode->removeChild($unsafeNode);
    }

    foreach ($xpath->query('.//*[@src] | .//*[@href]', $rootNode) as $element) {
        $attributeName = $element->hasAttribute('src') ? 'src' : 'href';
        $value = trim($element->getAttribute($attributeName));

        if ($attributeName === 'src' && preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/s', $value, $matches)) {
            $savedPath = saveNewsInlineImage($matches[1], $matches[2]);

            if ($savedPath !== null) {
                $element->setAttribute('src', $savedPath);
            } else {
                $element->parentNode->removeChild($element);
                continue;
            }
        }

        if (preg_match('/^\s*javascript:/i', $value)) {
            $element->removeAttribute($attributeName);
        }
    }

    foreach ($xpath->query('.//@*[starts-with(name(), "on")]', $rootNode) as $attribute) {
        $attribute->ownerElement->removeAttribute($attribute->nodeName);
    }

    $normalizedHtml = trim(getNodeInnerHtml($rootNode));

    libxml_clear_errors();
    libxml_use_internal_errors($internalErrors);

    return $normalizedHtml;
}

function getContentPlainText($html)
{
    $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim((string) $text);
}

function hasRenderableContent($html)
{
    return getContentPlainText($html) !== '' || stripos((string) $html, '<img') !== false;
}

function slugifyNewsTitle($text)
{
    $map = [
        'a' => ['à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ'],
        'A' => ['À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ'],
        'e' => ['è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ'],
        'E' => ['È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ'],
        'i' => ['ì', 'í', 'ị', 'ỉ', 'ĩ'],
        'I' => ['Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ'],
        'o' => ['ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ'],
        'O' => ['Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ'],
        'u' => ['ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ'],
        'U' => ['Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ'],
        'y' => ['ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ'],
        'Y' => ['Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ'],
        'd' => ['đ'],
        'D' => ['Đ']
    ];

    foreach ($map as $ascii => $unicodeChars) {
        $text = str_replace($unicodeChars, $ascii, $text);
    }

    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');

    return $text !== '' ? $text : 'tin-tuc';
}

function generateUniqueNewsSlug(mysqli $conn, $title, $excludeId = null)
{
    $baseSlug = substr(slugifyNewsTitle($title), 0, 240);
    $baseSlug = trim($baseSlug, '-');
    $baseSlug = $baseSlug !== '' ? $baseSlug : 'tin-tuc';
    $slug = $baseSlug;
    $suffix = 1;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $conn->prepare('SELECT id FROM news WHERE slug = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $slug, $excludeId);
        } else {
            $stmt = $conn->prepare('SELECT id FROM news WHERE slug = ? LIMIT 1');
            $stmt->bind_param('s', $slug);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            return $slug;
        }

        $suffixText = '-' . $suffix;
        $slug = substr($baseSlug, 0, 255 - strlen($suffixText)) . $suffixText;
        $suffix++;
    }
}
