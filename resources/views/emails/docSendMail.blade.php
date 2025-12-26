<!DOCTYPE html>
<html>
    <head>
        <title>{{ $details['title']??'' }}</title>
    </head>
    <body>

        <p>{{ $details->get('body')??'-' }}</p>
        <ul>
            <li>Kimdan: {{ $details->get('from_user_name')??'-' }}</li>
            <li>Hujjat: <a href="{{ $details->get('link_url')??'#' }}" target="_blank">{{ $details->get('link_name')??' havolaga o`ting...' }}</a></li>
        </ul>
    </body>
</html>
