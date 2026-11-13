<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Counselling and mental health advocacy support in Abuja, Nigeria.' }}">
<meta name="theme-color" content="#70358C">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
