<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
     <a v-bind:href="String(link)" class="navbar-brand">{{ title }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
        <li>
            <a href="/logout" v-if="user.id">Logout</a>
            <a href="/login" v-else>login</a>
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
