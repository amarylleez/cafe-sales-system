<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - {{ config('app.name', 'Laravel') }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="%23334155"/><rect width="100%" height="100%" fill="url(%23grid)"/></svg>') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #423A8E 0%, #00CCCD 50%, #F8F9FA 100%);
            opacity: 0.3;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 550px;
            background: rgba(147, 197, 253, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 4px 20px rgba(96, 165, 250, 0.2),
                       inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 2px solid #60a5fa;
            margin: 20px 0;
        }

        h1 {
            font-size: 3rem;
            font-weight: 900;
            color: #60a5fa;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 8px;
            text-align: center;
            text-shadow: 0 0 30px rgba(96, 165, 250, 0.5),
                         0 0 60px rgba(96, 165, 250, 0.3),
                         0 5px 10px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            text-align: center;
            color: #93c5fd;
            font-size: 0.95rem;
            margin-bottom: 40px;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #93c5fd;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"],
        select {
            width: 100%;
            padding: 16px 20px;
            background: rgba(147, 197, 253, 0.1);
            border: 2px solid rgba(96, 165, 250, 0.3);
            border-radius: 8px;
            color: #dbeafe;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus,
        select:focus {
            border-color: #60a5fa;
            background: rgba(96, 165, 250, 0.2);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        select {
            cursor: pointer;
        }

        select option {
            background: #1e293b;
            color: #e0f2fe;
        }

        input::placeholder {
            color: rgba(147, 197, 253, 0.5);
        }

        .error-message {
            color: #fca5a5;
            font-size: 0.85rem;
            margin-top: 8px;
            display: block;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .link {
            color: #93c5fd;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .link:hover {
            color: #dbeafe;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
        }

        .btn-primary {
            padding: 18px 50px;
            background: rgba(147, 197, 253, 0.1);
            color: #93c5fd;
            border: 2px solid #60a5fa;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(96, 165, 250, 0.2),
                       inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: rgba(96, 165, 250, 0.2);
            border-color: #93c5fd;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(96, 165, 250, 0.4),
                       inset 0 1px 0 rgba(255, 255, 255, 0.2);
            color: #dbeafe;
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 2rem;
                letter-spacing: 4px;
            }

            .form-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary {
                width: 100%;
                padding: 15px 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <p class="subtitle">Create Your Account</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter your name">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your email">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Enter your password">
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
                @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Role -->
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="staff">Staff</option>
                    <option value="branch_manager">Branch Manager</option>
                    <option value="hq_admin">HQ Admin</option>
                </select>
                @error('role')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Branch -->
            <div class="form-group" id="branch-field">
                <label for="branch_id">Branch</label>
                <select id="branch_id" name="branch_id">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <script>
                document.getElementById('role').addEventListener('change', function() {
                    const branchField = document.getElementById('branch-field');
                    const branchSelect = document.getElementById('branch_id');
                    
                    if (this.value === 'hq_admin') {
                        branchField.style.display = 'none';
                        branchSelect.removeAttribute('required');
                        branchSelect.value = '';
                    } else {
                        branchField.style.display = 'block';
                        branchSelect.setAttribute('required', 'required');
                    }
                });
            </script>

            <div class="form-footer">
                <a class="link" href="{{ route('login') }}">
                    Already registered?
                </a>

                <button type="submit" class="btn-primary">
                    Register
                </button>
            </div>
        </form>
    </div>
</body>
</html>


