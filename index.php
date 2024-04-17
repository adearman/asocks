<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxy API Monitoring</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">Proxy API Monitoring</h1>
        <form method="post">
            <div class="form-group">
                <label for="apiList">List API:</label>
                <textarea class="form-control" id="apiList" name="apiList" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Mulai Proses</button>
        </form>
        <div class="mt-4" id="output">
            <?php
            function bytesToGigabytes($bytes)
            {
                return number_format($bytes / (1024 * 1024 * 1024), 2);
            }

            // Fungsi konversi detik ke hari dan jam
            function secondsToDaysHours($seconds)
            {
                $days = floor($seconds / (3600 * 24));
                $hours = floor($seconds % (3600 * 24) / 3600);
                return "$days hari $hours jam";
            }
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $apiList = explode("\n", $_POST["apiList"]);
                foreach ($apiList as $line) {
                    $line = trim($line);
                    $credentials = explode('|', $line);
                    $email = $credentials[0];
                    $apikey = $credentials[1] ?? '';

                    if ($apikey) {
                        try {
                            $trafficUrl = "https://api.asocks.com/v2/proxy/total-spent-traffic?apikey=$apikey";
                            $planInfoUrl = "https://api.asocks.com/v2/plan/info?apikey=$apikey";
                            $balanceInfoUrl = "https://api.asocks.com/v2/user/balance?apikey=$apikey";

                            $trafficResponse = json_decode(file_get_contents($trafficUrl));
                            $planInfoResponse = json_decode(file_get_contents($planInfoUrl));
                            $balanceInfoResponse = json_decode(file_get_contents($balanceInfoUrl));

                            // Process responses
                            if ($trafficResponse->success && $planInfoResponse->success && $balanceInfoResponse->success) {
                                $totalSpentTrafficGB = number_format($trafficResponse->total_spent_traffic / (1024 * 1024 * 1024), 2);
                                $expired = secondsToDaysHours($planInfoResponse->message->expiredSeconds);
                                $balanceGB = number_format($balanceInfoResponse->all_available_traffic, 2);

                                echo "<p id='response-$email'>$email | Usage: $totalSpentTrafficGB GB | Bandwidth: $balanceGB GB | Expired: $expired</p>";
                                echo "<script>document.getElementById('response-$email').scrollIntoView();</script>";
                                ob_flush();
                                flush();
                                sleep(1); // Sleep for 1 second before processing the next request (optional)
                            }
                        } catch (Exception $e) {
                            echo "<p>Error saat memproses API untuk $email: $e</p>";
                        }
                    }
                }
            }
            ?>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>