<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sms-bomer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message { font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body class="bg-light min-vh-100 d-flex justify-content-center align-items-start py-5">
    <div class="card border-0 shadow-sm rounded-3" style="width:100%;max-width:420px">
        <div class="card-header bg-white border-bottom px-4 py-3">
            <h5 class="mb-0 fw-semibold">💣 SMS Bomer</h5>
        </div>
        <div class="card-body px-4 py-4">
            <div class="mb-3">
                <label class="form-label small fw-medium">Phone Number</label>
                <input type="tel" id="phone" class="form-control" placeholder="09*********" maxlength="11">
                <div id="phoneError" class="error-message text-danger"></div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-medium">Quantity <span class="text-muted fw-normal">(1–300)</span></label>
                <input type="number" id="quantity" class="form-control" min="1" max="300" value="40">
                <div id="quantityError" class="error-message text-danger"></div>
            </div>

            <div class="d-grid gap-2 mb-4">
                <button id="startBtn" class="btn btn-dark">Start Sending</button>
                <button id="stopBtn" class="btn btn-outline-secondary" disabled>Stop</button>
            </div>

            <div class="row text-center border rounded-2 py-3 mb-4 mx-0">
                <div class="col border-end">
                    <div id="totalCount" class="fs-5 fw-semibold">0</div>
                    <div class="text-muted small">Total</div>
                </div>
                <div class="col border-end">
                    <div id="successCount" class="fs-5 fw-semibold text-success">0</div>
                    <div class="text-muted small">Sent</div>
                </div>
                <div class="col">
                    <div id="failCount" class="fs-5 fw-semibold text-danger">0</div>
                    <div class="text-muted small">Failed</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isRunning = false;
        let stopRequested = false;
        let successCount = 0;
        let failCount = 0;
        let totalAttempts = 0;
        let pendingRequests = 0;

        function formatPhone(phone) {
            phone = String(phone).trim().replace(/[\s\-+]/g, '');
            if (phone.startsWith('0')) phone = phone.substring(1);
            else if (phone.startsWith('63')) phone = phone.substring(2);
            return phone;
        }

        function validatePhone(phone) {
            const cleaned = formatPhone(phone);
            return /^9\d{9}$/.test(cleaned);
        }

        function updateStats() {
            document.getElementById('totalCount').textContent = totalAttempts;
            document.getElementById('successCount').textContent = successCount;
            document.getElementById('failCount').textContent = failCount;
        }

        async function sendRequest(phone, serviceName) {
            try {
                const response = await fetch('api.php', {  // Dito naka-point sa api.php
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'send_sms',
                        phone: phone,
                        service: serviceName
                    })
                });
                const result = await response.json();
                return result;
            } catch (error) {
                return { success: false, message: "Error" };
            }
        }

        async function sendRandomRequest(phone) {
            const services = [
                "BOMB OTP", "MWELL ULTRA", "EZLOAN", "XPRESS PH", "ABENSON",
                "EXCELLENT LENDING", "BISTRO", "WEMOVE", "LBC CONNECT", "PICKUP COFFEE",
                "HONEY LOAN", "KUMU PH", "S5.COM", "QUICK OTP"
            ];
            const randomService = services[Math.floor(Math.random() * services.length)];
            
            try {
                const result = await sendRequest(phone, randomService);
                totalAttempts++;
                
                if (result.success) {
                    successCount++;
                } else {
                    failCount++;
                }
                updateStats();
            } catch (error) {
                totalAttempts++;
                failCount++;
                updateStats();
            }
            pendingRequests--;
        }

        async function startAttack() {
            const phone = document.getElementById('phone').value.trim();
            const quantity = parseInt(document.getElementById('quantity').value);
            
            document.getElementById('phoneError').innerHTML = '';
            document.getElementById('quantityError').innerHTML = '';
            
            let hasError = false;
            
            if (!phone) {
                document.getElementById('phoneError').innerHTML = 'Please enter a phone number';
                hasError = true;
            } else if (!validatePhone(phone)) {
                document.getElementById('phoneError').innerHTML = 'Phone number must be 11 digits and start with 09';
                hasError = true;
            }
            
            if (isNaN(quantity) || quantity < 1 || quantity > 300) {
                document.getElementById('quantityError').innerHTML = 'Quantity must be between 1 and 300';
                hasError = true;
            }
            
            if (hasError) return;
            
            isRunning = true;
            stopRequested = false;
            successCount = 0;
            failCount = 0;
            totalAttempts = 0;
            pendingRequests = 0;
            updateStats();
            
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            document.getElementById('phone').disabled = true;
            document.getElementById('quantity').disabled = true;
            
            try {
                for (let i = 0; i < quantity; i++) {
                    if (stopRequested) break;
                    
                    pendingRequests++;
                    sendRandomRequest(formatPhone(phone));
                    
                    if (i < quantity - 1 && !stopRequested) {
                        await new Promise(resolve => setTimeout(resolve, Math.random() * 1000 + 500));
                    }
                }
                
                while (pendingRequests > 0) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                }
            } catch (error) {
            } finally {
                document.getElementById('startBtn').disabled = false;
                document.getElementById('stopBtn').disabled = true;
                document.getElementById('phone').disabled = false;
                document.getElementById('quantity').disabled = false;
                isRunning = false;
            }
        }

        function stopAttack() {
            if (isRunning) stopRequested = true;
        }

        // Event listeners
        document.getElementById('startBtn').onclick = startAttack;
        document.getElementById('stopBtn').onclick = stopAttack;

        document.getElementById('phone').addEventListener('input', function() {
            if (this.value && !validatePhone(this.value)) {
                document.getElementById('phoneError').innerHTML = 'Invalid format';
            } else {
                document.getElementById('phoneError').innerHTML = '';
            }
        });

        document.getElementById('quantity').addEventListener('input', function() {
            const qty = parseInt(this.value);
            if (isNaN(qty) || qty < 1 || qty > 300) {
                document.getElementById('quantityError').innerHTML = 'Quantity must be between 1 and 300';
            } else {
                document.getElementById('quantityError').innerHTML = '';
            }
        });
    </script>
</body>
</html>