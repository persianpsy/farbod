<!DOCTYPE html>
<html>
<head>
    <title>Persian Psychology – Gift Code</title>
</head>
<body>

    @component('mail::message')
<img style="text-align:center" src="https://persianpsychology.com/img/1.png"/>
<br/>
<br/>
<div style="font-family:cursive;font-size:15px">
    Dear {{ $data['sender'] }},
</div>
<br/>
<div style="font-family:cursive;font-size:30px">
    {{ $data['text'] }}
</div>
<!--<div style="font-family:cursive">-->
<!-- From {{ $data['sender'] }}-->
<!--</div>-->

<br/>
<br/>
<img style="text-align:right" src="https://persianpsychology.com/img/2.png"/>
@component('mail::button', ['url' => 'https://www.persianpsychology.com'])
Gift code : {{ $data['code'] }}
@endcomponent
@endcomponent
    
</body>
</html>
