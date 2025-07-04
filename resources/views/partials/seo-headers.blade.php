{{-- SEO Meta Tags for Faxtina Spa & Salon Application --}}
<meta name="description" content="Faxtina - The complete cloud-based solution for modern salons and spas. Manage appointments, clients, inventory, and grow your business.">
<meta name="keywords" content="salon software, spa management, appointment scheduling, client management, point of sale, business analytics">
<meta name="author" content="Faxtina">
<meta name="application-name" content="Faxtina">

{{-- Open Graph Meta Tags --}}
<meta property="og:title" content="Faxtina - Salon & Spa Management Software">
<meta property="og:description" content="The complete cloud-based solution for modern salons and spas. Manage appointments, clients, inventory, and grow your business.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ config('app.url') }}">
<meta property="og:image" content="{{ asset('images/faxtina-logo.jpg') }}">
<meta property="og:site_name" content="Faxtina">
<meta property="og:locale" content="en_US">

{{-- Twitter Card Meta Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Faxtina - Salon & Spa Management Software">
<meta name="twitter:description" content="The complete cloud-based solution for modern salons and spas.">
<meta name="twitter:image" content="{{ asset('images/faxtina-logo.jpg') }}">

{{-- Robots Meta Tags --}}
<meta name="robots" content="index, follow">
<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ config('app.url') }}{{ request()->path() === '/' ? '' : '/' . request()->path() }}">
