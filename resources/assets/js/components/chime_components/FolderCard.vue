<template>
    <div class="card hoverable">
        <div class="card-body">
            <div class="row">
                
                <div class="col-sm-9" @click="$router.push({ name: 'folder', params: {chimeId: chime.id, folderId: folder.id} })">
                   
                     <h4>{{ folder.name }}</h4>
                </div>
                <div class="col-sm-3 text-right">

                    <router-link :to="{ name: 'present', params: {chimeId: chime.id, folderId: folder.id} }">
                        <i class="material-icons">play_arrow</i>
                    </router-link>
                </div>
            </div> 
            
        </div>
    </div>
</template>

<script>

    export default {
        props: ['folder', 'chime'],
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
