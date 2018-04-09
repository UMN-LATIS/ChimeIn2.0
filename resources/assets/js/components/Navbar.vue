<template>
    <nav>
        <div class="nav-wrapper">
            <a
                v-bind:href="String(link)"
                class="brand-logo center">
                {{ title }}
            </a>
            <ul id="menu" class="sidenav right">
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
