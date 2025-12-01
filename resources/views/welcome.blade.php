<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Custom styles to match the Sales Performance image -->
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
            }

            body::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, #D35400 0%, #E67E22 50%, #FDF6E3 100%);
                opacity: 0.3;
                z-index: 0;
            }

            .container {
                position: relative;
                z-index: 1;
                text-align: center;
                padding: 80px 40px;
                max-width: 800px;
                width: 90%;
            }

            h1 {
                font-size: 4rem;
                font-weight: 900;
                color: #60a5fa;
                margin-bottom: 80px;
                text-transform: uppercase;
                letter-spacing: 12px;
                text-shadow: 0 0 30px rgba(96, 165, 250, 0.5),
                             0 0 60px rgba(96, 165, 250, 0.3),
                             0 5px 10px rgba(0, 0, 0, 0.3);
                animation: pulse 3s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    text-shadow: 0 0 30px rgba(96, 165, 250, 0.5),
                                 0 0 60px rgba(96, 165, 250, 0.3),
                                 0 5px 10px rgba(0, 0, 0, 0.3);
                }
                50% {
                    text-shadow: 0 0 40px rgba(96, 165, 250, 0.8),
                                 0 0 80px rgba(96, 165, 250, 0.5),
                                 0 5px 15px rgba(0, 0, 0, 0.5);
                }
            }

            .buttons {
                display: flex;
                gap: 40px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                display: inline-block;
                padding: 20px 60px;
                background: rgba(147, 197, 253, 0.1);
                color: #93c5fd;
                text-decoration: none;
                border-radius: 8px;
                font-size: 1.3rem;
                font-weight: 700;
                letter-spacing: 4px;
                text-transform: uppercase;
                border: 2px solid #60a5fa;
                transition: all 0.3s ease;
                box-shadow: 0 4px 20px rgba(96, 165, 250, 0.2),
                           inset 0 1px 0 rgba(255, 255, 255, 0.1);
                position: relative;
                overflow: hidden;
            }

            .btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.5s;
            }

            .btn:hover::before {
                left: 100%;
            }

            .btn:hover {
                background: rgba(96, 165, 250, 0.2);
                border-color: #93c5fd;
                transform: translateY(-3px);
                box-shadow: 0 8px 30px rgba(96, 165, 250, 0.4),
                           inset 0 1px 0 rgba(255, 255, 255, 0.2);
                color: #dbeafe;
            }

            .btn:active {
                transform: translateY(-1px);
            }

            @media (max-width: 768px) {
                h1 {
                    font-size: 2.5rem;
                    letter-spacing: 6px;
                    margin-bottom: 60px;
                }

                .btn {
                    padding: 16px 40px;
                    font-size: 1.1rem;
                    letter-spacing: 3px;
                }

                .buttons {
                    gap: 25px;
                }

                .container {
                    padding: 60px 30px;
                }
            }

            @media (max-width: 480px) {
                h1 {
                    font-size: 2rem;
                    letter-spacing: 4px;
                }

                .btn {
                    padding: 14px 35px;
                    font-size: 1rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Sales Performance</h1>
            <div class="buttons">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn">Login</a>
                    @endauth
                @endif
            </div>
        </div>
    </body>
</html>
