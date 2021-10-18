<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-light align-center">
        <div class="container">
        <router-link v-if="title" :to="link" class="navbar-brand">{{ title }}</router-link>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <div class="ml-auto navbar-text" data-cy="show-join-code" v-if="host">Go to <strong>{{ host }}</strong> and enter code <strong>{{ access_code }}</strong></div>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link align-items-center d-flex"
                        href="https://umn-latis.github.io/ChimeIn2.0/">Help <i class="material-icons">help</i></a>
                </li>
                <li class="nav-item"><a class="nav-link align-items-center d-flex" href="/impersonate/leave"
                        v-if="user.impersonating">End Impersonation<i class="material-icons">logout</i></a></li>
                <li class="nav-item">
                    <a href="/shibboleth-logout" class="nav-link align-items-center d-flex"
                        v-if="user.id && !user.guest_user">Log out <i class="material-icons">logout</i>
                        </a>
                    <a href="/login" class="nav-link align-items-center d-flex" v-else>Log in <i
                            class="material-icons">account_box</i>
                            </a>
                </li>
            </ul>
            </div>

        </div>

    </nav>

</template>

<script>
    export default {
        props: ['title', 'user', 'link', 'access_code', "host"],
    }
</script>

<style scoped>
    .material-icons {
        font-size: 1.2em;
        margin-left: 2px;
    }

    .ml-auto li {
        margin-left: 2px;
        margin-right: 2px;
    }

    .navbar .navbar-collapse {
        text-align: center;
    }
</style>