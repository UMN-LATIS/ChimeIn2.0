<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Present</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        rel="stylesheet"
        type="text/css"
        media="screen"
        href="../../../../css/app.css" />
</head>
<body>
    <div id="app">
        <navbar
            :title="'Present'"
            :user="{{ $user }}"
            :link="'/chime/' + chime_id">
        </navbar>

        <br />

        <div class="row">
            <div class="col s12 m8 l8">
                <results-display
                    v-if="show_results"
                    :sessions="sessions"
                    :session="current_session"
                    :question="current_question">
                </results-display>
                <prompt
                    v-else
                    :question="current_question"
                    :session="current_session">
                </prompt>
            </div>
            <div class="col s12 m4 l4">
            <actions
                v-on:nextquestion="next_question"
                v-on:startsession="start_session"
                v-on:stopsession="stop_session"
                v-on:viewresults="view_results">
            </actions>
            </div>
        </div>
    </div>
    <script src="{{ mix('js/present.js') }}"></script>
</body>
</html>