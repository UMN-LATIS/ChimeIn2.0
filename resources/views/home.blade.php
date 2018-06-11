<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/app.css" />
</head>
<body>
    <div id="app">
        <navbar
            :title="'Home'"
            :user="{{ $user }}"
            :link="'/'">
        </navbar>
        
        <div class="container">
            <h4 class="center">
                Welcome, {{ $user->name ? $user->name : 'Guest '.$user->id }}
            </h4>

            <div class="row">
                <div class="col s12 m12 l8">
                    <chime-panel
                        :chimes="viewable_chimes"
                        :user="{{ $user }}"
                        v-on:newchime="create_chime"
                        v-on:filterchime="filter_chimes"
                        v-on:deletechime="delete_chime">
                    </chime-panel>
                </div>
                <div class="col s12 m12 l4">
                    <div class="row">
                        <div class="col s12">
                            <access-panel
                                v-on:submitcode="join_chime">
                            </access-panel>
                        </div>
                        <div class="col s12">
                            <info-panel></info-panel>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ mix('js/home.js') }}"></script>
</body>
</html>