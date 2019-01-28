<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
     <router-link :to="link"class="navbar-brand">{{ title }}</router-link\>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto">
        <li>
            <a href="/logout" v-if="user.id && !user.guest_user">Log out</a>
            <a href="/shibboleth-logout" v-else>Log in</a>
        </li>
        </ul>

    </div>

    </nav>

</template>

<script>
export default {
    props: ['title', 'user', 'link']
}
</script>
