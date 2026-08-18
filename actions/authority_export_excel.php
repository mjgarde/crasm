<?php
/**
 * authority_export_word.php  (REAL .DOCX + LOGO — NO LIBRARY)
 * ---------------------------------------------------------
 * Real OOXML .docx built with PHP's built-in ZipArchive.
 * No Composer, no vendor folder, no PhpWord. Mirrors the
 * layout produced by authority_export_excel.php but as a
 * Word document (DSCIS Form 2 (RO) table with merged header
 * cells, PSA logo, and signature block).
 *
 * Requires: PHP "zip" extension (almost always on by default)
 *
 * Place this file in your /actions/ folder.
 * ---------------------------------------------------------
 */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

/* =========================================================
   1. CONFIG
   ========================================================= */
$REGION_LABEL      = 'REGION: XII';
$SUBREGION_LABEL   = 'SOCCSKSARGEN Region';
$REPORT_DATE       = date('F Y');
$SHEET_LABEL       = 'Sheet 1 of 1 sheets';

$PREPARED_BY_NAME  = 'MICHAEL A. MAMA';
$PREPARED_BY_TITLE = 'Administrative Aide VI (COSW)';
$REVIEWED_BY_NAME  = 'SHYLA MARIE M. DETICIO';
$REVIEWED_BY_TITLE = 'OIC-Chief, CRASD';
$APPROVED_BY_NAME  = 'ATTY. MAQTAHAR L. MANULON, CESO V';
$APPROVED_BY_TITLE = 'Regional Director';

/* =========================================================
   2. LOAD LOGO (PNG DIRECTLY - NO CONVERSION NEEDED)
   ========================================================= */
$logoPng   = null;
$logoWidth = 0;
$logoHeight = 0;

// Try multiple possible paths for logo
$possiblePaths = [
    __DIR__ . '/../assets/img/logo.png',
    __DIR__ . '/../assets/img/logo.jpg',
    __DIR__ . '/../assets/img/logo.jpeg',
    __DIR__ . '/../assets/img/logo.gif',
    __DIR__ . '/../assets/images/logo.png',
    __DIR__ . '/../assets/logo.png',
    __DIR__ . '/../img/logo.png',
];

$logoPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $logoPath = $path;
        break;
    }
}

if ($logoPath) {
    $logoPng = file_get_contents($logoPath);
    // Get image dimensions if GD is available
    if (function_exists('getimagesize')) {
        $info = @getimagesize($logoPath);
        if ($info) {
            $logoWidth = $info[0];
            $logoHeight = $info[1];
        }
    }
}

$hasLogo = $logoPng !== null;

/* =========================================================
   3. FETCH DATA
   ========================================================= */
$database = new Database();
$db = $database->connect();

$where  = [];
$params = [];
if (!empty($_GET['province'])) {
    $where[] = 'provinces = :province';
    $params[':province'] = $_GET['province'];
}
if (!empty($_GET['year'])) {
    $where[] = "YEAR(approved) = :year"; // MySQL
    $params[':year'] = $_GET['year'];
}
$sql = "SELECT * FROM authority_records";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY no ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

function fmtDate($value) {
    return !empty($value) ? date('n/j/Y', strtotime($value)) : '';
}
function xmlEscape($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/* =========================================================
   4. DOCX BUILDING HELPERS
   ========================================================= */

// EMU conversion: 1 inch = 914400 EMU; 1 px (96dpi) = 9525 EMU
function pxToEmu($px) { return (int) round($px * 9525); }

// Twips: 1 inch = 1440 twips
// Column widths (in twips) — total ~ 10500 (7.29in) to fit US Letter with 1" margins
$colWidthsTw = [
    'no'        => 600,   // NO.
    'name'      => 3000,  // NAME OF APPLICANT
    'new'       => 870,   // NEW
    'renewal'   => 1030,  // RENEWAL (+10 twips ≈ 1px more)
    'received'  => 1300,  // DATE RECEIVED
    'approved'  => 1300,  // APPROVED
    'disapproved' => 1400, // DISAPPROVED
    'transmitted' => 1300, // DATE TRANSMITTED BACK TO P.O.
];
$tableWidthTw = array_sum($colWidthsTw);

function cellBorders() {
    return '<w:tcBorders>'
        . '<w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
        . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
        . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
        . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
        . '</w:tcBorders>';
}

/**
 * Build one table cell.
 * $opts: width(tw), gridSpan(int), vMerge('restart'|'continue'|null),
 *        bold(bool), align('center'|'left'|'right'), size(halfpoints),
 *        border(bool), shading(hex|null)
 */
function tcell($text, $opts = []) {
    $width    = $opts['width']    ?? null;
    $gridSpan = $opts['gridSpan'] ?? null;
    $vMerge   = $opts['vMerge']   ?? null;
    $bold     = $opts['bold']     ?? false;
    $italic   = $opts['italic']   ?? false;
    $underline= $opts['underline']?? false;
    $align    = $opts['align']    ?? 'left';
    $size     = $opts['size']     ?? 18; // halfpoints (18 = 9pt)
    $border   = $opts['border']   ?? true;
    $vAlign   = $opts['vAlign']   ?? 'center';

    $tcPr = '<w:tcPr>';
    if ($width !== null) $tcPr .= '<w:tcW w:w="' . $width . '" w:type="dxa"/>';
    if ($gridSpan !== null) $tcPr .= '<w:gridSpan w:val="' . $gridSpan . '"/>';
    if ($vMerge === 'restart') $tcPr .= '<w:vMerge w:val="restart"/>';
    elseif ($vMerge === 'continue') $tcPr .= '<w:vMerge/>';
    if ($border) $tcPr .= cellBorders();
    $tcPr .= '<w:vAlign w:val="' . $vAlign . '"/>';
    $tcPr .= '</w:tcPr>';

    $rPr = '<w:rPr>';
    $rPr .= '<w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/>';
    if ($bold) $rPr .= '<w:b/>';
    if ($italic) $rPr .= '<w:i/>';
    if ($underline) $rPr .= '<w:u w:val="single"/>';
    $rPr .= '<w:sz w:val="' . $size . '"/>';
    $rPr .= '</w:rPr>';

    $jc = '<w:jc w:val="' . $align . '"/>';

    // vMerge continue rows must still have an (empty) paragraph
    $textXml = ($vMerge === 'continue')
        ? '<w:p><w:pPr><w:jc w:val="' . $align . '"/></w:pPr></w:p>'
        : '<w:p><w:pPr>' . $jc . '</w:pPr><w:r>' . $rPr . '<w:t xml:space="preserve">' . xmlEscape($text) . '</w:t></w:r></w:p>';

    return '<w:tc>' . $tcPr . $textXml . '</w:tc>';
}

function trow($cellsXml, $height = null) {
    $trPr = '';
    if ($height !== null) {
        $trPr = '<w:trPr><w:trHeight w:val="' . $height . '" w:hRule="atLeast"/></w:trPr>';
    }
    return '<w:tr>' . $trPr . implode('', $cellsXml) . '</w:tr>';
}

/* =========================================================
   5. BUILD DOCUMENT BODY
   ========================================================= */
$bodyXml = '';

// --- Form code line ---
$bodyXml .= '<w:p><w:pPr><w:jc w:val="left"/></w:pPr><w:r>'
    . '<w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="16"/></w:rPr>'
    . '<w:t>DSCIS Form 2 (RO)</w:t></w:r></w:p>';

// --- Title block: text centered, logo floated top-right via anchor ---
$logoDrawing = '';
if ($hasLogo) {
    $emuW = pxToEmu(70);
    $emuH = $logoHeight > 0 ? (int) round($emuW * ($logoHeight / $logoWidth)) : pxToEmu(70);
    $logoDrawing = '<w:r><w:rPr><w:noProof/></w:rPr><w:drawing>'
        . '<wp:anchor behindDoc="0" distT="0" distB="0" distL="114300" distR="0" simplePos="0" locked="0" layoutInCell="1" allowOverlap="1" relativeHeight="1">'
        . '<wp:simplePos x="0" y="0"/>'
        . '<wp:positionH relativeFrom="column"><wp:align>right</wp:align></wp:positionH>'
        . '<wp:positionV relativeFrom="paragraph"><wp:posOffset>0</wp:posOffset></wp:positionV>'
        . '<wp:extent cx="' . $emuW . '" cy="' . $emuH . '"/>'
        . '<wp:wrapNone/>'
        . '<wp:docPr id="1" name="PSA Logo"/>'
        . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
        . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<pic:nvPicPr><pic:cNvPr id="1" name="PSA Logo"/><pic:cNvPicPr/></pic:nvPicPr>'
        . '<pic:blipFill><a:blip r:embed="rIdLogo"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
        . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $emuW . '" cy="' . $emuH . '"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
        . '</pic:pic></a:graphicData></a:graphic></wp:anchor></w:drawing></w:r>';
}

$bodyXml .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>' . $logoDrawing . '<w:r>'
    . '<w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="22"/></w:rPr>'
    . '<w:t>Republic of the Philippines</w:t></w:r></w:p>';

$bodyXml .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>'
    . '<w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:b/><w:sz w:val="28"/></w:rPr>'
    . '<w:t>PHILIPPINE STATISTICS AUTHORITY</w:t></w:r></w:p>';

$bodyXml .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>'
    . '<w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="20"/></w:rPr>'
    . '<w:t>' . xmlEscape($SUBREGION_LABEL) . '</w:t></w:r></w:p>';

// --- Region / Date line (tab-separated) ---
$bodyXml .= '<w:p><w:pPr><w:tabs><w:tab w:val="right" w:pos="10500"/></w:tabs></w:pPr>'
    . '<w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:b/><w:sz w:val="18"/></w:rPr>'
    . '<w:t xml:space="preserve">' . xmlEscape($REGION_LABEL) . '</w:t></w:r>'
    . '<w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="18"/></w:rPr><w:tab/>'
    . '<w:t>DATE: ' . xmlEscape(strtoupper($REPORT_DATE)) . '</w:t></w:r></w:p>';

$bodyXml .= '<w:p><w:pPr><w:tabs><w:tab w:val="right" w:pos="10500"/></w:tabs></w:pPr>'
    . '<w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="18"/></w:rPr><w:tab/>'
    . '<w:t>' . xmlEscape($SHEET_LABEL) . '</w:t></w:r></w:p>';

// --- Table grid ---
$gridXml = '<w:tblGrid>';
foreach ($colWidthsTw as $w) $gridXml .= '<w:gridCol w:w="' . $w . '"/>';
$gridXml .= '</w:tblGrid>';

$tblPr = '<w:tblPr>'
    . '<w:tblW w:w="' . $tableWidthTw . '" w:type="dxa"/>'
    . '<w:tblBorders>'
    . '<w:top w:val="single" w:sz="4" w:color="000000"/>'
    . '<w:left w:val="single" w:sz="4" w:color="000000"/>'
    . '<w:bottom w:val="single" w:sz="4" w:color="000000"/>'
    . '<w:right w:val="single" w:sz="4" w:color="000000"/>'
    . '<w:insideH w:val="single" w:sz="4" w:color="000000"/>'
    . '<w:insideV w:val="single" w:sz="4" w:color="000000"/>'
    . '</w:tblBorders>'
    . '<w:tblLayout w:type="fixed"/>'
    . '</w:tblPr>';

// --- Header row 1 ---
$hdr1 = [];
$hdr1[] = tcell('NO.', ['width' => $colWidthsTw['no'], 'vMerge' => 'restart', 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr1[] = tcell('NAME OF APPLICANT', ['width' => $colWidthsTw['name'], 'vMerge' => 'restart', 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr1[] = tcell('TYPE OF APPLICATION', ['width' => $colWidthsTw['new'] + $colWidthsTw['renewal'], 'gridSpan' => 2, 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr1[] = tcell('DATE RECEIVED', ['width' => $colWidthsTw['received'], 'vMerge' => 'restart', 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr1[] = tcell('ACTION TAKEN (DATE)', ['width' => $colWidthsTw['approved'] + $colWidthsTw['disapproved'], 'gridSpan' => 2, 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr1[] = tcell('DATE TRANSMITTED BACK TO P.O.', ['width' => $colWidthsTw['transmitted'], 'vMerge' => 'restart', 'bold' => true, 'align' => 'center', 'size' => 16]);
$tableRows = trow($hdr1, 500);

// --- Header row 2 ---
$hdr2 = [];
$hdr2[] = tcell('', ['width' => $colWidthsTw['no'], 'vMerge' => 'continue']);
$hdr2[] = tcell('', ['width' => $colWidthsTw['name'], 'vMerge' => 'continue']);
$hdr2[] = tcell('NEW', ['width' => $colWidthsTw['new'], 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr2[] = tcell('RENEWAL', ['width' => $colWidthsTw['renewal'], 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr2[] = tcell('', ['width' => $colWidthsTw['received'], 'vMerge' => 'continue']);
$hdr2[] = tcell('APPROVED', ['width' => $colWidthsTw['approved'], 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr2[] = tcell('DISAPPROVED', ['width' => $colWidthsTw['disapproved'], 'bold' => true, 'align' => 'center', 'size' => 16]);
$hdr2[] = tcell('', ['width' => $colWidthsTw['transmitted'], 'vMerge' => 'continue']);
$tableRows .= trow($hdr2);

// --- Data rows ---
$num = 1;
foreach ($records as $rec) {
    $isNew     = strcasecmp($rec['type'] ?? '', 'New') === 0;
    $isRenewal = strcasecmp($rec['type'] ?? '', 'Renewal') === 0;

    $row = [];
    $row[] = tcell((string) $num, ['width' => $colWidthsTw['no'], 'align' => 'center', 'size' => 16]);
    $row[] = tcell($rec['name_of_so'], ['width' => $colWidthsTw['name'], 'align' => 'left', 'size' => 16]);
    $row[] = tcell($isNew ? 'NEW' : '', ['width' => $colWidthsTw['new'], 'align' => 'center', 'size' => 16]);
    $row[] = tcell($isRenewal ? 'RENEWAL' : '', ['width' => $colWidthsTw['renewal'], 'align' => 'center', 'size' => 16]);
    $row[] = tcell(fmtDate($rec['received_in_rsso'] ?? null), ['width' => $colWidthsTw['received'], 'align' => 'center', 'size' => 16]);
    $row[] = tcell(fmtDate($rec['approved'] ?? null), ['width' => $colWidthsTw['approved'], 'align' => 'center', 'size' => 16]);
    $row[] = tcell(fmtDate($rec['disapproved'] ?? null), ['width' => $colWidthsTw['disapproved'], 'align' => 'center', 'size' => 16]);
    $row[] = tcell(fmtDate($rec['transmitted_to_pso'] ?? null), ['width' => $colWidthsTw['transmitted'], 'align' => 'center', 'size' => 16]);
    $tableRows .= trow($row);
    $num++;
}

$tableXml = '<w:tbl>' . $tblPr . $gridXml . $tableRows . '</w:tbl>';

$bodyXml .= $tableXml;

// --- Spacer paragraphs ---
$bodyXml .= '<w:p/><w:p/>';

// --- Signature block (3-column borderless table) ---
// Give "Approved by" a bit more width so the long name fits on one line.
$sigColW      = (int) round($tableWidthTw / 3) - 150;
$sigColWLast  = $tableWidthTw - ($sigColW * 2);
$sigTblPr = '<w:tblPr><w:tblW w:w="' . $tableWidthTw . '" w:type="dxa"/>'
    . '<w:tblBorders>'
    . '<w:top w:val="none" w:sz="0" w:color="auto"/>'
    . '<w:left w:val="none" w:sz="0" w:color="auto"/>'
    . '<w:bottom w:val="none" w:sz="0" w:color="auto"/>'
    . '<w:right w:val="none" w:sz="0" w:color="auto"/>'
    . '<w:insideH w:val="none" w:sz="0" w:color="auto"/>'
    . '<w:insideV w:val="none" w:sz="0" w:color="auto"/>'
    . '</w:tblBorders></w:tblPr>';
$sigGrid = '<w:tblGrid><w:gridCol w:w="' . $sigColW . '"/><w:gridCol w:w="' . $sigColW . '"/><w:gridCol w:w="' . $sigColWLast . '"/></w:tblGrid>';

function sigCell($text, $width, $bold = false, $underline = false, $align = 'center') {
    return tcell($text, [
        'width' => $width, 'bold' => $bold, 'underline' => $underline,
        'align' => $align, 'size' => 18, 'border' => false,
    ]);
}

$sigRows  = trow([sigCell('Prepared by:', $sigColW, false, false, 'left'), sigCell('Reviewed by:', $sigColW, false, false, 'left'), sigCell('Approved by:', $sigColWLast, false, false, 'left')]);
$sigRows .= trow([sigCell('', $sigColW), sigCell('', $sigColW), sigCell('', $sigColWLast)]);
$sigRows .= trow([sigCell('', $sigColW), sigCell('', $sigColW), sigCell('', $sigColWLast)]);
$sigRows .= trow([
    sigCell($PREPARED_BY_NAME, $sigColW, true, true, 'center'),
    sigCell($REVIEWED_BY_NAME, $sigColW, true, true, 'center'),
    sigCell($APPROVED_BY_NAME, $sigColWLast, true, true, 'center'),
]);
$sigRows .= trow([
    sigCell($PREPARED_BY_TITLE, $sigColW, false, false, 'center'),
    sigCell($REVIEWED_BY_TITLE, $sigColW, false, false, 'center'),
    sigCell($APPROVED_BY_TITLE, $sigColWLast, false, false, 'center'),
]);

$bodyXml .= '<w:tbl>' . $sigTblPr . $sigGrid . $sigRows . '</w:tbl>';

// --- Section properties (US Letter, 0.5in margins) ---
$bodyXml .= '<w:sectPr>'
    . '<w:pgSz w:w="12240" w:h="15840"/>'
    . '<w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" w:header="720" w:footer="720" w:gutter="0"/>'
    . '</w:sectPr>';

/* =========================================================
   6. OOXML PARTS
   ========================================================= */
$contentTypesExtra = $hasLogo
    ? '<Default Extension="png" ContentType="image/png"/>'
    : '';

$contentTypes = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
{$contentTypesExtra}
</Types>
XML;

$rootRels = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML;

$docRelsExtra = $hasLogo
    ? '<Relationship Id="rIdLogo" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.png"/>'
    : '';

$documentRels = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
{$docRelsExtra}
</Relationships>
XML;

$styles = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:docDefaults>
<w:rPrDefault><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="18"/></w:rPr></w:rPrDefault>
</w:docDefaults>
<w:style w:type="paragraph" w:default="1" w:styleId="Normal">
<w:name w:val="Normal"/>
</w:style>
</w:styles>
XML;

$document = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"
xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<w:body>
{$bodyXml}
</w:body>
</w:document>
XML;

/* =========================================================
   7. BUILD ZIP (DOCX) AND STREAM
   ========================================================= */
$filename = 'DSCIS_Form2_RO_' . date('Ymd_His') . '.docx';
$tmpFile = tempnam(sys_get_temp_dir(), 'docx_');

$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('Could not create the Word file. Please make sure the PHP "zip" extension is enabled (php -m | grep zip).');
}

$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rootRels);
$zip->addFromString('word/document.xml', $document);
$zip->addFromString('word/styles.xml', $styles);
$zip->addFromString('word/_rels/document.xml.rels', $documentRels);

if ($hasLogo) {
    $zip->addFromString('word/media/image1.png', $logoPng);
}

$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');

readfile($tmpFile);
unlink($tmpFile);
exit;