<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Register - MCC Payroll System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        /* BASE STYLES */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #3498db, #87CEEB); /* Mas magandang gradient */
            padding: 20px 0;
        }

        .register-container {
            background: #ffffff;
            /* Inalis ang blur effect para mas clear */
            padding: 30px;
            border-radius: 12px; /* Mas rounded */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); /* Mas matingkad na shadow */
            width: 100%;
            max-width: 420px;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
        }

        /* FORM ELEMENTS */
        .mb-3 {
            margin-bottom: 1.25rem; /* Pinalaki ang space */
        }
        .form-label {
            font-weight: 600;
            color: #34495e;
            display: block;
            margin-bottom: 0.4rem;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #bdc3c7;
            padding: 10px 12px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        /* INPUT GROUP STYLES (Para sa password toggle) */
        .input-group {
            display: flex;
            width: 100%;
        }
        .input-group .form-control {
            border-right: none;
        }
        .input-group > .form-control:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .btn-outline-secondary {
            border: 1px solid #bdc3c7;
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 0 10px;
            border-left: none;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-outline-secondary:hover {
            background-color: #e9ecef;
        }

        /* BUTTON STYLES */
        .btn-register {
            background-color: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            font-size: 1.1em;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-register:disabled {
            background-color: #b0bec5 !important;
            color: #ececec !important;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-register:hover {
            background-color: #2980b9;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.4);
        }

        /* ERROR/FEEDBACK STYLES */
        .is-invalid {
            border-color: #e74c3c !important;
        }
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #e74c3c;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        .form-text {
            font-size: 0.85em;
            color: #7f8c8d !important;
        }

        /* LOGIN LINK */
        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9em;
            color: #34495e;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        /* Password Strength Bar Styles */
        .password-strength-bar-container {
            height: 5px;
            background-color: #eee;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .strength-weak { background-color: #e74c3c; } /* Red */
        .strength-fair { background-color: #f39c12; } /* Orange */
        .strength-good { background-color: #2ecc71; } /* Green */
        .strength-strong { background-color: #3498db; } /* Blue */

        /* Custom SweetAlert Styles */
        .swal2-popup {
            border-radius: 15px !important;
        }
        .swal2-title {
            color: #2c3e50 !important;
        }
        .swal2-html-container {
            max-height: 60vh;
            overflow-y: auto;
            text-align: left;
            line-height: 1.6;
            color: #34495e;
            padding: 0 1em 1em 1em; /* Add some bottom padding */
        }
        /* Custom scrollbar for the terms */
        .swal2-html-container::-webkit-scrollbar {
            width: 10px;
        }
        .swal2-html-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 10px;
        }
        .swal2-html-container::-webkit-scrollbar-thumb {
            background: #dcdcdc;
            border-radius: 10px;
        }
        .swal2-html-container::-webkit-scrollbar-thumb:hover {
            background: #c9c9c9;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center">Register Account</h2>

        <form action="/register" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')">
                        <i class="bi bi-eye-slash-fill" id="togglePasswordIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="password-strength-bar-container">
                    <div id="password-strength-bar" class="password-strength-bar"></div>
                </div>
                <small id="password-strength-text" class="form-text text-muted">Password strength: Very Weak</small>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'toggleConfirmPasswordIcon')">
                        <i class="bi bi-eye-slash-fill" id="toggleConfirmPasswordIcon"></i>
                    </button>
                </div>
                </div>

                           <div class="mb-3">
    <label for="role" class="form-label">Role</label>
    <select name="role" id="role" class="form-control form-select @error('role') is-invalid @enderror" required onchange="toggleCourseField()">
        <option value="">Select Role</option>
        <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
        <option value="attendance_checker" {{ old('role') == 'attendance_checker' ? 'selected' : '' }}>Attendance Checker</option>
    </select>
    @error('role')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
            <div class="mb-3" id="course-group" style="display: {{ old('role') == 'attendance_checker' ? 'block' : 'none' }}">
                <label for="course" class="form-label">Department/Course</label>
                <select name="course" id="course" class="form-control form-select @error('course') is-invalid @enderror">
                    <option value="">Select Department</option>
                    <option value="staff" {{ old('course') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="utility" {{ old('course') == 'utility' ? 'selected' : '' }}>Utility</option>
                    <option value="bsit" {{ old('course') == 'bsit' ? 'selected' : '' }}>BSIT</option>
                    <option value="bsba" {{ old('course') == 'bsba' ? 'selected' : '' }}>BSBA</option>
                    <option value="bshm" {{ old('course') == 'bshm' ? 'selected' : '' }}>BSHM</option>
                    <option value="bsed" {{ old('course') == 'bsed' ? 'selected' : '' }}>BSED</option>
                    <option value="beed" {{ old('course') == 'beed' ? 'selected' : '' }}>BEED</option>
                </select>
                @error('course')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>          


             <div class="mb-3" style="margin-top: 15px;">
                <label style="font-size: 13px; display: flex; align-items: center;">
                    <input type="checkbox" id="terms" name="terms" style="width: auto; margin-right: 8px;">
                    I agree to the
                    <a href="javascript:void(0);" id="terms-link" style="color: #3498db; text-decoration: underline; margin-left: 5px;">
                        Terms and Conditions
                    </a>
                </label>
                <div id="terms-error" class="error-message"></div>
            </div>

            <button type="submit" class="btn-register" id="register-btn" disabled>Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="/">Login here</a>
        </div>
    </div>

    <script>
        // --- SHOW/HIDE PASSWORD FUNCTION ---
        function togglePasswordVisibility(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove('bi-eye-slash-fill');
                toggleIcon.classList.add('bi-eye-fill');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove('bi-eye-fill');
                toggleIcon.classList.add('bi-eye-slash-fill');
            }
        }
        // --- END SHOW/HIDE PASSWORD FUNCTION ---
        
        // --- TOGGLE COURSE & TERMS FIELD FUNCTION ---
        function toggleCourseField() {
            const roleSelect = document.getElementById('role');
            const courseGroup = document.getElementById('course-group');
            
            if (roleSelect.value === 'attendance_checker') {
                courseGroup.style.display = 'block';
            } else {
                courseGroup.style.display = 'none';
                document.getElementById('course').value = ''; 
            }
        }
        document.addEventListener('DOMContentLoaded', toggleCourseField);
        document.getElementById('role').addEventListener('change', toggleCourseField);
        // --- END TOGGLE COURSE FIELD FUNCTION ---

        // --- PASSWORD STRENGTH CHECK ---
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                let score = 0;
                
                if (password.length >= 12) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/\d/.test(password)) score++;
                if (/[@$!%*?&]/.test(password)) score++;

                const maxScore = 5;
                const percentage = (score / maxScore) * 100;

                strengthBar.style.width = percentage + '%';

                if (score === 0) {
                    strengthBar.className = 'password-strength-bar strength-weak';
                    strengthText.textContent = 'Password strength: Very Weak';
                } else if (score <= 2) {
                    strengthBar.className = 'password-strength-bar strength-weak';
                    strengthText.textContent = 'Password strength: Weak';
                } else if (score <= 3) {
                    strengthBar.className = 'password-strength-bar strength-fair';
                    strengthText.textContent = 'Password strength: Fair';
                } else if (score <= 4) {
                    strengthBar.className = 'password-strength-bar strength-good';
                    strengthText.textContent = 'Password strength: Good';
                } else {
                    strengthBar.className = 'password-strength-bar strength-strong';
                    strengthText.textContent = 'Password strength: Strong';
                }
            });
        }
        // --- END PASSWORD STRENGTH CHECK ---
        
        // --- TERMS AND CONDITIONS POPUP ---
        document.getElementById('terms-link').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show a loading state
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we fetch the terms and conditions.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ url('/terms') }}")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok. Status: ' + response.status);
                    }
                    return response.text();
                })
                .then(html => {
                    Swal.fire({
                        icon: 'info',
                        title: '<strong>Terms and Conditions</strong>',
                        html: html,
                        width: '80%',
                        confirmButtonText: 'Got it!',
                        confirmButtonColor: '#3498db',
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching terms and conditions:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Load Content',
                        text: 'The terms and conditions could not be loaded at this time. Please check your connection or try again later.'
                    });
                });
        });
        // --- END TERMS AND CONDITIONS POPUP ---

        // --- REGISTER BUTTON STATE ---
        const termsCheckbox = document.getElementById('terms');
        const registerBtn = document.getElementById('register-btn');

        if (termsCheckbox && registerBtn) {
            function updateRegisterBtnState() {
                if (termsCheckbox.checked) {
                    registerBtn.disabled = false;
                    registerBtn.style.backgroundColor = '';
                    registerBtn.style.color = '';
                } else {
                    registerBtn.disabled = true;
                    registerBtn.style.backgroundColor = '#b0bec5';
                    registerBtn.style.color = '#ececec';
                }
            }
            termsCheckbox.addEventListener('change', updateRegisterBtnState);
            updateRegisterBtnState();
        }
        // --- END REGISTER BUTTON STATE ---

        // --- DEVTOOLS DETECTION ---
        devtools.detect(function(status){
          if(status){
            document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
          }
        });
        // --- END DEVTOOLS DETECTION ---
    </script>
</body>
</html>