<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    </head>
    <body>
        <h1>Formster test</h1>
        <p><a href="{{ route('view', compact('model')) }}">View</a></p>
        <div>
            <x-formster::form :object="$model" action="{{ route('update', compact('model')) }}" />
        </div>
    </body>
</html>
