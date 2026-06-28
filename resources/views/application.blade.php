<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="{{ asset('images/jaya-raya-logo.png') }}" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Development - Dashboard</title>
  <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
  @vite(['resources/ts/main.ts'])
</head>

<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
        <!-- Jaya Raya Logo -->
        <img src="{{ asset('images/jaya-raya-logo.png') }}" alt="Jaya Raya Logo" width="86" height="48">
      </div>
      <div class="loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>
  </div>
  
  <script>
    const loaderColor = localStorage.getItem('timdev-initial-loader-bg') || '#FFFFFF'
    const primaryColor = localStorage.getItem('timdev-initial-loader-color') || '#9155FD'

    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

    if (primaryColor)
      document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
  </script>
</body>

</html>
