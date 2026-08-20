<?php
require_once __DIR__ . '/../bootstrap.php';

date_default_timezone_set('Europe/London');

$timezone = new DateTimeZone('Europe/London');
$start = new DateTimeImmutable('today', $timezone);
$end = $start->modify('+1 day');
$startTimestamp = $start->getTimestamp();
$endTimestamp = $end->getTimestamp();

$points = [];
$rainTotal = 0.0;
$low = null;
$high = null;
$peakGust = 0.0;
$latest = null;

$consumeRow = function (array $row) use (&$points, &$rainTotal, &$low, &$high, &$peakGust, &$latest): void {
    $temperature = $row['outTemp'] !== null ? (float) $row['outTemp'] : null;
    $rainTotal += ((float) ($row['rain'] ?? 0)) * 10;
    $peakGust = max($peakGust, ((float) ($row['windGust'] ?? 0)) * 3.6);
    if ($temperature !== null) {
        $low = $low === null ? $temperature : min($low, $temperature);
        $high = $high === null ? $temperature : max($high, $temperature);
    }
    $latest = $row;
    $points[] = [
        'time' => (int) $row['dateTime'],
        'temp' => $temperature,
        'rain' => $rainTotal,
    ];
};

if (getenv('SOCIAL_CARD_PREVIEW') === '1') {
    for ($hour = 0; $hour <= 20; $hour++) {
        $consumeRow([
            'dateTime' => $startTimestamp + ($hour * 3600),
            'outTemp' => 14.2 + sin(($hour - 4) / 7) * 7.4,
            'rain' => in_array($hour, [7, 8, 15], true) ? 0.06 : 0,
            'outHumidity' => 78,
            'windGust' => 4.8,
        ]);
    }
} else {
    require_once __DIR__ . '/../dbconn.php';
    $sql = 'SELECT dateTime, outTemp, rain, outHumidity, windGust
            FROM archive
            WHERE dateTime >= ? AND dateTime < ?
            ORDER BY dateTime ASC';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $startTimestamp, $endTimestamp);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $consumeRow($row);
    }
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    mysqli_close($link);
}

$current = $latest && $latest['outTemp'] !== null ? (float) $latest['outTemp'] : null;
$humidity = $latest && $latest['outHumidity'] !== null ? (float) $latest['outHumidity'] : null;

function card_colour($image, string $hex, int $alpha = 0) {
    $hex = ltrim($hex, '#');
    return imagecolorallocatealpha(
        $image,
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
        $alpha
    );
}

function card_round_rect($image, int $x1, int $y1, int $x2, int $y2, int $radius, int $colour): void {
    imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $colour);
    imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $colour);
    imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $colour);
    imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $colour);
    imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $colour);
    imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $colour);
}

function card_text($image, string $text, float $size, int $x, int $y, int $colour, ?string $font): void {
    if ($font && function_exists('imagettftext')) {
        imagettftext($image, $size, 0, $x, $y, $colour, $font, $text);
        return;
    }
    imagestring($image, 5, $x, max(0, $y - 15), $text, $colour);
}

function card_value(?float $value, int $decimals = 1): string {
    return $value === null ? '—' : number_format($value, $decimals);
}

$regularCandidates = [
    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
    '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
    '/System/Library/Fonts/Supplemental/Arial.ttf',
];
$boldCandidates = [
    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
    '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
    '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
];
$regularFont = null;
$boldFont = null;
foreach ($regularCandidates as $candidate) {
    if (is_file($candidate)) { $regularFont = $candidate; break; }
}
foreach ($boldCandidates as $candidate) {
    if (is_file($candidate)) { $boldFont = $candidate; break; }
}
$boldFont = $boldFont ?: $regularFont;

$image = imagecreatetruecolor(1200, 630);
imageantialias($image, true);
$navy = card_colour($image, '#10233f');
$panel = card_colour($image, '#18314f');
$panelLight = card_colour($image, '#203d5e');
$white = card_colour($image, '#f8fbff');
$muted = card_colour($image, '#9fb3ca');
$grid = card_colour($image, '#58718c', 64);
$temperatureColour = card_colour($image, '#fb7185');
$rainColour = card_colour($image, '#38bdf8');
$green = card_colour($image, '#4ade80');

imagefill($image, 0, 0, $navy);
card_text($image, 'WHEATHAMPSTEAD  •  LIVE WEATHER', 14, 42, 45, $muted, $boldFont);
card_text($image, date('l j F'), 14, 1005, 45, $muted, $regularFont);
card_text($image, 'Today at a glance', 31, 42, 91, $white, $boldFont);
imagefilledellipse($image, 1132, 84, 12, 12, $green);
card_text($image, 'LIVE', 11, 1144, 89, $green, $boldFont);

card_round_rect($image, 40, 115, 1160, 420, 18, $panel);
$plotLeft = 94;
$plotTop = 154;
$plotRight = 1118;
$plotBottom = 374;
$plotWidth = $plotRight - $plotLeft;
$plotHeight = $plotBottom - $plotTop;

for ($i = 0; $i <= 4; $i++) {
    $y = (int) round($plotTop + ($plotHeight * $i / 4));
    imageline($image, $plotLeft, $y, $plotRight, $y, $grid);
}
foreach ([0, 6, 12, 18, 24] as $hour) {
    $x = (int) round($plotLeft + ($plotWidth * $hour / 24));
    imageline($image, $x, $plotTop, $x, $plotBottom, $grid);
    card_text($image, sprintf('%02d:00', $hour), 13, $x - 24, 400, $muted, $regularFont);
}

$tempFloor = $low === null ? 0.0 : floor($low - 2);
$tempCeiling = $high === null ? 1.0 : ceil($high + 2);
if ($tempCeiling <= $tempFloor) { $tempCeiling = $tempFloor + 1; }
$rainCeiling = max(1.0, ceil($rainTotal));

card_text($image, card_value($tempCeiling, 0) . '°', 14, 46, $plotTop + 7, $temperatureColour, $boldFont);
card_text($image, card_value($tempFloor, 0) . '°', 14, 46, $plotBottom, $temperatureColour, $boldFont);
card_text($image, card_value($rainCeiling, 0) . ' mm', 14, 1115, $plotTop + 7, $rainColour, $boldFont);

if (count($points) > 1) {
    $rainPolygon = [$plotLeft, $plotBottom];
    $temperatureLine = [];
    foreach ($points as $point) {
        $dayFraction = max(0, min(1, ($point['time'] - $startTimestamp) / max(1, $endTimestamp - $startTimestamp)));
        $x = (int) round($plotLeft + ($plotWidth * $dayFraction));
        $rainY = (int) round($plotBottom - (($point['rain'] / $rainCeiling) * $plotHeight));
        $rainPolygon[] = $x;
        $rainPolygon[] = $rainY;
        if ($point['temp'] !== null) {
            $tempY = (int) round($plotBottom - ((($point['temp'] - $tempFloor) / ($tempCeiling - $tempFloor)) * $plotHeight));
            $temperatureLine[] = [$x, $tempY];
        }
    }
    $rainPolygon[] = $rainPolygon[count($rainPolygon) - 2];
    $rainPolygon[] = $plotBottom;
    imagefilledpolygon($image, $rainPolygon, card_colour($image, '#0ea5e9', 92));
    for ($i = 1; $i < count($temperatureLine); $i++) {
        imagesetthickness($image, 4);
        imageline($image, $temperatureLine[$i - 1][0], $temperatureLine[$i - 1][1], $temperatureLine[$i][0], $temperatureLine[$i][1], $temperatureColour);
    }
    imagesetthickness($image, 1);
}

imagefilledrectangle($image, 790, 132, 810, 137, $temperatureColour);
card_text($image, 'Temperature', 14, 818, 142, $white, $regularFont);
imagefilledrectangle($image, 950, 132, 970, 137, $rainColour);
card_text($image, 'Cumulative rain', 14, 978, 142, $white, $regularFont);

$stats = [
    ['CURRENT', card_value($current) . '°C', $humidity === null ? 'Latest observation' : card_value($humidity, 0) . '% humidity', 44, 40, 240],
    ['TODAY\'S RANGE', card_value($low) . '–' . card_value($high) . '°C', 'Low to high', 36, 294, 310],
    ['RAIN TODAY', card_value($rainTotal) . ' mm', 'Accumulated since midnight', 44, 618, 250],
    ['PEAK GUST', card_value($peakGust) . ' kph', 'Strongest gust today', 40, 882, 278],
];

foreach ($stats as $stat) {
    $x = $stat[4];
    card_round_rect($image, $x, 445, $x + $stat[5], 590, 15, $panelLight);
    card_text($image, $stat[0], 11, $x + 18, 474, $muted, $boldFont);
    card_text($image, $stat[1], $stat[3], $x + 18, 530, $white, $boldFont);
    card_text($image, $stat[2], 12, $x + 18, 565, $muted, $regularFont);
}

card_text($image, 'smeird.com  •  Personal weather station', 11, 42, 618, $muted, $regularFont);

header('Content-Type: image/png');
header('Cache-Control: public, max-age=300, stale-while-revalidate=300');
imagepng($image, null, 7);
