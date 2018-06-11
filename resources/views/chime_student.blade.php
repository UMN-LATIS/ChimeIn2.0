<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chime</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        rel="stylesheet"
        type="text/css"
        media="screen"
        href="../css/app.css" />
</head>
<body>
    <div id="app">
        <navbar
            :title="chime.name"
            :user="{{ $user }}"
            :link="'/'">
        </navbar>

        <br />
        
        <div class="container center-align">
            <actions
                v-on:selectcurrentquestions="() => {show = 'current_questions'}"
                v-on:selectpastresponses="() => {show = 'past_responses'}"
            ></actions>

            <br />

            <div v-if="show === 'current_questions'">
                <div v-if="sessions.length < 1">
                    <h3>No Open Sessions!</h3>
                </div>
                <prompt
                    v-else
                    v-for="s in sessions"
                    :session="s"
                    :key="s.id">
                </prompt>
            </div>

            <div v-if="show === 'past_responses'">
                <div v-if="responses.length < 1">
                    <h3>No Responses Yet!</h3>
                </div>
                <response
                    v-else
                    v-for="(r, i) in responses"
                    :question="r.question"
                    :response="r.response"
                    :key="i">
                </response>
            </div>
        </div>
    </div>
    <script>window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'</script>
    <script src="{{ mix('js/chime_student.js') }}"></script>
</body>
</html>