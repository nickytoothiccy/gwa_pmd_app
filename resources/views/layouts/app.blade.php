<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GWA PMD App - Equipment Management and Reporting">
    <meta name="author" content="Your Company Name">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GWA PMD App') }}</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .navbar-brand, .nav-link {
            color: #333 !important;
        }
        .glossy-bg {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .btn-large {
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        main {
            flex: 1;
        }
        footer {
            background-color: #f8f9fa;
            padding: 1rem 0;
            margin-top: auto;
        }
        #sendEmailBtn {
            display: none;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">GWA PMD App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pmd_cnet_ffu.index') }}">FFU</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.index') }}">Reports</a>
                    </li>
                </ul>
                <button id="sendEmailBtn" class="btn btn-warning" title="Send FFU updates email">
                    <i class="fas fa-file-exclamation"></i>
                </button>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="text-center">
        <div class="container">
            <span class="text-muted">&copy; {{ date('Y') }} Your Company Name. All rights reserved.</span>
        </div>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sendEmailBtn = document.getElementById('sendEmailBtn');

            function checkJsonFileStatus() {
                axios.get('{{ route("pmd_cnet_ffu.check_json_file") }}')
                    .then(response => {
                        if (response.data.hasData) {
                            sendEmailBtn.style.display = 'inline-block';
                        } else {
                            sendEmailBtn.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error checking JSON file status:', error));
            }

            sendEmailBtn.addEventListener('click', function() {
                axios.post('{{ route("pmd_cnet_ffu.send_email") }}')
                    .then(response => {
                        if (response.data.success) {
                            alert('Email sent successfully');
                            checkJsonFileStatus();
                        } else {
                            alert('Failed to send email');
                        }
                    })
                    .catch(error => {
                        console.error('Error sending email:', error);
                        alert('An error occurred while sending the email');
                    });
            });

            // Check JSON file status every 60 seconds
            setInterval(checkJsonFileStatus, 60000);

            // Initial check
            checkJsonFileStatus();
        });
    </script>
    @stack('scripts')
</body>
</html>
