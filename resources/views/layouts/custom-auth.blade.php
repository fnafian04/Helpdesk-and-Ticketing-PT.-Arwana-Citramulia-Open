<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Helpdesk PT. Arwana</title>
   @vite(['resources/css/auth-style.css'])
</head>
<body>
    
    <div class="auth-card">
        <div class="auth-header">
            <div class="company-logo">A</div> <h1>Helpdesk System</h1>
            <p>PT. Arwana Citramulia Tbk</p>
        </div>

        @yield('content')
    </div>

</body>
</html>