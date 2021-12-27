<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/openplayerjs@latest/dist/openplayer.min.css">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #vast-player {
            width: 100%;
        }
    </style>
</head>

<body>
<video id="vast-player">
    <source src="{{ $url }}" type="video/mp4" />
</video>

<script src="https://cdn.fluidplayer.com/v3/current/fluidplayer.min.js"></script>
<script>
    var player = fluidPlayer('vast-player', {
        layoutControls: {
            posterImage: '{{ $image }}',
            primaryColor: "#e63c47",
            fillToContainer: true,
            allowTheatre: false,
            playbackRateEnabled: true
        },
        vastOptions: {
            adList: [{
                roll: 'preRoll',
                vastTag: 'https://v2.api.goal.ge/news-video/vast.xml?preroll_id={{ $preroll->id }}',
                adText: ''
            }],
            skipButtonCaption: 'გამოტოვე [seconds] წამში',
            skipButtonClickCaption: 'გამოტოვება <span class="skip_button_icon"></span>',
            adText: null,
            adTextPosition: 'top left',
            adCTAText: 'რეკლამაზე გადასვლა',
            adCTATextPosition: 'bottom right',
            vastTimeout: 6000,
            showPlayButton: false,
            maxAllowedVastTagRedirects: 1,
        }
    });
</script>
</body>

</html>