<template>
    <div class="card">
        <div class="card-body">
            <b-row>

                <b-col sm="9">
                   
                     <h4>
                    <router-link :to="{ name: 'folder', params: {chimeId: chime.id, folderId: folder.id} }">
                     {{ folder.name }}</router-link></h4>
                </b-col>
                <b-col sm="3" class="text-right">

                    <router-link :to="{ name: 'present', params: {chimeId: chime.id, folderId: folder.id} }">
                        <i class="material-icons">play_arrow</i>
                    </router-link>
                </b-col>
            </b-row> 

        </div>
    </div>
</template>

<script>

    export default {
        props: ['folder', 'chime'],
    // this causes us to listen too much, because we get chime level data for each folder
    mixins: [],
    data() {
        return {
        }
    },
};
</script>

<style >
.pointer {
    cursor: pointer;
}
</style>
<style scoped>
ul li {
    list-style: none;
}

</style>
