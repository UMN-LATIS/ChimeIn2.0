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
            <new-folder
                :chime="chime"
                v-on:newfolder="create_folder"
                v-on:filterfolder="filter_folders"></new-folder>
            <div v-if="viewable_folders.length > 0">
                <transition-group name="fade">
                    <folder-card
                        v-for="folder in viewable_folders"
                        :folder="folder"
                        :chime="chime"
                        :key="folder.id"
                        v-on:editfolder="edit_folder"
                        v-on:deletefolder="delete_folder">
                    </folder-card>
                </transition-group>
            </div>
            <div v-else>
                No Folders Yet!
            </div>
        </div>
    </div>
    <script>window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'</script>
    <script src="{{ mix('js/chime.js') }}"></script>

</body>
</html>