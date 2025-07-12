<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = trim($_POST['history']);
    $lines = explode("\n", $raw);
    $history = [];
    foreach ($lines as $line) {
        $parts = explode(",", $line);
        if (count($parts) == 2 && is_numeric(trim($parts[1]))) {
            $history[] = [trim($parts[0]), intval(trim($parts[1]))];
        }
    }

    $numbers = array_map(fn($item) => $item[1], array_slice($history, -20));
    $counts = array_count_values($numbers);
    arsort($counts);
    $prediction = array_key_first($counts);
    $size = $prediction >= 5 ? 'BIG' : 'SMALL';
    $confidence = round(($counts[$prediction] / count($numbers)) * 100, 2);
    $pattern = implode(' → ', array_slice($numbers, -4));

    $last_period = $history[count($history) - 1][0];
    if (str_starts_with($last_period, 'W1-')) {
        $dt = DateTime::createFromFormat('YmdHi', substr($last_period, 3));
        $dt->modify('+1 minute');
        $next_period = 'W1-' . $dt->format('YmdHi');
    } elseif (str_starts_with($last_period, 'W30-')) {
        $dt = DateTime::createFromFormat('YmdHis', substr($last_period, 4));
        $dt->modify('+30 seconds');
        $next_period = 'W30-' . $dt->format('YmdHis');
    } else {
        $next_period = 'UNKNOWN';
    }
} else {
    $raw = '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Wingo Predictor PHP</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        textarea { width: 100%; height: 200px; font-size: 16px; }
        .result { background: white; padding: 15px; margin-top: 20px; border-radius: 10px; }
    </style>
</head>
<body>
    <h2>✨ Wingo Predictor (PHP Version)</h2>
    <form method="post">
        <label><strong>Enter last 20-50 results:</strong></label><br>
        <textarea name="history"><?php echo htmlspecialchars($raw); ?></textarea><br><br>
        <button type="submit">Predict</button>
    </form>

    <?php if (!empty($prediction)) : ?>
    <div class="result">
        <h3>🔮 Prediction</h3>
        <p><strong>Next Period:</strong> <?php echo $next_period; ?></p>
        <p><strong>Predicted Number:</strong> <?php echo $prediction; ?></p>
        <p><strong>Size:</strong> <?php echo $size; ?></p>
        <p><strong>Confidence:</strong> <?php echo $confidence; ?>%</p>
        <p><strong>Pattern:</strong> <?php echo $pattern; ?></p>
    </div>
    <?php endif; ?>
</body>
</html>
