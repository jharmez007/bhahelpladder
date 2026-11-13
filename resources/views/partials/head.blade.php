<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Counselling and mental health advocacy support from BHA Help Ladder.' }}">
<meta name="theme-color" content="#70358C">

<script>
    window.localStorage.setItem('flux.appearance', 'light');
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
