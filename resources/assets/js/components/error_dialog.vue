<template>
    <modal :show="$store.getters.message">
        <div class="alert alert-danger" role="alert">
            <strong>Error: </strong> {{ $store.getters.message }}  Refresh your browser, or return to the <router-link to="/" >home page</router-link> to try again.  If the error continues, please <a href="mailto:clatel@umn.edu">get in touch</a>.
    </div>    
    </modal>
</template>

