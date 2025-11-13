<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register</title>
    @vite(['resources/css/app.css'])
    <style>
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold mb-6">Register</h1>
        <!-- Step 1: Send OTP -->
        <div id="sendOtpSection">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Phone Number</label>
                <div class="flex gap-2">
                    <input type="tel" id="phone_number" required
                           class="w-full px-4 py-2 border rounded"
                           placeholder="+96170123456">
                    <button type="button" id="sendOtpBtn"
                            class="bg-blue-500 text-white px-4 py-2 rounded">
                        Send OTP
                    </button>
                </div>
                <p id="otpStatus" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <div class="mb-4 hidden" id="otpInputSection">
                <label class="block text-sm font-medium mb-2">OTP Code</label>
                <input type="text" id="otp_code" class="w-full px-4 py-2 border rounded" placeholder="Enter OTP">
                <button type="button" id="verifyOtpBtn"
                        class="mt-2 bg-green-500 text-white px-4 py-2 rounded">
                    Verify OTP
                </button>
            </div>
        </div>
        <!-- Step 2: Registration Form -->
        <form id="registerForm" class="hidden">
            <input type="hidden" id="hiddenPhoneNumber">
            <input type="hidden" id="hiddenOtpCode">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Username</label>
                <input type="text" id="username" required class="w-full px-4 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">First Name</label>
                <input type="text" id="first_name" required class="w-full px-4 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="last_name" required class="w-full px-4 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">License Number</label>
                <input type="text" id="license_number" required class="w-full px-4 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" required class="w-full px-4 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Confirm Password</label>
                <input type="password" id="password_confirmation" required class="w-full px-4 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Role</label>
                <select id="role" required class="w-full px-4 py-2 border rounded" onchange="toggleBusinessType()">
                    <option value="client">Client</option>
                    <option value="agency">Agency</option>
                </select>
            </div>
            <!-- Business Type Field (Hidden by default) -->
            <div class="mb-4 hidden" id="businessTypeSection">
                <label class="block text-sm font-medium mb-2">Business Type</label>
                <input type="text" id="business_type" class="w-full px-4 py-2 border rounded" placeholder="e.g., Company">
            </div>
            <button type="button" id="registerBtn" class="w-full bg-blue-500 text-white py-2 rounded">
                Register
            </button>
        </form>
    </div>
    <!-- JavaScript for OTP and Business Type Logic -->
    <script>
        // Toggle Business Type field based on role
        function toggleBusinessType() {
            const role = document.getElementById('role').value;
            const businessTypeSection = document.getElementById('businessTypeSection');
            if (role === 'agency') {
                businessTypeSection.classList.remove('hidden');
            } else {
                businessTypeSection.classList.add('hidden');
                document.getElementById('business_type').value = '';
            }
        }
        // Send OTP
        document.getElementById('sendOtpBtn').addEventListener('click', async () => {
            const phoneNumber = document.getElementById('phone_number').value;
            try {
                const response = await fetch('/api/auth/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone_number: phoneNumber }),
                });
                const data = await response.json();
                if (response.ok) {
                    document.getElementById('otpStatus').textContent = 'OTP sent! Check your phone.';
                    document.getElementById('otpInputSection').classList.remove('hidden');
                } else {
                    document.getElementById('otpStatus').textContent = data.message || 'Failed to send OTP.';
                }
            } catch (error) {
                document.getElementById('otpStatus').textContent = 'An error occurred.';
            }
        });
        // Verify OTP
        document.getElementById('verifyOtpBtn').addEventListener('click', () => {
            const otpCode = document.getElementById('otp_code').value;
            const phoneNumber = document.getElementById('phone_number').value;
            if (!otpCode) {
                alert('Please enter the OTP code.');
                return;
            }
            document.getElementById('hiddenPhoneNumber').value = phoneNumber;
            document.getElementById('hiddenOtpCode').value = otpCode;
            document.getElementById('sendOtpSection').classList.add('hidden');
            document.getElementById('registerForm').classList.remove('hidden');
        });
        // Register
        document.getElementById('registerBtn').addEventListener('click', async () => {
            const phoneNumber = document.getElementById('hiddenPhoneNumber').value;
            const otpCode = document.getElementById('hiddenOtpCode').value;
            const username = document.getElementById('username').value;
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const licenseNumber = document.getElementById('license_number').value;
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            const role = document.getElementById('role').value;
            const businessType = document.getElementById('business_type').value;

            if (password !== passwordConfirmation) {
                alert('Passwords do not match!');
                return;
            }

            const payload = {
                phone_number: phoneNumber,
                otp_code: otpCode,
                username: username,
                first_name: firstName,
                last_name: lastName,
                license_number: licenseNumber,
                password: password,
                role: role,
                ...(role === 'agency' && { business_type: businessType }),
            };

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (response.ok) {
                    alert('Registration successful!');
                    // Redirect or handle success
                } else {
                    alert(data.message || 'Registration failed.');
                }
            } catch (error) {
                alert('An error occurred.');
            }
        });
    </script>
</body>
</html>
